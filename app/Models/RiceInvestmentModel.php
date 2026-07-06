<?php
namespace App\Models;

class RiceInvestmentModel extends BaseModel
{
    public function createInvestment($memberId, $amount, $paymentProof, $notes = '')
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO rice_investments (member_id, amount, payment_proof, status, notes, created_at) 
            VALUES (:member_id, :amount, :payment_proof, 'pending', :notes, NOW())
        ");
        return $stmt->execute([
            'member_id' => $memberId,
            'amount' => $amount,
            'payment_proof' => $paymentProof,
            'notes' => $notes
        ]);
    }

    public function getInvestmentsByMember($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_rice_investments 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllInvestmentsWithMemberInfo()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT i.*, m.firstname, m.surname, m.membership_number, m.email, m.contact_number 
            FROM rice_investments i
            JOIN members m ON i.member_id = m.id
            ORDER BY i.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getInvestmentById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM rice_investments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->getConnection()->prepare("UPDATE rice_investments SET status = :status WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'status' => $status
        ]);
    }

    public function getTotalInvestmentByMember($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT SUM(amount) as total 
            FROM rls_rice_investments 
            WHERE status = 'approved'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    public function getRiceWithMemberInfo($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT i.*, m.firstname, m.surname, m.membership_number, m.email
            FROM rice_investments i
            JOIN members m ON i.member_id = m.id
            WHERE i.created_at BETWEEN :date_from AND :date_to
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ]);
        return $stmt->fetchAll();
    }
}
