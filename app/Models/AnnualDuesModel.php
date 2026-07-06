<?php
namespace App\Models;

class AnnualDuesModel extends BaseModel
{
    public function addAnnualDues($memberId, $amount, $status, $paymentDate, $notes = null)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO annual_dues (member_id, amount, status, payment_date, notes) VALUES (:member_id, :amount, :status, :payment_date, :notes)");
        return $stmt->execute([
            'member_id' => $memberId,
            'amount' => $amount,
            'status' => $status,
            'payment_date' => $paymentDate,
            'notes' => $notes
        ]);
    }

    public function addDues($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO annual_dues (member_id, amount, status, payment_date, notes) 
            VALUES (:member_id, :amount, :status, :payment_date, :notes)
        ");
        return $stmt->execute($data);
    }

    public function getAnnualDuesByMember($memberId)
    {
        // Use the RLS view — only returns rows where member_id = @app_member_id.
        // RowPolicy::ensureDbSync() guarantees the session variable is set.
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM rls_annual_dues ORDER BY payment_date DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMemberDuesHistory($memberId)
    {
        return $this->getAnnualDuesByMember($memberId);
    }

    public function getAllDuesWithMemberInfo()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ad.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number
            FROM annual_dues ad
            LEFT JOIN members m ON ad.member_id = m.id
            ORDER BY ad.payment_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDuesForYear($memberId, $year)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_annual_dues
            WHERE YEAR(payment_date) = :year
              AND status = 'Paid'
            LIMIT 1
        ");
        $stmt->execute(['year' => $year]);
        return $stmt->fetch();
    }

    public function hasPaidForYear($memberId, $year)
    {
        $dues = $this->getDuesForYear($memberId, $year);
        return $dues !== false;
    }
    public function getTotalRevenue()
    {
        $stmt = $this->getConnection()->query("SELECT SUM(amount) as total FROM annual_dues WHERE status = 'Paid'");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getMonthlyRevenue($month)
    {
        $stmt = $this->getConnection()->prepare("SELECT SUM(amount) as total FROM annual_dues WHERE status = 'Paid' AND DATE_FORMAT(payment_date, '%Y-%m') = :month");
        $stmt->execute(['month' => $month]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRecentDues($limit = 5)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT ad.*, m.firstname, m.surname 
            FROM annual_dues ad 
            JOIN members m ON ad.member_id = m.id 
            WHERE ad.status = 'Paid' 
            ORDER BY ad.payment_date DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getDuesById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM annual_dues WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateDues($id, $data)
    {
        // Explicit whitelist – never allow arbitrary column names from user input
        $allowed = ['amount', 'status', 'notes', 'payment_date'];
        $setClauses = [];
        $params = ['id' => (int)$id];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $setClauses[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $sql = 'UPDATE annual_dues SET ' . implode(', ', $setClauses) . ' WHERE id = :id';
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteDues($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM annual_dues WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getDuesWithMemberInfo($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ad.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number
            FROM annual_dues ad
            LEFT JOIN members m ON ad.member_id = m.id
            WHERE ad.payment_date BETWEEN :date_from AND :date_to
            ORDER BY ad.payment_date DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        return $stmt->fetchAll();
    }

    public function getPaginatedDues($page = 1, $perPage = 10, $search = '', $status = '')
    {
        $offset = ($page - 1) * $perPage;

        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(m.firstname LIKE :search_firstname OR m.surname LIKE :search_surname OR m.membership_number LIKE :search_membership OR m.email LIKE :search_email)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
            $params[':search_email'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "ad.status = :status";
            $params[':status'] = $status;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT 
                ad.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number
            FROM annual_dues ad
            LEFT JOIN members m ON ad.member_id = m.id
            $whereClause
            ORDER BY ad.payment_date DESC
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

    public function getTotalDuesCount($search = '', $status = '')
    {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(m.firstname LIKE :search_firstname OR m.surname LIKE :search_surname OR m.membership_number LIKE :search_membership OR m.email LIKE :search_email)";
            $params[':search_firstname'] = '%' . $search . '%';
            $params[':search_surname'] = '%' . $search . '%';
            $params[':search_membership'] = '%' . $search . '%';
            $params[':search_email'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "ad.status = :status";
            $params[':status'] = $status;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT COUNT(*) 
            FROM annual_dues ad
            LEFT JOIN members m ON ad.member_id = m.id
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