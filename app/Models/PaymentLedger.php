<?php

namespace App\Models;

/**
 * PaymentLedger — append-only event store for payment state transitions.
 *
 * Design rules:
 *  - NEVER UPDATE or DELETE a row. Every state change = a new INSERT.
 *  - The canonical state of a payment is the state of its LATEST ledger row.
 *  - All money amounts are stored in the smallest currency unit (kobo).
 */
class PaymentLedger extends BaseModel
{
    // -------------------------------------------------------------------------
    // Write (append only)
    // -------------------------------------------------------------------------

    /**
     * Append one event to the ledger.
     *
     * @param array $data {
     *   int    payment_id       required
     *   string state            required — one of the ENUM values
     *   string actor            'member'|'admin'|'gateway'|'webhook'|'system'
     *   int    actor_id         nullable
     *   int    amount_kobo      defaults to 0
     *   string currency         defaults to 'NGN'
     *   string gateway          e.g. 'paystack'
     *   string gateway_ref      gateway transaction reference
     *   string gateway_event_id webhook event.id (unique)
     *   string idempotency_key  client UUID
     *   mixed  payload          array|null — stored as JSON
     *   string ip_address
     * }
     * @return int  new ledger row id
     */
    public function append(array $data): int
    {
        $db = $this->getConnection();

        // Calculate next sequence number for this payment
        $seq = $this->nextSequence((int)$data['payment_id']);

        $stmt = $db->prepare("
            INSERT INTO payment_ledger
                (payment_id, sequence, state, actor, actor_id,
                 amount_kobo, currency, gateway, gateway_ref,
                 gateway_event_id, idempotency_key, payload, ip_address)
            VALUES
                (:payment_id, :sequence, :state, :actor, :actor_id,
                 :amount_kobo, :currency, :gateway, :gateway_ref,
                 :gateway_event_id, :idempotency_key, :payload, :ip_address)
        ");

        $stmt->execute([
            ':payment_id'        => (int)$data['payment_id'],
            ':sequence'          => $seq,
            ':state'             => $data['state'],
            ':actor'             => $data['actor']           ?? 'system',
            ':actor_id'          => $data['actor_id']        ?? null,
            ':amount_kobo'       => (int)($data['amount_kobo'] ?? 0),
            ':currency'          => $data['currency']        ?? 'NGN',
            ':gateway'           => $data['gateway']         ?? '',
            ':gateway_ref'       => $data['gateway_ref']     ?? null,
            ':gateway_event_id'  => $data['gateway_event_id'] ?? null,
            ':idempotency_key'   => $data['idempotency_key'] ?? null,
            ':payload'           => isset($data['payload'])
                                    ? json_encode($data['payload'], JSON_UNESCAPED_UNICODE)
                                    : null,
            ':ip_address'        => $data['ip_address']      ?? ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);

        return (int)$db->lastInsertId();
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /** Full chronological timeline for one payment. */
    public function getTimeline(int $paymentId): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM payment_ledger
            WHERE payment_id = :pid
            ORDER BY sequence ASC, id ASC
        ");
        $stmt->execute([':pid' => $paymentId]);
        return $stmt->fetchAll();
    }

    /** Latest state for one payment. */
    public function getLatestState(int $paymentId): ?array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM payment_ledger
            WHERE payment_id = :pid
            ORDER BY sequence DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':pid' => $paymentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Check whether a webhook event has already been recorded (dedup). */
    public function webhookEventExists(string $gatewayEventId): bool
    {
        $stmt = $this->getConnection()->prepare("
            SELECT id FROM payment_ledger
            WHERE gateway_event_id = :eid
            LIMIT 1
        ");
        $stmt->execute([':eid' => $gatewayEventId]);
        return (bool)$stmt->fetch();
    }

    /** All ledger rows for a member across all payments — scoped via rls_payment_ledger. */
    public function getTimelineByMember(int $memberId, int $limit = 50): array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT rl.*, pt.membership_number, pt.payment_type
            FROM rls_payment_ledger rl
            JOIN payment_transactions pt ON rl.payment_id = pt.id
            ORDER BY rl.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function nextSequence(int $paymentId): int
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COALESCE(MAX(sequence), 0) + 1
            FROM payment_ledger
            WHERE payment_id = :pid
        ");
        $stmt->execute([':pid' => $paymentId]);
        return (int)$stmt->fetchColumn();
    }
}
