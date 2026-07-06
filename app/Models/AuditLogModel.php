<?php

namespace App\Models;

class AuditLogModel extends BaseModel
{
    /**
     * Log a user action to the database
     */
    public function logAction($auditData)
    {
        $sql = "INSERT INTO audit_logs (
            user_id, action, details, ip_address, user_agent, 
            request_uri, request_method, created_at
        ) VALUES (
            :user_id, :action, :details, :ip_address, :user_agent,
            :request_uri, :request_method, :created_at
        )";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($auditData);
    }

    /**
     * Get action statistics for a date range
     */
    public function getActionStats($startDate, $endDate)
    {
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users,
                    DATE(created_at) as date
                FROM audit_logs 
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY action, DATE(created_at)
                ORDER BY date DESC, count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get actions by user
     */
    public function getActionsByUser($userId, $limit = 100)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get actions by type
     */
    public function getActionsByType($action, $limit = 100)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE action = :action 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get recent actions
     */
    public function getRecentActions($hours = 24, $limit = 50)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':hours', $hours, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get action summary by date
     */
    public function getActionSummaryByDate($startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_actions,
                    COUNT(DISTINCT action) as unique_actions,
                    COUNT(DISTINCT user_id) as active_users
                FROM audit_logs 
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at)
                ORDER BY date DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get most common actions
     */
    public function getMostCommonActions($limit = 10)
    {
        $sql = "SELECT 
                    action,
                    COUNT(*) as occurrence_count,
                    COUNT(DISTINCT user_id) as unique_users,
                    MIN(created_at) as first_occurrence,
                    MAX(created_at) as last_occurrence
                FROM audit_logs 
                GROUP BY action
                ORDER BY occurrence_count DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get actions by IP address (for security monitoring)
     */
    public function getActionsByIP($ipAddress, $limit = 50)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE ip_address = :ip_address 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs($days = 90)
    {
        $sql = "DELETE FROM audit_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Get action trends over time
     */
    public function getActionTrends($days = 7)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    action,
                    COUNT(*) as count
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at), action
                ORDER BY date DESC, count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30)
    {
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    MAX(created_at) as last_action
                FROM audit_logs 
                WHERE user_id = :user_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY action
                ORDER BY count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get audit log by ID
     */
    public function getAuditLogById($id)
    {
        $sql = "SELECT * FROM audit_logs WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Search audit logs
     */
    public function searchAuditLogs($searchTerm, $limit = 100)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE action LIKE :search_term 
                OR details LIKE :search_term 
                OR request_uri LIKE :search_term
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':search_term', '%' . $searchTerm . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get recent audit logs with user information for activity feed
     */
    public function getRecentActivityFeed($limit = 10)
    {
        $sql = "SELECT al.*, 
                       COALESCE(u.email, m.firstname, 'System') as firstname,
                       COALESCE(NULL, m.surname, '') as surname
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN members m ON al.user_id = m.id
                ORDER BY al.created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get recent audit logs with user information for real-time updates
     */
    public function getRecentActivityFeedSince($since, $limit = 10)
    {
        $sql = "SELECT al.*, 
                       COALESCE(u.email, m.firstname, 'System') as firstname,
                       COALESCE(NULL, m.surname, '') as surname
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN members m ON al.user_id = m.id
                WHERE al.created_at > :since
                ORDER BY al.created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':since', $since);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
} 