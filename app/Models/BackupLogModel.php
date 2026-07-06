<?php

namespace App\Models;

class BackupLogModel extends BaseModel
{
    /**
     * Log a backup operation
     */
    public function logBackup($backupData)
    {
        $sql = "INSERT INTO backup_logs (
                    backup_id, filename, filepath, type, size, duration, 
                    status, error_message, created_at
                ) VALUES (
                    :backup_id, :filename, :filepath, :type, :size, :duration,
                    :status, :error_message, :created_at
                )";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($backupData);
    }

    /**
     * Get last successful backup
     */
    public function getLastSuccessfulBackup()
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE status = 'success' 
                ORDER BY created_at DESC 
                LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get last full backup
     */
    public function getLastFullBackup()
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE status = 'success' AND type = 'full' 
                ORDER BY created_at DESC 
                LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats($startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total_backups,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_backups,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_backups,
                    AVG(duration) as avg_duration,
                    SUM(size) as total_size,
                    MAX(created_at) as last_backup,
                    MIN(created_at) as first_backup
                FROM backup_logs";

        $params = [];
        
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate . ' 00:00:00';
            $params['end_date'] = $endDate . ' 23:59:59';
        }

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }

    /**
     * Get backups by type
     */
    public function getBackupsByType($type, $limit = 50, $offset = 0)
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE type = :type 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get recent backups
     */
    public function getRecentBackups($limit = 20)
    {
        $sql = "SELECT * FROM backup_logs 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get failed backups
     */
    public function getFailedBackups($limit = 50)
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE status = 'failed' 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get backup by ID
     */
    public function getBackupById($backupId)
    {
        $sql = "SELECT * FROM backup_logs WHERE backup_id = :backup_id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['backup_id' => $backupId]);
        
        return $stmt->fetch();
    }

    /**
     * Get backup by filename
     */
    public function getBackupByFilename($filename)
    {
        $sql = "SELECT * FROM backup_logs WHERE filename = :filename";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['filename' => $filename]);
        
        return $stmt->fetch();
    }

    /**
     * Update backup status
     */
    public function updateBackupStatus($backupId, $status, $errorMessage = null)
    {
        $sql = "UPDATE backup_logs SET 
                    status = :status,
                    error_message = :error_message,
                    updated_at = NOW()
                WHERE backup_id = :backup_id";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute([
            'backup_id' => $backupId,
            'status' => $status,
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Delete old backup logs
     */
    public function cleanOldBackupLogs($days = 90)
    {
        $sql = "DELETE FROM backup_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Get backup trends
     */
    public function getBackupTrends($days = 30)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    type,
                    status,
                    COUNT(*) as count,
                    AVG(duration) as avg_duration,
                    SUM(size) as total_size
                FROM backup_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at), type, status
                ORDER BY date DESC, type, status";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get backup performance metrics
     */
    public function getBackupPerformanceMetrics($days = 30)
    {
        $sql = "SELECT 
                    type,
                    COUNT(*) as total_backups,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_backups,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_backups,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration,
                    MIN(duration) as min_duration,
                    AVG(size) as avg_size,
                    SUM(size) as total_size
                FROM backup_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY type
                ORDER BY total_backups DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get backup errors
     */
    public function getBackupErrors($limit = 50)
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE status = 'failed' AND error_message IS NOT NULL
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Search backup logs
     */
    public function searchBackupLogs($searchTerm, $limit = 100)
    {
        $sql = "SELECT * FROM backup_logs 
                WHERE backup_id LIKE :search_term 
                OR filename LIKE :search_term 
                OR type LIKE :search_term 
                OR status LIKE :search_term
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':search_term', '%' . $searchTerm . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get backup summary by date range
     */
    public function getBackupSummaryByDateRange($startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_backups,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_backups,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_backups,
                    AVG(duration) as avg_duration,
                    SUM(size) as total_size
                FROM backup_logs 
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
} 