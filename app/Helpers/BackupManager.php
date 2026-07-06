<?php

namespace App\Helpers;

use App\Models\BackupLogModel;
use App\Helpers\Monitoring;

class BackupManager
{
    private static $instance = null;
    private $config;
    private $monitoring;
    private $backupLogModel;
    private $dbConfig;

    private function __construct()
    {
        $this->config = $this->loadConfig();
        $this->monitoring = Monitoring::getInstance();
        $this->backupLogModel = new BackupLogModel();
        $this->dbConfig = $this->getDatabaseConfig();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create a full database backup
     */
    public function createBackup($type = 'full', $options = [])
    {
        $startTime = microtime(true);
        $backupId = uniqid('backup_');
        
        try {
            $this->monitoring->logInfo('Starting database backup', [
                'backup_id' => $backupId,
                'type' => $type,
                'options' => $options
            ]);

            // Create backup directory
            $backupDir = $this->createBackupDirectory();
            
            // Generate backup filename
            $filename = $this->generateBackupFilename($type);
            $backupPath = $backupDir . '/' . $filename;
            
            // Create backup based on type
            switch ($type) {
                case 'full':
                    $result = $this->createFullBackup($backupPath, $options);
                    break;
                case 'incremental':
                    $result = $this->createIncrementalBackup($backupPath, $options);
                    break;
                case 'differential':
                    $result = $this->createDifferentialBackup($backupPath, $options);
                    break;
                default:
                    throw new \Exception("Unknown backup type: {$type}");
            }

            if (!$result) {
                throw new \Exception('Backup creation failed');
            }

            // Compress backup if enabled
            if ($this->config['compression']['enabled']) {
                $backupPath = $this->compressBackup($backupPath);
            }

            // Encrypt backup if enabled
            if ($this->config['encryption']['enabled']) {
                $backupPath = $this->encryptBackup($backupPath);
            }

            // Calculate backup size
            $backupSize = filesize($backupPath);
            
            // Log backup success
            $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            
            $backupData = [
                'backup_id' => $backupId,
                'filename' => basename($backupPath),
                'filepath' => $backupPath,
                'type' => $type,
                'size' => $backupSize,
                'duration' => $duration,
                'status' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->backupLogModel->logBackup($backupData);

            $this->monitoring->logInfo('Database backup completed successfully', [
                'backup_id' => $backupId,
                'filename' => basename($backupPath),
                'size' => $this->formatBytes($backupSize),
                'duration' => round($duration, 2) . 'ms'
            ]);

            // Clean up old backups
            $this->cleanupOldBackups();

            return $backupData;

        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            
            $this->monitoring->logError($e, [
                'backup_id' => $backupId,
                'type' => $type,
                'duration' => round($duration, 2) . 'ms'
            ]);

            // Log backup failure
            $backupData = [
                'backup_id' => $backupId,
                'filename' => $filename ?? 'unknown',
                'filepath' => $backupPath ?? 'unknown',
                'type' => $type,
                'size' => 0,
                'duration' => $duration,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->backupLogModel->logBackup($backupData);

            throw $e;
        }
    }

    /**
     * Create full database backup
     */
    private function createFullBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        switch ($dbConfig['driver']) {
            case 'mysql':
                return $this->createMySQLBackup($backupPath, $options);
            case 'sqlite':
                return $this->createSQLiteBackup($backupPath, $options);
            case 'pgsql':
                return $this->createPostgreSQLBackup($backupPath, $options);
            default:
                throw new \Exception("Unsupported database driver: {$dbConfig['driver']}");
        }
    }

    /**
     * Create MySQL backup
     */
    private function createMySQLBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($backupPath)
        );

        // Add additional options
        if (!empty($options['single_transaction'])) {
            $command .= ' --single-transaction';
        }
        if (!empty($options['routines'])) {
            $command .= ' --routines';
        }
        if (!empty($options['triggers'])) {
            $command .= ' --triggers';
        }
        if (!empty($options['events'])) {
            $command .= ' --events';
        }

        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('MySQL backup failed: ' . implode("\n", $output));
        }

        return file_exists($backupPath) && filesize($backupPath) > 0;
    }

    /**
     * Create SQLite backup
     */
    private function createSQLiteBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        // For SQLite, we can simply copy the database file
        if (file_exists($dbConfig['database'])) {
            return copy($dbConfig['database'], $backupPath);
        }
        
        // If using in-memory database, create a dump
        $pdo = new \PDO('sqlite::memory:');
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
        
        $backupContent = '';
        foreach ($tables as $table) {
            $tableName = $table['name'];
            $createTable = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$tableName}'")->fetch();
            $backupContent .= $createTable['sql'] . ";\n";
            
            $data = $pdo->query("SELECT * FROM {$tableName}")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $row) {
                $values = array_map(function($value) {
                    return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                }, $row);
                $backupContent .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $values) . ");\n";
            }
        }
        
        return file_put_contents($backupPath, $backupContent) !== false;
    }

    /**
     * Create PostgreSQL backup
     */
    private function createPostgreSQLBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        $command = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s --file=%s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($backupPath)
        );

        // Set password environment variable
        putenv("PGPASSWORD=" . $dbConfig['password']);

        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        // Clear password from environment
        putenv("PGPASSWORD");
        
        if ($returnCode !== 0) {
            throw new \Exception('PostgreSQL backup failed: ' . implode("\n", $output));
        }

        return file_exists($backupPath) && filesize($backupPath) > 0;
    }

    /**
     * Create incremental backup
     */
    private function createIncrementalBackup($backupPath, $options = [])
    {
        // Get last backup timestamp
        $lastBackup = $this->backupLogModel->getLastSuccessfulBackup();
        
        if (!$lastBackup) {
            // If no previous backup, create a full backup
            return $this->createFullBackup($backupPath, $options);
        }

        // For incremental backup, we'll create a backup of changes since last backup
        // This is a simplified implementation - in production, you might use binary logs
        return $this->createFullBackup($backupPath, $options);
    }

    /**
     * Create differential backup
     */
    private function createDifferentialBackup($backupPath, $options = [])
    {
        // Get last full backup
        $lastFullBackup = $this->backupLogModel->getLastFullBackup();
        
        if (!$lastFullBackup) {
            // If no previous full backup, create a full backup
            return $this->createFullBackup($backupPath, $options);
        }

        // For differential backup, we'll create a backup of changes since last full backup
        // This is a simplified implementation
        return $this->createFullBackup($backupPath, $options);
    }

    /**
     * Compress backup file
     */
    private function compressBackup($backupPath)
    {
        $compressedPath = $backupPath . '.gz';
        
        $input = gzopen($compressedPath, 'w9');
        $output = fopen($backupPath, 'rb');
        
        while (!feof($output)) {
            gzwrite($input, fread($output, 8192));
        }
        
        fclose($output);
        gzclose($input);
        
        // Remove original file
        unlink($backupPath);
        
        return $compressedPath;
    }

    /**
     * Encrypt backup file
     */
    private function encryptBackup($backupPath)
    {
        $encryptionKey = $this->config['encryption']['key'];
        
        if (empty($encryptionKey)) {
            throw new \Exception('Encryption key not configured');
        }

        $encryptedPath = $backupPath . '.enc';
        $data = file_get_contents($backupPath);
        
        $cipher = 'aes-256-cbc';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        
        $encrypted = openssl_encrypt($data, $cipher, $encryptionKey, 0, $iv);
        
        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        $encryptedData = $iv . $encrypted;
        file_put_contents($encryptedPath, $encryptedData);
        
        // Remove original file
        unlink($backupPath);
        
        return $encryptedPath;
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($backupPath, $options = [])
    {
        $startTime = microtime(true);
        
        try {
            $this->monitoring->logInfo('Starting database restore', [
                'backup_path' => $backupPath,
                'options' => $options
            ]);

            // Decrypt if needed
            if (pathinfo($backupPath, PATHINFO_EXTENSION) === 'enc') {
                $backupPath = $this->decryptBackup($backupPath);
            }

            // Decompress if needed
            if (pathinfo($backupPath, PATHINFO_EXTENSION) === 'gz') {
                $backupPath = $this->decompressBackup($backupPath);
            }

            $dbConfig = $this->dbConfig;
            
            switch ($dbConfig['driver']) {
                case 'mysql':
                    $result = $this->restoreMySQLBackup($backupPath, $options);
                    break;
                case 'sqlite':
                    $result = $this->restoreSQLiteBackup($backupPath, $options);
                    break;
                case 'pgsql':
                    $result = $this->restorePostgreSQLBackup($backupPath, $options);
                    break;
                default:
                    throw new \Exception("Unsupported database driver: {$dbConfig['driver']}");
            }

            $duration = (microtime(true) - $startTime) * 1000;
            
            $this->monitoring->logInfo('Database restore completed successfully', [
                'backup_path' => $backupPath,
                'duration' => round($duration, 2) . 'ms'
            ]);

            return $result;

        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            
            $this->monitoring->logError($e, [
                'backup_path' => $backupPath,
                'duration' => round($duration, 2) . 'ms'
            ]);

            throw $e;
        }
    }

    /**
     * Restore MySQL backup
     */
    private function restoreMySQLBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($backupPath)
        );

        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('MySQL restore failed: ' . implode("\n", $output));
        }

        return true;
    }

    /**
     * Restore SQLite backup
     */
    private function restoreSQLiteBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        // For SQLite, we can simply copy the backup file
        if (file_exists($dbConfig['database'])) {
            unlink($dbConfig['database']);
        }
        
        return copy($backupPath, $dbConfig['database']);
    }

    /**
     * Restore PostgreSQL backup
     */
    private function restorePostgreSQLBackup($backupPath, $options = [])
    {
        $dbConfig = $this->dbConfig;
        
        $command = sprintf(
            'psql --host=%s --port=%s --username=%s --dbname=%s < %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($backupPath)
        );

        // Set password environment variable
        putenv("PGPASSWORD=" . $dbConfig['password']);

        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        // Clear password from environment
        putenv("PGPASSWORD");
        
        if ($returnCode !== 0) {
            throw new \Exception('PostgreSQL restore failed: ' . implode("\n", $output));
        }

        return true;
    }

    /**
     * Decrypt backup file
     */
    private function decryptBackup($backupPath)
    {
        $encryptionKey = $this->config['encryption']['key'];
        
        if (empty($encryptionKey)) {
            throw new \Exception('Encryption key not configured');
        }

        $decryptedPath = str_replace('.enc', '', $backupPath);
        $encryptedData = file_get_contents($backupPath);
        
        $cipher = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($cipher);
        
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $cipher, $encryptionKey, 0, $iv);
        
        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        file_put_contents($decryptedPath, $decrypted);
        
        return $decryptedPath;
    }

    /**
     * Decompress backup file
     */
    private function decompressBackup($backupPath)
    {
        $decompressedPath = str_replace('.gz', '', $backupPath);
        
        $input = gzopen($backupPath, 'rb');
        $output = fopen($decompressedPath, 'wb');
        
        while (!gzeof($input)) {
            fwrite($output, gzread($input, 8192));
        }
        
        fclose($output);
        gzclose($input);
        
        return $decompressedPath;
    }

    /**
     * Clean up old backups
     */
    private function cleanupOldBackups()
    {
        $backupDir = $this->config['storage']['path'];
        $maxAge = $this->config['retention']['max_age_days'] * 24 * 60 * 60; // Convert to seconds
        $maxSize = $this->config['retention']['max_size_mb'] * 1024 * 1024; // Convert to bytes
        
        $files = glob($backupDir . '/*');
        $totalSize = 0;
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = time() - filemtime($file);
                $fileSize = filesize($file);
                $totalSize += $fileSize;
                
                // Delete old files
                if ($fileAge > $maxAge) {
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
        
        if ($deletedCount > 0) {
            $this->monitoring->logInfo('Backup cleanup completed', [
                'deleted_count' => $deletedCount,
                'remaining_size' => $this->formatBytes($totalSize)
            ]);
        }
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats($days = 30)
    {
        $backupDir = $this->config['storage']['path'];
        $files = glob($backupDir . '/*');
        
        $stats = [
            'total_backups' => count($files),
            'total_size' => 0,
            'oldest_backup' => null,
            'newest_backup' => null,
            'backups_by_type' => [],
            'recent_backups' => []
        ];
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileSize = filesize($file);
                $fileTime = filemtime($file);
                $fileName = basename($file);
                
                $stats['total_size'] += $fileSize;
                
                if (!$stats['oldest_backup'] || $fileTime < $stats['oldest_backup']['time']) {
                    $stats['oldest_backup'] = [
                        'file' => $fileName,
                        'time' => $fileTime,
                        'date' => date('Y-m-d H:i:s', $fileTime)
                    ];
                }
                
                if (!$stats['newest_backup'] || $fileTime > $stats['newest_backup']['time']) {
                    $stats['newest_backup'] = [
                        'file' => $fileName,
                        'time' => $fileTime,
                        'date' => date('Y-m-d H:i:s', $fileTime)
                    ];
                }
                
                // Categorize by type
                $type = $this->getBackupType($fileName);
                if (!isset($stats['backups_by_type'][$type])) {
                    $stats['backups_by_type'][$type] = 0;
                }
                $stats['backups_by_type'][$type]++;
                
                // Recent backups
                if ($fileTime > (time() - ($days * 24 * 60 * 60))) {
                    $stats['recent_backups'][] = [
                        'file' => $fileName,
                        'size' => $this->formatBytes($fileSize),
                        'date' => date('Y-m-d H:i:s', $fileTime)
                    ];
                }
            }
        }
        
        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
        
        return $stats;
    }

    /**
     * Get backup type from filename
     */
    private function getBackupType($filename)
    {
        if (strpos($filename, 'full') !== false) {
            return 'full';
        } elseif (strpos($filename, 'incremental') !== false) {
            return 'incremental';
        } elseif (strpos($filename, 'differential') !== false) {
            return 'differential';
        }
        return 'unknown';
    }

    /**
     * Create backup directory
     */
    private function createBackupDirectory()
    {
        $backupDir = $this->config['storage']['path'];
        
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                throw new \Exception("Failed to create backup directory: {$backupDir}");
            }
        }
        
        return $backupDir;
    }

    /**
     * Generate backup filename
     */
    private function generateBackupFilename($type)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $dbName = $this->dbConfig['database'];
        
        return "backup_{$type}_{$dbName}_{$timestamp}.sql";
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
     * Load backup configuration
     */
    private function loadConfig()
    {
        return [
            'enabled' => $_ENV['BACKUP_ENABLED'] ?? true,
            'schedule' => [
                'frequency' => $_ENV['BACKUP_FREQUENCY'] ?? 'daily',
                'time' => $_ENV['BACKUP_TIME'] ?? '02:00',
                'timezone' => $_ENV['BACKUP_TIMEZONE'] ?? 'UTC'
            ],
            'storage' => [
                'path' => $_ENV['BACKUP_PATH'] ?? __DIR__ . '/../../backups',
                'max_size_mb' => $_ENV['BACKUP_MAX_SIZE_MB'] ?? 1024
            ],
            'compression' => [
                'enabled' => $_ENV['BACKUP_COMPRESSION_ENABLED'] ?? true,
                'level' => $_ENV['BACKUP_COMPRESSION_LEVEL'] ?? 9
            ],
            'encryption' => [
                'enabled' => $_ENV['BACKUP_ENCRYPTION_ENABLED'] ?? false,
                'key' => $_ENV['BACKUP_ENCRYPTION_KEY'] ?? null
            ],
            'retention' => [
                'max_age_days' => $_ENV['BACKUP_RETENTION_DAYS'] ?? 30,
                'max_size_mb' => $_ENV['BACKUP_RETENTION_SIZE_MB'] ?? 1024
            ],
            'notification' => [
                'enabled' => $_ENV['BACKUP_NOTIFICATION_ENABLED'] ?? true,
                'email' => $_ENV['BACKUP_NOTIFICATION_EMAIL'] ?? 'admin@gafconl.com'
            ]
        ];
    }

    /**
     * Get database configuration
     */
    private function getDatabaseConfig()
    {
        return [
            'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'gafconl',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? ''
        ];
    }
} 