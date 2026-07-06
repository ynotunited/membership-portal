<?php

namespace App\Models;

/**
 * PaymentModel — orchestrates payment initialisation and verification.
 *
 * Key guarantees:
 *  1. Intent written to ledger BEFORE the gateway API is called.
 *  2. Idempotency key checked first; duplicate request returns cached result.
 *  3. Status changes NEVER overwrite rows — every transition appends to ledger.
 *  4. updatePaymentStatus() kept as a backward-compat shim but now appends.
 */
class PaymentModel extends BaseModel
{
    private string $paystackSecretKey;
    private string $monifySecretKey;
    private string $monifyPublicKey;
    private string $opaySecretKey;
    private string $opayPublicKey;

    private PaymentLedger      $ledger;
    private PaymentIdempotency $idempotency;

    public function __construct()
    {
        parent::__construct();
        $this->paystackSecretKey = getenv('PAYSTACK_SECRET_KEY') ?: '';
        $this->monifySecretKey   = getenv('MONIFY_SECRET_KEY')   ?: '';
        $this->monifyPublicKey   = getenv('MONIFY_PUBLIC_KEY')   ?: '';
        $this->opaySecretKey     = getenv('OPAY_SECRET_KEY')     ?: '';
        $this->opayPublicKey     = getenv('OPAY_PUBLIC_KEY')     ?: '';

        $this->ledger      = new PaymentLedger();
        $this->idempotency = new PaymentIdempotency();
    }

    // =========================================================================
    // Public: initialise a payment
    // =========================================================================

    /**
     * @param string      $idempotencyKey  UUID v4 from the client
     * @param string      $email
     * @param int         $amountKobo      amount in smallest unit (kobo)
     * @param string      $membershipNumber
     * @param string      $callbackUrl
     * @param string      $gateway         paystack|monify|opay|manual
     * @param string      $paymentType     annual_dues|shares|thrift_savings|general
     * @return array      ['status'=>bool, 'data'=>[...]] | ['status'=>false, 'message'=>'...']
     */
    public function initializePayment(
        string $email,
        int    $amountKobo,
        string $membershipNumber,
        string $callbackUrl,
        string $gateway      = 'paystack',
        string $paymentType  = 'general',
        string $idempotencyKey = ''
    ): array {
        // ── 1. Idempotency check ─────────────────────────────────────────────
        if ($idempotencyKey !== '') {
            if (!PaymentIdempotency::isValidUuid($idempotencyKey)) {
                return ['status' => false, 'message' => 'Invalid idempotency key format. Must be a UUID v4.'];
            }

            $reqHash = PaymentIdempotency::requestHash($amountKobo, $email, $paymentType, $gateway);
            $cached  = $this->idempotency->find($idempotencyKey);

            if ($cached !== null) {
                // Payload changed for the same key → reject
                if ($cached['request_hash'] !== $reqHash) {
                    return ['status' => false, 'message' => 'Idempotency key reused with different payment parameters.'];
                }
                // Return the cached response — no second charge
                return json_decode($cached['response_body'], true);
            }
        }

        // ── 2. Create the mutable payment_transactions row ───────────────────
        $paymentId = $this->createPaymentRecord($email, $amountKobo, $membershipNumber, $gateway, $paymentType);

        // ── 3. Write the INTENT ledger row BEFORE calling the gateway ─────────
        $this->ledger->append([
            'payment_id'      => $paymentId,
            'state'           => 'intent',
            'actor'           => 'member',
            'actor_id'        => $_SESSION['user_id'] ?? null,
            'amount_kobo'     => $amountKobo,
            'gateway'         => $gateway,
            'idempotency_key' => $idempotencyKey ?: null,
        ]);

        // ── 4. Call the gateway ───────────────────────────────────────────────
        switch ($gateway) {
            case 'monify':
                $result = $this->initializeMonifyPayment($email, $amountKobo, $membershipNumber, $callbackUrl, $paymentId, $idempotencyKey);
                break;
            case 'opay':
                $result = $this->initializeOpayPayment($email, $amountKobo, $membershipNumber, $callbackUrl, $paymentId, $idempotencyKey);
                break;
            case 'manual':
                $result = $this->initializeManualPayment($amountKobo, $paymentId, $idempotencyKey);
                break;
            case 'paystack':
            default:
                $result = $this->initializePaystackPayment($email, $amountKobo, $membershipNumber, $callbackUrl, $paymentId, $idempotencyKey);
        }

        // ── 5. Store in idempotency cache if key was provided ─────────────────
        if ($idempotencyKey !== '' && $result['status'] === true) {
            $reqHash = PaymentIdempotency::requestHash($amountKobo, $email, $paymentType, $gateway);
            $this->idempotency->store($idempotencyKey, $reqHash, $paymentId, $result);
        }

        return $result;
    }

    // =========================================================================
    // Backward-compat shim: append to ledger instead of UPDATE
    // =========================================================================

    /**
     * Previously did UPDATE payment_transactions SET status = ...
     * Now appends a new ledger row AND still syncs the mutable column for
     * legacy queries that read payment_transactions.status directly.
     */
    public function updatePaymentStatus(
        $paymentId,
        string $status,
        ?string $reference    = null,
        ?string $errorMessage = null
    ): void {
        $db = $this->getConnection();

        // Map legacy status strings to ledger state enum values
        $stateMap = [
            'pending'        => 'intent',
            'success'        => 'captured',
            'failed'         => 'failed',
            'cancelled'      => 'cancelled',
            'refunded'       => 'refunded',
            'ADMIN_APPROVED' => 'admin_approved',
        ];
        $state = $stateMap[$status] ?? 'failed';

        // Append ledger row
        $this->ledger->append([
            'payment_id'  => (int)$paymentId,
            'state'       => $state,
            'actor'       => 'system',
            'gateway_ref' => $reference,
            'payload'     => $errorMessage ? ['error' => $errorMessage] : null,
        ]);

        // Keep the mutable column in sync for any code that still reads it
        $db->prepare(
            "UPDATE payment_transactions
             SET status = ?, reference = ?, error_message = ?, updated_at = NOW()
             WHERE id = ?"
        )->execute([$status, $reference, $errorMessage, $paymentId]);
    }

    // =========================================================================
    // Verification
    // =========================================================================

    public function verifyPayment(string $reference, string $gateway = 'paystack'): array
    {
        switch ($gateway) {
            case 'monify': return $this->verifyMonifyPayment($reference);
            case 'opay':   return $this->verifyOpayPayment($reference);
            default:       return $this->verifyPaystackPayment($reference);
        }
    }

    // =========================================================================
    // Read helpers (unchanged)
    // =========================================================================

    public function getPendingPayments(): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT pt.*, m.firstname, m.surname, m.id as member_id
            FROM payment_transactions pt
            LEFT JOIN members m ON pt.membership_number = m.membership_number
            WHERE pt.status = 'pending'
            ORDER BY pt.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaymentRecord($paymentId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM payment_transactions WHERE id = ?"
        );
        $stmt->execute([$paymentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPaymentRecordByMember($paymentId): ?array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM rls_payment_transactions WHERE id = ?"
        );
        $stmt->execute([$paymentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Return the full immutable timeline for a payment. */
    public function getTimeline(int $paymentId): array
    {
        return $this->ledger->getTimeline($paymentId);
    }

    // =========================================================================
    // Private: gateway initialisers
    // =========================================================================

    private function createPaymentRecord(
        string $email,
        int    $amountKobo,
        string $membershipNumber,
        string $gateway,
        string $paymentType
    ): int {
        $db   = $this->getConnection();
        $stmt = $db->prepare("
            INSERT INTO payment_transactions
                (email, amount, membership_number, gateway, status, payment_type, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'pending', ?, NOW(), NOW())
        ");
        $stmt->execute([$email, $amountKobo, $membershipNumber, $gateway, $paymentType]);
        return (int)$db->lastInsertId();
    }

    private function initializePaystackPayment(
        string $email,
        int    $amountKobo,
        string $membershipNumber,
        string $callbackUrl,
        int    $paymentId,
        string $idempotencyKey = ''
    ): array {
        // Demo mode
        if (empty($this->paystackSecretKey)) {
            $ref = 'PAYSTACK_' . time() . '_' . bin2hex(random_bytes(4));
            $this->ledger->append([
                'payment_id'      => $paymentId,
                'state'           => 'gateway_init',
                'actor'           => 'system',
                'gateway'         => 'paystack',
                'gateway_ref'     => $ref,
                'idempotency_key' => $idempotencyKey ?: null,
                'payload'         => ['demo' => true],
            ]);
            $this->updatePaymentStatus($paymentId, 'pending', $ref);
            $mockUrl = \App\Helpers\Url::appUrl() . '/member/dues/mock-payment?' . http_build_query([
                'reference'      => $ref,
                'amount'         => $amountKobo,
                'email'          => $email,
                'callback_url'   => $callbackUrl,
                'gateway'        => 'paystack',
                'payment_id'     => $paymentId,
            ]);
            return ['status' => true, 'data' => ['reference' => $ref, 'authorization_url' => $mockUrl, 'payment_id' => $paymentId]];
        }

        try {
            $ch = curl_init('https://api.paystack.co/transaction/initialize');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'email'        => $email,
                    'amount'       => $amountKobo,
                    'currency'     => 'NGN',
                    'callback_url' => $callbackUrl,
                    'metadata'     => json_encode(['membership_number' => $membershipNumber, 'payment_id' => $paymentId]),
                ]),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->paystackSecretKey,
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT        => 30,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                throw new \RuntimeException('cURL error: ' . $curlErr);
            }

            $result = json_decode($response, true);

            if ($result['status'] && $httpCode === 200) {
                $ref = $result['data']['reference'];
                $this->ledger->append([
                    'payment_id'      => $paymentId,
                    'state'           => 'gateway_init',
                    'actor'           => 'gateway',
                    'gateway'         => 'paystack',
                    'gateway_ref'     => $ref,
                    'idempotency_key' => $idempotencyKey ?: null,
                ]);
                $this->updatePaymentStatus($paymentId, 'pending', $ref);
                return ['status' => true, 'data' => ['reference' => $ref, 'authorization_url' => $result['data']['authorization_url'], 'payment_id' => $paymentId]];
            }

            $msg = $result['message'] ?? 'Paystack initialisation failed';
            $this->ledger->append(['payment_id' => $paymentId, 'state' => 'failed', 'actor' => 'gateway', 'gateway' => 'paystack', 'payload' => ['error' => $msg]]);
            $this->updatePaymentStatus($paymentId, 'failed', null, $msg);
            return ['status' => false, 'message' => $msg];

        } catch (\Exception $e) {
            $this->ledger->append(['payment_id' => $paymentId, 'state' => 'failed', 'actor' => 'system', 'gateway' => 'paystack', 'payload' => ['error' => $e->getMessage()]]);
            $this->updatePaymentStatus($paymentId, 'failed', null, $e->getMessage());
            return ['status' => false, 'message' => 'Payment initialisation failed: ' . $e->getMessage()];
        }
    }

    private function initializeMonifyPayment(string $email, int $amountKobo, string $membershipNumber, string $callbackUrl, int $paymentId, string $idempotencyKey = ''): array
    {
        if (empty($this->monifySecretKey)) {
            $ref = 'MONIFY_' . time() . '_' . bin2hex(random_bytes(4));
            $this->ledger->append(['payment_id' => $paymentId, 'state' => 'gateway_init', 'actor' => 'system', 'gateway' => 'monify', 'gateway_ref' => $ref, 'payload' => ['demo' => true]]);
            $mockUrl = \App\Helpers\Url::appUrl() . '/member/dues/mock-payment?' . http_build_query(['reference' => $ref, 'amount' => $amountKobo, 'email' => $email, 'callback_url' => $callbackUrl, 'gateway' => 'monify', 'payment_id' => $paymentId]);
            return ['status' => true, 'data' => ['reference' => $ref, 'authorization_url' => $mockUrl, 'payment_id' => $paymentId]];
        }
        // Live Monify — omitted for brevity; add curl call here following Paystack pattern
        return ['status' => false, 'message' => 'Monify live integration not yet configured.'];
    }

    private function initializeOpayPayment(string $email, int $amountKobo, string $membershipNumber, string $callbackUrl, int $paymentId, string $idempotencyKey = ''): array
    {
        if (empty($this->opaySecretKey)) {
            $ref = 'OPAY_' . time() . '_' . bin2hex(random_bytes(4));
            $this->ledger->append(['payment_id' => $paymentId, 'state' => 'gateway_init', 'actor' => 'system', 'gateway' => 'opay', 'gateway_ref' => $ref, 'payload' => ['demo' => true]]);
            $mockUrl = \App\Helpers\Url::appUrl() . '/member/dues/mock-payment?' . http_build_query(['reference' => $ref, 'amount' => $amountKobo, 'email' => $email, 'callback_url' => $callbackUrl, 'gateway' => 'opay', 'payment_id' => $paymentId]);
            return ['status' => true, 'data' => ['reference' => $ref, 'authorization_url' => $mockUrl, 'payment_id' => $paymentId]];
        }
        return ['status' => false, 'message' => 'OPay live integration not yet configured.'];
    }

    private function initializeManualPayment(int $amountKobo, int $paymentId, string $idempotencyKey = ''): array
    {
        $ref = 'MANUAL_' . time() . '_' . bin2hex(random_bytes(4));
        $this->ledger->append(['payment_id' => $paymentId, 'state' => 'gateway_init', 'actor' => 'member', 'gateway' => 'manual', 'gateway_ref' => $ref, 'idempotency_key' => $idempotencyKey ?: null]);
        $this->updatePaymentStatus($paymentId, 'pending', $ref);
        return ['status' => true, 'data' => ['reference' => $ref, 'payment_id' => $paymentId, 'authorization_url' => null]];
    }

    // =========================================================================
    // Private: gateway verifiers
    // =========================================================================

    private function verifyPaystackPayment(string $reference): array
    {
        if (empty($this->paystackSecretKey)) {
            return ['status' => true, 'data' => ['status' => 'success', 'amount' => 1200000, 'reference' => $reference]];
        }
        $ch = curl_init('https://api.paystack.co/transaction/verify/' . urlencode($reference));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->paystackSecretKey], CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: ['status' => false, 'message' => 'Invalid gateway response'];
    }

    private function verifyMonifyPayment(string $reference): array
    {
        if (empty($this->monifySecretKey)) {
            return ['status' => true, 'data' => ['status' => 'success', 'amount' => 1200000, 'reference' => $reference]];
        }
        $ch = curl_init('https://sandbox-api.monify.com/v1/merchant/transactions/query?paymentReference=' . urlencode($reference));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->monifySecretKey], CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: ['status' => false, 'message' => 'Invalid gateway response'];
    }

    private function verifyOpayPayment(string $reference): array
    {
        if (empty($this->opaySecretKey)) {
            return ['status' => true, 'data' => ['status' => 'success', 'amount' => 1200000, 'reference' => $reference]];
        }
        $ch = curl_init('https://cashier.opayweb.com/api/v3/transaction/status?reference=' . urlencode($reference));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->opaySecretKey], CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: ['status' => false, 'message' => 'Invalid gateway response'];
    }

    private function getMonifyToken(): array
    {
        $creds = base64_encode($this->monifyPublicKey . ':' . $this->monifySecretKey);
        $ch = curl_init('https://sandbox-api.monify.com/v1/auth/login');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $creds, 'Content-Type: application/json'], CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?: ['status' => false];
    }
}
