<?php

namespace App\Models;

class ErrorLogModel extends BaseModel
{
    /**
     * Log an error to the database
     */
    public function logError($errorData)
    {
        $sql = "INSERT INTO error_logs (
            error_type, message, file, line, trace, user_id, 
            ip_address, user_agent, request_uri, request_method, 
            context, created_at
        ) VALUES (
            :error_type, :message, :file, :line, :trace, :user_id,
            :ip_address, :user_agent, :request_uri, :request_method,
            :context, :created_at
        )";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($errorData);
    }

    /**
     * Get recent error count
     */
    public function getRecentErrorCount($hours = 24)
    {
        $sql = "SELECT COUNT(*) as count FROM error_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['hours' => $hours]);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }

    /**
     * Get error statistics for a date range
     */
    public function getErrorStats($startDate, $endDate)
    {
        $sql = "SELECT 
                    error_type,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM error_logs 
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY error_type, DATE(created_at)
                ORDER BY date DESC, count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get errors by type
     */
    public function getErrorsByType($type, $limit = 100)
    {
        $sql = "SELECT * FROM error_logs 
                WHERE error_type = :type 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get errors for a specific user
     */
    public function getErrorsByUser($userId, $limit = 50)
    {
        $sql = "SELECT * FROM error_logs 
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
     * Get error summary by date
     */
    public function getErrorSummaryByDate($startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_errors,
                    COUNT(DISTINCT error_type) as unique_error_types,
                    COUNT(DISTINCT user_id) as affected_users
                FROM error_logs 
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
     * Get most common errors
     */
    public function getMostCommonErrors($limit = 10)
    {
        $sql = "SELECT 
                    error_type,
                    message,
                    COUNT(*) as occurrence_count,
                    MIN(created_at) as first_occurrence,
                    MAX(created_at) as last_occurrence
                FROM error_logs 
                GROUP BY error_type, message
                ORDER BY occurrence_count DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get errors by IP address (for security monitoring)
     */
    public function getErrorsByIP($ipAddress, $limit = 50)
    {
        $sql = "SELECT * FROM error_logs 
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
     * Clean old error logs
     */
    public function cleanOldLogs($days = 30)
    {
        $sql = "DELETE FROM error_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Get error rate by hour
     */
    public function getErrorRateByHour($date)
    {
        $sql = "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as error_count
                FROM error_logs 
                WHERE DATE(created_at) = :date
                GROUP BY HOUR(created_at)
                ORDER BY hour";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['date' => $date]);

        return $stmt->fetchAll();
    }

    /**
     * Get errors affecting specific endpoints
     */
    public function getErrorsByEndpoint($endpoint, $limit = 50)
    {
        $sql = "SELECT * FROM error_logs 
                WHERE request_uri LIKE :endpoint 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':endpoint', '%' . $endpoint . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get error trends over time
     */
    public function getErrorTrends($days = 7)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    error_type,
                    COUNT(*) as count
                FROM error_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at), error_type
                ORDER BY date DESC, count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get critical errors (errors that occur frequently)
     */
    public function getCriticalErrors($threshold = 5, $hours = 24)
    {
        $sql = "SELECT 
                    error_type,
                    message,
                    COUNT(*) as occurrence_count
                FROM error_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
                GROUP BY error_type, message
                HAVING occurrence_count >= :threshold
                ORDER BY occurrence_count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':hours', $hours, \PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get error details by ID
     */
    public function getErrorById($id)
    {
        $sql = "SELECT * FROM error_logs WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Mark error as resolved
     */
    public function markErrorResolved($id, $resolution = '')
    {
        $sql = "UPDATE error_logs 
                SET resolved = 1, resolution = :resolution, resolved_at = NOW()
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'resolution' => $resolution
        ]);
    }

    /**
     * Get unresolved errors
     */
    public function getUnresolvedErrors($limit = 100)
    {
        $sql = "SELECT * FROM error_logs 
                WHERE resolved = 0 OR resolved IS NULL
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
} 