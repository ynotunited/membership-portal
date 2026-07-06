<?php

namespace App\Console;

use App\Helpers\BackupManager;
use App\Helpers\Monitoring;
use App\Models\BackupLogModel;

class BackupScheduler
{
    private $backupManager;
    private $monitoring;
    private $backupLogModel;

    public function __construct()
    {
        $this->backupManager = BackupManager::getInstance();
        $this->monitoring = Monitoring::getInstance();
        $this->backupLogModel = new BackupLogModel();
    }

    /**
     * Run scheduled backups
     */
    public function runScheduledBackups()
    {
        try {
            $this->monitoring->logInfo('Starting scheduled backup check', [
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            $schedules = $this->getActiveSchedules();
            
            foreach ($schedules as $schedule) {
                if ($this->shouldRunBackup($schedule)) {
                    $this->executeScheduledBackup($schedule);
                }
            }

            $this->monitoring->logInfo('Scheduled backup check completed', [
                'schedules_checked' => count($schedules)
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'scheduled_backup_check'
            ]);
        }
    }

    /**
     * Execute a scheduled backup
     */
    private function executeScheduledBackup($schedule)
    {
        try {
            $this->monitoring->logInfo('Executing scheduled backup', [
                'schedule_id' => $schedule['id'],
                'schedule_name' => $schedule['name'],
                'type' => $schedule['type']
            ]);

            // Create backup
            $backupData = $this->backupManager->createBackup($schedule['type'], $schedule['options'] ?? []);

            // Update schedule last run
            $this->updateScheduleLastRun($schedule['id']);

            // Send notification if enabled
            if ($this->shouldSendNotification($schedule)) {
                $this->sendBackupNotification($backupData, $schedule);
            }

            $this->monitoring->logInfo('Scheduled backup completed successfully', [
                'schedule_id' => $schedule['id'],
                'backup_id' => $backupData['backup_id'],
                'filename' => $backupData['filename']
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'schedule_id' => $schedule['id'],
                'schedule_name' => $schedule['name'],
                'action' => 'scheduled_backup_execution'
            ]);

            // Send failure notification
            $this->sendBackupFailureNotification($schedule, $e->getMessage());
        }
    }

    /**
     * Get active backup schedules
     */
    private function getActiveSchedules()
    {
        $sql = "SELECT * FROM backup_schedules WHERE enabled = 1 ORDER BY priority DESC, id ASC";
        $stmt = $this->backupLogModel->getConnection()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Check if backup should run based on schedule
     */
    private function shouldRunBackup($schedule)
    {
        $now = new \DateTime('now', new \DateTimeZone($schedule['timezone']));
        $nextRun = new \DateTime($schedule['next_run']);
        
        return $now >= $nextRun;
    }

    /**
     * Update schedule last run time
     */
    private function updateScheduleLastRun($scheduleId)
    {
        $sql = "UPDATE backup_schedules SET 
                    last_run = NOW(),
                    next_run = CASE 
                        WHEN frequency = 'hourly' THEN DATE_ADD(NOW(), INTERVAL 1 HOUR)
                        WHEN frequency = 'daily' THEN DATE_ADD(NOW(), INTERVAL 1 DAY)
                        WHEN frequency = 'weekly' THEN DATE_ADD(NOW(), INTERVAL 1 WEEK)
                        WHEN frequency = 'monthly' THEN DATE_ADD(NOW(), INTERVAL 1 MONTH)
                    END
                WHERE id = :schedule_id";

        $stmt = $this->backupLogModel->getConnection()->prepare($sql);
        $stmt->execute(['schedule_id' => $scheduleId]);
    }

    /**
     * Check if notification should be sent
     */
    private function shouldSendNotification($schedule)
    {
        $config = $this->getBackupConfig();
        return $config['notification_enabled'] ?? true;
    }

    /**
     * Send backup success notification
     */
    private function sendBackupNotification($backupData, $schedule)
    {
        try {
            $config = $this->getBackupConfig();
            $email = $config['notification_email'] ?? 'admin@gafconl.com';

            $subject = "Backup Completed Successfully - {$schedule['name']}";
            $message = $this->generateBackupNotificationMessage($backupData, $schedule);

            // Log notification
            $this->logNotification($backupData['backup_id'], 'email', $email, $subject, $message);

            // In a real application, you would send the email here
            $this->monitoring->logInfo('Backup notification sent', [
                'backup_id' => $backupData['backup_id'],
                'email' => $email
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_notification',
                'backup_id' => $backupData['backup_id'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Send backup failure notification
     */
    private function sendBackupFailureNotification($schedule, $errorMessage)
    {
        try {
            $config = $this->getBackupConfig();
            $email = $config['notification_email'] ?? 'admin@gafconl.com';

            $subject = "Backup Failed - {$schedule['name']}";
            $message = $this->generateBackupFailureMessage($schedule, $errorMessage);

            // Log notification
            $this->logNotification('failed_' . uniqid(), 'email', $email, $subject, $message);

            // In a real application, you would send the email here
            $this->monitoring->logInfo('Backup failure notification sent', [
                'schedule_id' => $schedule['id'],
                'email' => $email
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_failure_notification',
                'schedule_id' => $schedule['id'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Generate backup notification message
     */
    private function generateBackupNotificationMessage($backupData, $schedule)
    {
        $size = $this->formatBytes($backupData['size']);
        $duration = round($backupData['duration'] / 1000, 2);

        return "
Backup completed successfully!

Schedule: {$schedule['name']}
Type: {$backupData['type']}
File: {$backupData['filename']}
Size: {$size}
Duration: {$duration} seconds
Completed: " . date('Y-m-d H:i:s') . "

Backup ID: {$backupData['backup_id']}
        ";
    }

    /**
     * Generate backup failure message
     */
    private function generateBackupFailureMessage($schedule, $errorMessage)
    {
        return "
Backup failed!

Schedule: {$schedule['name']}
Type: {$schedule['type']}
Error: {$errorMessage}
Failed: " . date('Y-m-d H:i:s') . "

Please check the backup configuration and try again.
        ";
    }

    /**
     * Log notification
     */
    private function logNotification($backupId, $type, $recipient, $subject, $message)
    {
        $sql = "INSERT INTO backup_notifications (
                    backup_id, notification_type, recipient, subject, message, status, sent_at
                ) VALUES (
                    :backup_id, :notification_type, :recipient, :subject, :message, 'sent', NOW()
                )";

        $stmt = $this->backupLogModel->getConnection()->prepare($sql);
        $stmt->execute([
            'backup_id' => $backupId,
            'notification_type' => $type,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        ]);
    }

    /**
     * Get backup configuration
     */
    private function getBackupConfig()
    {
        $sql = "SELECT config_key, config_value, config_type FROM backup_config";
        $stmt = $this->backupLogModel->getConnection()->prepare($sql);
        $stmt->execute();
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $value = $row['config_value'];
            
            switch ($row['config_type']) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $config[$row['config_key']] = $value;
        }
        
        return $config;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Run manual backup
     */
    public function runManualBackup($type = 'full', $options = [])
    {
        try {
            $this->monitoring->logInfo('Starting manual backup', [
                'type' => $type,
                'options' => $options
            ]);

            $backupData = $this->backupManager->createBackup($type, $options);

            $this->monitoring->logInfo('Manual backup completed successfully', [
                'backup_id' => $backupData['backup_id'],
                'filename' => $backupData['filename']
            ]);

            return $backupData;

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'manual_backup',
                'type' => $type
            ]);

            throw $e;
        }
    }

    /**
     * Clean up old backups
     */
    public function cleanupOldBackups()
    {
        try {
            $this->monitoring->logInfo('Starting backup cleanup');

            $config = $this->getBackupConfig();
            $maxAge = $config['retention_days'] ?? 30;
            $maxSize = ($config['retention_size_mb'] ?? 1024) * 1024 * 1024; // Convert to bytes

            $backupDir = $config['backup_path'] ?? __DIR__ . '/../../backups';
            $files = glob($backupDir . '/*');
            
            $deletedCount = 0;
            $totalSize = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileAge = time() - filemtime($file);
                    $fileSize = filesize($file);
                    $totalSize += $fileSize;

                    // Delete old files
                    if ($fileAge > ($maxAge * 24 * 60 * 60)) {
                        unlink($file);
                        $deletedCount++;

                        $this->monitoring->logInfo('Deleted old backup file', [
                            'file' => basename($file),
                            'age_days' => round($fileAge / (24 * 60 * 60), 2)
                        ]);
                    }
                }
            }

            // Delete files if total size exceeds limit
            if ($totalSize > $maxSize) {
                $files = glob($backupDir . '/*');
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });

                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $deletedCount++;

                        $this->monitoring->logInfo('Deleted backup file due to size limit', [
                            'file' => basename($file)
                        ]);

                        $totalSize -= filesize($file);
                        if ($totalSize <= $maxSize) {
                            break;
                        }
                    }
                }
            }

            $this->monitoring->logInfo('Backup cleanup completed', [
                'deleted_count' => $deletedCount,
                'remaining_size' => $this->formatBytes($totalSize)
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_cleanup'
            ]);

            throw $e;
        }
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup($backupPath)
    {
        try {
            $this->monitoring->logInfo('Starting backup verification', [
                'backup_path' => $backupPath
            ]);

            $startTime = microtime(true);

            // Check if file exists
            if (!file_exists($backupPath)) {
                throw new \Exception('Backup file not found');
            }

            // Check file size
            $fileSize = filesize($backupPath);
            if ($fileSize === 0) {
                throw new \Exception('Backup file is empty');
            }

            // Check file permissions
            if (!is_readable($backupPath)) {
                throw new \Exception('Backup file is not readable');
            }

            $duration = (microtime(true) - $startTime) * 1000;

            $verificationData = [
                'verification_type' => 'integrity',
                'status' => 'passed',
                'details' => json_encode([
                    'file_size' => $fileSize,
                    'duration' => $duration
                ])
            ];

            $this->logVerification($backupPath, $verificationData);

            $this->monitoring->logInfo('Backup verification completed', [
                'backup_path' => $backupPath,
                'status' => 'passed',
                'duration' => round($duration, 2) . 'ms'
            ]);

            return true;

        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            $verificationData = [
                'verification_type' => 'integrity',
                'status' => 'failed',
                'details' => json_encode([
                    'error' => $e->getMessage(),
                    'duration' => $duration
                ])
            ];

            $this->logVerification($backupPath, $verificationData);

            $this->monitoring->logError($e, [
                'action' => 'backup_verification',
                'backup_path' => $backupPath
            ]);

            throw $e;
        }
    }

    /**
     * Log verification result
     */
    private function logVerification($backupPath, $verificationData)
    {
        $backupId = basename($backupPath, '.sql');
        
        $sql = "INSERT INTO backup_verification_logs (
                    backup_id, verification_type, status, details, duration
                ) VALUES (
                    :backup_id, :verification_type, :status, :details, :duration
                )";

        $stmt = $this->backupLogModel->getConnection()->prepare($sql);
        $stmt->execute([
            'backup_id' => $backupId,
            'verification_type' => $verificationData['verification_type'],
            'status' => $verificationData['status'],
            'details' => $verificationData['details'],
            'duration' => json_decode($verificationData['details'], true)['duration'] ?? 0
        ]);
    }
} 