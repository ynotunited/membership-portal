<?php

namespace App\Controllers;

use App\Models\BaseModel;
use App\Models\PaymentLedger;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\ThriftSavingsModel;
use App\Models\MemberModel;

/**
 * WebhookController — secure asynchronous webhook ingestion.
 *
 * Security guarantees:
 *  1. HMAC signature validated before any data is read.
 *  2. Raw body stored in payment_webhooks BEFORE processing (audit trail).
 *  3. gateway_event_id uniqueness checked — duplicate events are ignored (idempotent).
 *  4. Every accepted event appends a row to payment_ledger (immutable).
 *  5. No session, no CSRF — webhooks are server-to-server; auth = HMAC only.
 */
class WebhookController extends BaseController
{
    private PaymentLedger $ledger;

    public function __construct()
    {
        $this->ledger = new PaymentLedger();
        // No session or CSRF needed — webhook endpoints are server-to-server
    }

    // -------------------------------------------------------------------------
    // Paystack
    // -------------------------------------------------------------------------

    public function paystack(): void
    {
        $rawBody   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        $secret    = getenv('PAYSTACK_SECRET_KEY') ?: '';

        // 1. Validate HMAC-SHA512 signature
        if (empty($secret) || !$this->validateHmac($rawBody, $signature, $secret, 'sha512')) {
            $this->rejectWebhook('paystack', 'Invalid signature');
        }

        $event = json_decode($rawBody, true);
        if (!$event || empty($event['id'])) {
            $this->rejectWebhook('paystack', 'Malformed payload');
        }

        $eventId   = (string)$event['id'];
        $eventType = $event['event'] ?? 'unknown';

        // 2. Store raw body (before processing)
        $webhookRowId = $this->storeRawWebhook('paystack', $eventId, $eventType, true, $rawBody);

        // 3. Deduplicate — if event already processed, return 200 immediately
        if ($this->ledger->webhookEventExists($eventId)) {
            $this->markProcessed($webhookRowId);
            $this->jsonOk('Already processed');
        }

        // 4. Process
        try {
            $this->processPaystackEvent($event, $eventId, $eventType);
            $this->markProcessed($webhookRowId);
        } catch (\Throwable $e) {
            $this->markFailed($webhookRowId, $e->getMessage());
            // Still return 200 so Paystack doesn't retry indefinitely;
            // the error is logged in payment_webhooks.error
        }

        $this->jsonOk('Processed');
    }

    // -------------------------------------------------------------------------
    // Paystack event processor
    // -------------------------------------------------------------------------

    private function processPaystackEvent(array $event, string $eventId, string $eventType): void
    {
        $data      = $event['data'] ?? [];
        $reference = $data['reference'] ?? '';
        $amountKobo = (int)($data['amount'] ?? 0);
        $status    = $data['status'] ?? '';

        // Find the payment_transactions row by gateway reference
        $db   = (new BaseModel())->getConnection();
        $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();

        if (!$payment) {
            // Unknown reference — log and ignore
            error_log("Webhook: unknown reference {$reference}");
            return;
        }

        $paymentId = (int)$payment['id'];

        // Map Paystack status → ledger state
        $stateMap = [
            'success'   => 'captured',
            'failed'    => 'failed',
            'abandoned' => 'cancelled',
            'reversed'  => 'refunded',
        ];
        $ledgerState = $stateMap[$status] ?? 'webhook_received';

        // 5. Append to immutable ledger
        $this->ledger->append([
            'payment_id'       => $paymentId,
            'state'            => $ledgerState,
            'actor'            => 'webhook',
            'amount_kobo'      => $amountKobo,
            'gateway'          => 'paystack',
            'gateway_ref'      => $reference,
            'gateway_event_id' => $eventId,
            'payload'          => $data,
        ]);

        // 6. Sync mutable status column and fulfil benefits
        if ($ledgerState === 'captured') {
            // Avoid double-crediting — only fulfil if current status is pending
            if ($payment['status'] === 'pending') {
                $this->fulfilPayment($payment, $amountKobo);
            }
            $db->prepare("UPDATE payment_transactions SET status='success', reference=?, updated_at=NOW() WHERE id=?")
               ->execute([$reference, $paymentId]);
        } elseif (in_array($ledgerState, ['failed', 'cancelled'])) {
            $db->prepare("UPDATE payment_transactions SET status='failed', updated_at=NOW() WHERE id=?")
               ->execute([$paymentId]);
        } elseif ($ledgerState === 'refunded') {
            $db->prepare("UPDATE payment_transactions SET status='refunded', updated_at=NOW() WHERE id=?")
               ->execute([$paymentId]);
        }
    }

    // -------------------------------------------------------------------------
    // Benefit fulfilment (called only once per payment, guarded by status check)
    // -------------------------------------------------------------------------

    private function fulfilPayment(array $payment, int $amountKobo): void
    {
        $memberModel = new MemberModel();
        $member = $this->getMemberByMembershipNumber($payment['membership_number']);
        if (!$member) {
            return;
        }

        $memberId   = (int)$member['id'];
        $amountNgn  = $amountKobo / 100;
        $date       = date('Y-m-d');
        $type       = $payment['payment_type'] ?? 'general';

        switch ($type) {
            case 'annual_dues':
                (new AnnualDuesModel())->addAnnualDues($memberId, $amountNgn, 'paid', $date, 'Webhook: Paystack captured');
                $memberModel->getConnection()
                    ->prepare("UPDATE members SET annual_dues_status='paid', annual_dues_date=? WHERE id=?")
                    ->execute([$date, $memberId]);
                break;

            case 'shares':
                $shares = (int)floor($amountNgn / 100);
                (new SharesModel())->addShares($memberId, $shares, $amountNgn, $date, 'Webhook: Paystack captured');
                break;

            case 'thrift_savings':
                (new ThriftSavingsModel())->addPayment($memberId, $amountNgn, $date);
                break;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validateHmac(string $body, string $signature, string $secret, string $algo): bool
    {
        if (empty($signature)) {
            return false;
        }
        $expected = hash_hmac($algo, $body, $secret);
        return hash_equals($expected, $signature);
    }

    private function storeRawWebhook(
        string $gateway,
        string $eventId,
        string $eventType,
        bool   $signatureValid,
        string $rawBody
    ): int {
        $db   = (new BaseModel())->getConnection();
        $stmt = $db->prepare("
            INSERT IGNORE INTO payment_webhooks
                (gateway, gateway_event_id, event_type, signature_valid, raw_body, received_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$gateway, $eventId, $eventType, (int)$signatureValid, $rawBody]);
        return (int)$db->lastInsertId();
    }

    private function markProcessed(int $id): void
    {
        if (!$id) return;
        (new BaseModel())->getConnection()
            ->prepare("UPDATE payment_webhooks SET processed=1, processed_at=NOW() WHERE id=?")
            ->execute([$id]);
    }

    private function markFailed(int $id, string $error): void
    {
        if (!$id) return;
        (new BaseModel())->getConnection()
            ->prepare("UPDATE payment_webhooks SET processed=0, error=? WHERE id=?")
            ->execute([$error, $id]);
    }

    private function getMemberByMembershipNumber(string $number): ?array
    {
        $stmt = (new BaseModel())->getConnection()
            ->prepare("SELECT * FROM members WHERE membership_number = ? LIMIT 1");
        $stmt->execute([$number]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function rejectWebhook(string $gateway, string $reason): never
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $reason]);
        exit;
    }

    private function jsonOk(string $message): never
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['ok' => $message]);
        exit;
    }
}
