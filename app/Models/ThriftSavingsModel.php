<?php
namespace App\Models;

class ThriftSavingsModel extends BaseModel
{
    public function getSummary($userId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT 
                SUM(amount) as total_savings, 
                MAX(payment_date) as last_payment_date,
                COUNT(*) as payment_count
            FROM rls_thrift_savings
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getHistory($userId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT amount, payment_date 
            FROM rls_thrift_savings 
            ORDER BY payment_date DESC 
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addPayment($userId, $amount, $paymentDate, $reference = null)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO thrift_savings (user_id, amount, payment_date) VALUES (:user_id, :amount, :payment_date)");
        return $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'payment_date' => $paymentDate
        ]);
    }

    public function getPaymentById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM thrift_savings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updatePayment($id, $amount, $paymentDate)
    {
        $stmt = $this->getConnection()->prepare("UPDATE thrift_savings SET amount = :amount, payment_date = :payment_date WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'amount' => $amount,
            'payment_date' => $paymentDate
        ]);
    }

    public function deletePayment($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM thrift_savings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getThriftWithMemberInfo($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT ts.*, m.firstname, m.surname, m.membership_number, m.email
            FROM thrift_savings ts
            JOIN members m ON ts.user_id = m.id
            WHERE ts.payment_date BETWEEN :date_from AND :date_to
            ORDER BY ts.payment_date DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        return $stmt->fetchAll();
    }

    public function getPaginatedThrift($page = 1, $perPage = 10, $search = '')
    {
        $offset = ($page - 1) * $perPage;

        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(m.firstname LIKE :search_firstname OR m.surname LIKE :search_surname OR m.membership_number LIKE :search_membership)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT ts.*, m.firstname, m.surname, m.membership_number 
            FROM thrift_savings ts
            JOIN members m ON ts.user_id = m.id
            $whereClause
            ORDER BY ts.payment_date DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalThriftCount($search = '')
    {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(m.firstname LIKE :search_firstname OR m.surname LIKE :search_surname OR m.membership_number LIKE :search_membership)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT COUNT(*) 
            FROM thrift_savings ts
            JOIN members m ON ts.user_id = m.id
            $whereClause
        ";

        $stmt = $this->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchColumn();
    }
}