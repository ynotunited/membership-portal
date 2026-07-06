<?php
namespace App\Models;

class SharesModel extends BaseModel
{
    public function addShares($userId, $shares, $amount, $paymentDate, $notes = '')
    {
        $db = $this->getConnection();

        try {
            $sql = "INSERT INTO shares (
                member_id, number_of_shares, amount, purchase_date, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $userId,
                $shares,
                $amount,
                $paymentDate,
                $notes
            ]);

            return $result;
        } catch (\Exception $e) {
            error_log('Error adding shares: ' . $e->getMessage());
            return false;
        }
    }

    public function getSharesByMember($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare(
            "SELECT * FROM rls_shares ORDER BY purchase_date DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalSharesByMember($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare(
            "SELECT SUM(number_of_shares) as total_shares FROM rls_shares"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['total_shares'] : 0;
    }

    public function getAllSharesWithMemberInfo()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                s.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number
            FROM shares s
            LEFT JOIN members m ON s.member_id = m.id
            ORDER BY s.purchase_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSharesByMemberId($memberId)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM shares WHERE member_id = :member_id ORDER BY purchase_date DESC");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }



    public function getSharesWithMemberInfo($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                s.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number
            FROM shares s
            LEFT JOIN members m ON s.member_id = m.id
            WHERE s.purchase_date BETWEEN :date_from AND :date_to
            ORDER BY s.purchase_date DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        return $stmt->fetchAll();
    }

    public function getTotalSharesByUser($userId)
    {
        $stmt = $this->getConnection()->prepare("SELECT SUM(number_of_shares) as total_shares FROM shares WHERE member_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['total_shares'] : 0;
    }

    public function getMemberShares($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT 
                SUM(number_of_shares) as total_shares,
                SUM(amount) as total_amount,
                MAX(purchase_date) as last_purchase_date,
                COUNT(*) as purchase_count
            FROM rls_shares
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getMemberSharesHistory($memberId)
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_shares 
            ORDER BY purchase_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getShareById($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM shares WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateShare($id, $data)
    {
        // Explicit whitelist – never allow arbitrary column names from user input
        $allowed = ['number_of_shares', 'amount', 'notes', 'purchase_date'];
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

        $sql = 'UPDATE shares SET ' . implode(', ', $setClauses) . ' WHERE id = :id';
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteShare($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM shares WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getTotalRevenue()
    {
        $stmt = $this->getConnection()->query("SELECT SUM(amount) as total FROM shares");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getMonthlyRevenue($month)
    {
        $stmt = $this->getConnection()->prepare("SELECT SUM(amount) as total FROM shares WHERE DATE_FORMAT(purchase_date, '%Y-%m') = :month");
        $stmt->execute(['month' => $month]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRecentShares($limit = 5)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT s.*, m.firstname, m.surname 
            FROM shares s 
            JOIN members m ON s.member_id = m.id 
            ORDER BY s.purchase_date DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaginatedShares($page = 1, $perPage = 10, $search = '')
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

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT 
                s.*,
                m.membership_number,
                m.firstname,
                m.surname,
                m.chapter,
                m.email,
                m.contact_number,
                (s.amount / s.number_of_shares) as amount_per_share,
                s.amount as total_amount
            FROM shares s
            LEFT JOIN members m ON s.member_id = m.id
            $whereClause
            ORDER BY s.purchase_date DESC
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

    public function getTotalSharesCount($search = '')
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

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT COUNT(*) 
            FROM shares s
            LEFT JOIN members m ON s.member_id = m.id
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