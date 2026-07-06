<?php

namespace App\Models;

/**
 * PaymentIdempotency — client-generated UUID deduplication store.
 *
 * Flow:
 *  1. Client sends X-Idempotency-Key: <uuid-v4> header (or POST field).
 *  2. Server calls check() before doing any work.
 *     - If found & not expired → return cached response immediately.
 *     - If found & payload changed → reject with 422.
 *     - If not found → proceed and call store() when done.
 */
class PaymentIdempotency extends BaseModel
{
    /** How long a key is valid (seconds). Default 24 hours. */
    private const TTL = 86400;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Build the request fingerprint used to detect changed payloads.
     * Hash covers the financially significant fields only.
     */
    public static function requestHash(
        int    $amountKobo,
        string $email,
        string $paymentType,
        string $gateway
    ): string {
        return hash('sha256', implode('|', [$amountKobo, strtolower($email), $paymentType, $gateway]));
    }

    /**
     * Look up an idempotency key.
     *
     * @return array|null  null = key not seen; array = cached row
     */
    public function find(string $key): ?array
    {
        // Purge expired first (lazy cleanup)
        $this->purgeExpired();

        $stmt = $this->getConnection()->prepare("
            SELECT * FROM payment_idempotency
            WHERE idempotency_key = :key AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Store the result of a successful payment initialisation.
     *
     * @param string $key          UUID from client
     * @param string $requestHash  result of requestHash()
     * @param int    $paymentId    payment_transactions.id
     * @param array  $response     the array that was returned to the client
     * @param int    $httpStatus
     */
    public function store(
        string $key,
        string $requestHash,
        int    $paymentId,
        array  $response,
        int    $httpStatus = 200
    ): void {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO payment_idempotency
                (idempotency_key, payment_id, request_hash, response_body, http_status, expires_at)
            VALUES
                (:key, :pid, :hash, :body, :status, DATE_ADD(NOW(), INTERVAL :ttl SECOND))
            ON DUPLICATE KEY UPDATE
                -- If re-stored with same key (race condition), keep original
                idempotency_key = idempotency_key
        ");
        $stmt->execute([
            ':key'    => $key,
            ':pid'    => $paymentId,
            ':hash'   => $requestHash,
            ':body'   => json_encode($response, JSON_UNESCAPED_UNICODE),
            ':status' => $httpStatus,
            ':ttl'    => self::TTL,
        ]);
    }

    /**
     * Validate that the idempotency key is a well-formed UUID v4.
     */
    public static function isValidUuid(string $key): bool
    {
        return (bool)preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $key
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function purgeExpired(): void
    {
        $this->getConnection()
             ->prepare("DELETE FROM payment_idempotency WHERE expires_at <= NOW()")
             ->execute();
    }
}
