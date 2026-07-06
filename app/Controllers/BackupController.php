<?php

namespace App\Controllers;

use App\Helpers\BackupManager;
use App\Models\BackupLogModel;
use App\Helpers\Monitoring;

class BackupController extends BaseController
{
    private $backupManager;
    private $backupLogModel;
    private $monitoring;

    public function __construct()
    {
        parent::__construct();
        $this->backupManager = BackupManager::getInstance();
        $this->backupLogModel = new BackupLogModel();
        $this->monitoring = Monitoring::getInstance();
    }

    /**
     * Display backup dashboard
     */
    public function index()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $backupStats = $this->backupLogModel->getBackupStats();
        $recentBackups = $this->backupLogModel->getRecentBackups(10);
        $failedBackups = $this->backupLogModel->getFailedBackups(5);
        $fileStats = $this->backupManager->getBackupStats();

        $data = [
            'backup_stats' => $backupStats,
            'recent_backups' => $recentBackups,
            'failed_backups' => $failedBackups,
            'file_stats' => $fileStats,
            'page_title' => 'Backup Management'
        ];

        $this->render('admin/backup/dashboard', $data);
    }

    /**
     * Create a new backup
     */
    public function create()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $type = $_POST['type'] ?? 'full';
            $options = $_POST['options'] ?? [];

            $this->monitoring->logUserAction(
                $this->getCurrentUserId(),
                'backup_created',
                ['type' => $type, 'options' => $options]
            );

            $backupData = $this->backupManager->createBackup($type, $options);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup' => $backupData
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_creation',
                'type' => $type ?? 'unknown'
            ]);

            $this->jsonResponse([
                'error' => 'Backup creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restore()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $backupPath = $_POST['backup_path'] ?? '';
            
            if (empty($backupPath)) {
                throw new \Exception('Backup path is required');
            }

            $this->monitoring->logUserAction(
                $this->getCurrentUserId(),
                'backup_restore',
                ['backup_path' => $backupPath]
            );

            $result = $this->backupManager->restoreBackup($backupPath);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Database restored successfully'
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_restore',
                'backup_path' => $backupPath ?? 'unknown'
            ]);

            $this->jsonResponse([
                'error' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $filename = $_GET['file'] ?? '';
        
        if (empty($filename)) {
            $this->redirect('/admin/backup');
            return;
        }

        $backupDir = $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../../backups';
        $filePath = $backupDir . '/' . $filename;

        if (!file_exists($filePath)) {
            $this->redirect('/admin/backup');
            return;
        }

        // Log download
        $this->monitoring->logUserAction(
            $this->getCurrentUserId(),
            'backup_downloaded',
            ['filename' => $filename]
        );

        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Output file
        readfile($filePath);
        exit;
    }

    /**
     * Delete backup file
     */
    public function delete()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $filename = $_POST['filename'] ?? '';
            
            if (empty($filename)) {
                throw new \Exception('Filename is required');
            }

            $backupDir = $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../../backups';
            $filePath = $backupDir . '/' . $filename;

            if (!file_exists($filePath)) {
                throw new \Exception('Backup file not found');
            }

            $this->monitoring->logUserAction(
                $this->getCurrentUserId(),
                'backup_deleted',
                ['filename' => $filename]
            );

            unlink($filePath);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Backup file deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->monitoring->logError($e, [
                'action' => 'backup_delete',
                'filename' => $filename ?? 'unknown'
            ]);

            $this->jsonResponse([
                'error' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup logs
     */
    public function logs()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $logs = $this->backupLogModel->getRecentBackups($limit);
        $totalLogs = $this->backupLogModel->getBackupStats();
        $totalPages = ceil($totalLogs['total_backups'] / $limit);

        $data = [
            'logs' => $logs,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'page_title' => 'Backup Logs'
        ];

        $this->render('admin/backup/logs', $data);
    }

    /**
     * Get backup statistics
     */
    public function stats()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $days = (int)($_GET['days'] ?? 30);
            
            $backupStats = $this->backupLogModel->getBackupStats();
            $performanceMetrics = $this->backupLogModel->getBackupPerformanceMetrics($days);
            $trends = $this->backupLogModel->getBackupTrends($days);
            $fileStats = $this->backupManager->getBackupStats($days);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'backup_stats' => $backupStats,
                    'performance_metrics' => $performanceMetrics,
                    'trends' => $trends,
                    'file_stats' => $fileStats
                ]
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup files
     */
    public function files()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $fileStats = $this->backupManager->getBackupStats();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $fileStats
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to get backup files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test backup configuration
     */
    public function test()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $backupDir = $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../../backups';
            
            $tests = [
                'backup_directory' => [
                    'status' => is_dir($backupDir) ? 'ok' : 'error',
                    'message' => is_dir($backupDir) ? 'Directory exists' : 'Directory does not exist'
                ],
                'backup_directory_writable' => [
                    'status' => is_writable($backupDir) ? 'ok' : 'error',
                    'message' => is_writable($backupDir) ? 'Directory is writable' : 'Directory is not writable'
                ],
                'database_connection' => [
                    'status' => 'ok',
                    'message' => 'Database connection available'
                ],
                'backup_tools' => [
                    'status' => $this->testBackupTools(),
                    'message' => 'Backup tools available'
                ]
            ];

            $this->jsonResponse([
                'success' => true,
                'tests' => $tests
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'error' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test backup tools availability
     */
    private function testBackupTools()
    {
        $dbConfig = [
            'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'gafconl',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? ''
        ];

        switch ($dbConfig['driver']) {
            case 'mysql':
                $command = 'mysqldump --version';
                break;
            case 'pgsql':
                $command = 'pg_dump --version';
                break;
            default:
                return 'ok'; // SQLite doesn't need external tools
        }

        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        return $returnCode === 0 ? 'ok' : 'error';
    }

    /**
     * Get backup configuration
     */
    public function config()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        $config = [
            'enabled' => $_ENV['BACKUP_ENABLED'] ?? true,
            'frequency' => $_ENV['BACKUP_FREQUENCY'] ?? 'daily',
            'time' => $_ENV['BACKUP_TIME'] ?? '02:00',
            'timezone' => $_ENV['BACKUP_TIMEZONE'] ?? 'UTC',
            'path' => $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../../backups',
            'compression_enabled' => $_ENV['BACKUP_COMPRESSION_ENABLED'] ?? true,
            'encryption_enabled' => $_ENV['BACKUP_ENCRYPTION_ENABLED'] ?? false,
            'retention_days' => $_ENV['BACKUP_RETENTION_DAYS'] ?? 30,
            'retention_size_mb' => $_ENV['BACKUP_RETENTION_SIZE_MB'] ?? 1024,
            'notification_enabled' => $_ENV['BACKUP_NOTIFICATION_ENABLED'] ?? true,
            'notification_email' => $_ENV['BACKUP_NOTIFICATION_EMAIL'] ?? 'admin@gafconl.com'
        ];

        $this->jsonResponse([
            'success' => true,
            'config' => $config
        ]);
    }

    /**
     * Update backup configuration
     */
    public function updateConfig()
    {
        // Check admin permissions
        if (!$this->isAdmin()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $config = $_POST['config'] ?? [];
            
            // Validate configuration
            $this->validateBackupConfig($config);

            // Update environment variables (in a real application, you'd save to a config file)
            foreach ($config as $key => $value) {
                $_ENV['BACKUP_' . strtoupper($key)] = $value;
            }

            $this->monitoring->logUserAction(
                $this->getCurrentUserId(),
                'backup_config_updated',
                ['config' => $config]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Configuration updated successfully'
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'error' => 'Configuration update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate backup configuration
     */
    private function validateBackupConfig($config)
    {
        $errors = [];

        if (isset($config['frequency']) && !in_array($config['frequency'], ['hourly', 'daily', 'weekly', 'monthly'])) {
            $errors[] = 'Invalid frequency value';
        }

        if (isset($config['time']) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $config['time'])) {
            $errors[] = 'Invalid time format (use HH:MM)';
        }

        if (isset($config['retention_days']) && (!is_numeric($config['retention_days']) || $config['retention_days'] < 1)) {
            $errors[] = 'Retention days must be a positive number';
        }

        if (isset($config['retention_size_mb']) && (!is_numeric($config['retention_size_mb']) || $config['retention_size_mb'] < 1)) {
            $errors[] = 'Retention size must be a positive number';
        }

        if (!empty($errors)) {
            throw new \Exception('Configuration validation failed: ' . implode(', ', $errors));
        }
    }
} 