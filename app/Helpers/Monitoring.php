<?php

namespace App\Helpers;

use App\Models\AuditLogModel;
use App\Models\ErrorLogModel;

class Monitoring
{
    private static $instance = null;
    private $startTime;
    private $memoryUsage;
    private $errorLogModel;
    private $auditLogModel;

    private function __construct()
    {
        $this->startTime = microtime(true);
        $this->memoryUsage = memory_get_usage();
        $this->errorLogModel = new ErrorLogModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log application errors with detailed context
     */
    public function logError($error, $context = [])
    {
        $errorData = [
            'error_type' => get_class($error),
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'context' => json_encode($context),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Log to database
        $this->errorLogModel->logError($errorData);

        // Log to file for backup
        $this->logToFile('error', $errorData);

        // Send to external monitoring service if configured
        $this->sendToExternalService('error', $errorData);
    }

    /**
     * Log application warnings
     */
    public function logWarning($message, $context = [])
    {
        $warningData = [
            'level' => 'warning',
            'message' => $message,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'context' => json_encode($context),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('warning', $warningData);
        $this->sendToExternalService('warning', $warningData);
    }

    /**
     * Log application info messages
     */
    public function logInfo($message, $context = [])
    {
        $infoData = [
            'level' => 'info',
            'message' => $message,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'context' => json_encode($context),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('info', $infoData);
    }

    /**
     * Log user actions for audit trail
     */
    public function logUserAction($action, $details = [], $userId = null)
    {
        $userId = $userId ?? $_SESSION['user_id'] ?? null;
        
        $auditData = [
            'user_id' => $userId,
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->auditLogModel->logAction($auditData);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance($operation, $duration, $memoryUsage = null)
    {
        $performanceData = [
            'operation' => $operation,
            'duration' => $duration,
            'memory_usage' => $memoryUsage ?? memory_get_usage(),
            'peak_memory' => memory_get_peak_usage(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('performance', $performanceData);
    }

    /**
     * Get application performance metrics
     */
    public function getPerformanceMetrics()
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        $memoryUsage = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        return [
            'execution_time' => round($executionTime * 1000, 2), // in milliseconds
            'memory_usage' => $this->formatBytes($memoryUsage),
            'peak_memory' => $this->formatBytes($peakMemory),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'php_version' => PHP_VERSION,
            'server_load' => $this->getServerLoad(),
            'database_connections' => $this->getDatabaseConnections()
        ];
    }

    /**
     * Monitor database performance
     */
    public function logDatabaseQuery($query, $duration, $rows = null)
    {
        $queryData = [
            'query' => $query,
            'duration' => $duration,
            'rows_affected' => $rows,
            'user_id' => $_SESSION['user_id'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('database', $queryData);
    }

    /**
     * Monitor API usage and rate limiting
     */
    public function logApiUsage($endpoint, $method, $statusCode, $duration)
    {
        $apiData = [
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'duration' => $duration,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('api', $apiData);
    }

    /**
     * Monitor payment transactions
     */
    public function logPaymentTransaction($gateway, $amount, $status, $reference, $duration = null)
    {
        $paymentData = [
            'gateway' => $gateway,
            'amount' => $amount,
            'status' => $status,
            'reference' => $reference,
            'duration' => $duration,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logToFile('payment', $paymentData);
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->checkMemoryUsage(),
            'error_rate' => $this->getErrorRate(),
            'response_time' => $this->getAverageResponseTime(),
            'uptime' => $this->getUptime()
        ];
    }

    /**
     * Check if system is healthy
     */
    public function isSystemHealthy()
    {
        $health = $this->getSystemHealth();
        
        return $health['database']['status'] === 'healthy' &&
               $health['disk_space']['status'] === 'healthy' &&
               $health['memory_usage']['status'] === 'healthy' &&
               $health['error_rate'] < 5; // Less than 5% error rate
    }

    /**
     * Send alerts for critical issues
     */
    public function sendAlert($type, $message, $severity = 'medium')
    {
        $alertData = [
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'timestamp' => date('Y-m-d H:i:s'),
            'system_health' => $this->getSystemHealth()
        ];

        // Log alert
        $this->logToFile('alert', $alertData);

        // Send to external monitoring service
        $this->sendToExternalService('alert', $alertData);

        // Send email alert for critical issues
        if ($severity === 'critical') {
            $this->sendEmailAlert($alertData);
        }
    }

    /**
     * Generate monitoring report
     */
    public function generateReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $endDate ?? date('Y-m-d');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'errors' => $this->errorLogModel->getErrorStats($startDate, $endDate),
            'performance' => $this->getPerformanceStats($startDate, $endDate),
            'api_usage' => $this->getApiUsageStats($startDate, $endDate),
            'user_actions' => $this->auditLogModel->getActionStats($startDate, $endDate),
            'system_health' => $this->getSystemHealth()
        ];
    }

    // Private helper methods

    private function logToFile($type, $data)
    {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }

        $filename = $logDir . '/' . $type . '_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL;
        
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Public proxy so MonitoringMiddleware can call logToFile without
     * duplicating the path/format logic.
     */
    public function writeToFile($type, $data)
    {
        $this->logToFile($type, $data);
    }

    private function sendToExternalService($type, $data)
    {
        // Check if external monitoring is configured
        $monitoringUrl = $_ENV['MONITORING_URL'] ?? null;
        $monitoringKey = $_ENV['MONITORING_API_KEY'] ?? null;

        if ($monitoringUrl && $monitoringKey) {
            $payload = [
                'type' => $type,
                'data' => $data,
                'timestamp' => time(),
                'api_key' => $monitoringKey
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($payload)
                ]
            ]);

            @file_get_contents($monitoringUrl, false, $context);
        }
    }

    private function sendEmailAlert($alertData)
    {
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? null;
        if (!$adminEmail) {
            return;
        }

        $subject = 'GAFCONL System Alert: ' . $alertData['type'];
        $message = "Critical system alert:\n\n";
        $message .= "Type: " . $alertData['type'] . "\n";
        $message .= "Message: " . $alertData['message'] . "\n";
        $message .= "Severity: " . $alertData['severity'] . "\n";
        $message .= "Timestamp: " . $alertData['timestamp'] . "\n";

        mail($adminEmail, $subject, $message);
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    private function getServerLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2]
            ];
        }
        return null;
    }

    private function getDatabaseConnections()
    {
        // This would need to be implemented based on your database setup
        return 0;
    }

    private function checkDatabaseHealth()
    {
        try {
            $model = new \App\Models\BaseModel();
            $db = $model->getConnection();
            $db->query('SELECT 1');
            
            return [
                'status' => 'healthy',
                'response_time' => 0
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkDiskSpace()
    {
        $freeSpace = disk_free_space(__DIR__);
        $totalSpace = disk_total_space(__DIR__);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;

        return [
            'status' => $usagePercent > 90 ? 'critical' : ($usagePercent > 80 ? 'warning' : 'healthy'),
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'usage_percent' => round($usagePercent, 2)
        ];
    }

    private function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        $usagePercent = ($memoryUsage / $memoryLimitBytes) * 100;

        return [
            'status' => $usagePercent > 90 ? 'critical' : ($usagePercent > 80 ? 'warning' : 'healthy'),
            'current_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => $memoryLimit,
            'usage_percent' => round($usagePercent, 2)
        ];
    }

    private function parseMemoryLimit($limit)
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'k': return $value * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'g': return $value * 1024 * 1024 * 1024;
            default: return $value;
        }
    }

    private function getErrorRate()
    {
        // Calculate error rate from recent logs
        $recentErrors = $this->errorLogModel->getRecentErrorCount();
        $totalRequests = $this->getTotalRequests();
        
        return $totalRequests > 0 ? ($recentErrors / $totalRequests) * 100 : 0;
    }

    private function getAverageResponseTime()
    {
        // This would need to be implemented based on your performance logging
        return 0;
    }

    private function getUptime()
    {
        // This would need to be implemented based on your server setup
        return 'Unknown';
    }

    private function getTotalRequests()
    {
        // This would need to be implemented based on your request logging
        return 1000; // Placeholder
    }

    private function getPerformanceStats($startDate, $endDate)
    {
        // This would need to be implemented based on your performance logging
        return [];
    }

    private function getApiUsageStats($startDate, $endDate)
    {
        // This would need to be implemented based on your API logging
        return [];
    }
} 