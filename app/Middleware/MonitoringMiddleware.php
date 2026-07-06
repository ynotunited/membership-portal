<?php

namespace App\Middleware;

use App\Helpers\Monitoring;

class MonitoringMiddleware
{
    private $monitoring;
    private $startTime;

    public function __construct()
    {
        $this->monitoring = Monitoring::getInstance();
        $this->startTime = microtime(true);
    }

    /**
     * Handle the request and add monitoring
     */
    public function handle()
    {
        // Log request start
        $this->monitoring->logInfo('Request started', [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        // Set up error handling
        $this->setupErrorHandling();

        // Set up shutdown function to log performance
        register_shutdown_function([$this, 'logPerformance']);

        // Log user action if authenticated
        if (isset($_SESSION['user_id'])) {
            $this->monitoring->logUserAction('page_access', [
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? ''
            ]);
        }
    }

    /**
     * Setup error handling
     */
    private function setupErrorHandling()
    {
        // Set error handler
        set_error_handler([$this, 'handleError']);
        
        // Set exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Set fatal error handler
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        $this->monitoring->logError($error, [
            'error_type' => 'PHP Error',
            'error_number' => $errno
        ]);

        // Don't call the default error handler
        return true;
    }

    /**
     * Handle exceptions
     */
    public function handleException($exception)
    {
        $this->monitoring->logError($exception, [
            'error_type' => 'Uncaught Exception'
        ]);

        // Log as critical alert if it's a serious error
        if ($exception instanceof \PDOException) {
            $this->monitoring->sendAlert('database_error', $exception->getMessage(), 'critical');
        } elseif ($exception instanceof \Error) {
            $this->monitoring->sendAlert('fatal_error', $exception->getMessage(), 'critical');
        }

        // Show error page in production
        if (!isset($_ENV['APP_DEBUG']) || $_ENV['APP_DEBUG'] !== 'true') {
            http_response_code(500);
            include __DIR__ . '/../../app/Views/errors/500.php';
            exit;
        }
    }

    /**
     * Handle fatal errors
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $this->monitoring->logError($exception, [
                'error_type' => 'Fatal Error'
            ]);
            
            $this->monitoring->sendAlert('fatal_error', $error['message'], 'critical');
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance()
    {
        $endTime = microtime(true);
        $duration = ($endTime - $this->startTime) * 1000; // Convert to milliseconds
        
        $this->monitoring->logPerformance('request', $duration);
        
        // Log API usage if it's an API endpoint
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            $this->monitoring->logApiUsage(
                $_SERVER['REQUEST_URI'] ?? '',
                $_SERVER['REQUEST_METHOD'] ?? 'GET',
                http_response_code(),
                $duration
            );
        }
    }

    /**
     * Log database query performance
     */
    public function logDatabaseQuery($query, $duration, $rows = null)
    {
        $this->monitoring->logDatabaseQuery($query, $duration, $rows);
    }

    /**
     * Log payment transaction
     */
    public function logPaymentTransaction($gateway, $amount, $status, $reference, $duration = null)
    {
        $this->monitoring->logPaymentTransaction($gateway, $amount, $status, $reference, $duration);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($eventType, $description, $severity = 'medium')
    {
        $securityData = [
            'event_type'  => $eventType,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_id'     => $_SESSION['user_id'] ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'severity'    => $severity,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        // Write to the security log file via the public proxy
        $this->monitoring->writeToFile('security', $securityData);

        // Also record via SecurityLogger so it hits the DB audit table
        \App\Helpers\SecurityLogger::event(
            strtoupper($severity === 'high' || $severity === 'critical' ? 'ALERT' : 'WARNING'),
            'security.' . $eventType,
            ['description' => $description, 'severity' => $severity]
        );

        // Send alert for high/critical security events
        if (in_array($severity, ['high', 'critical'])) {
            $this->monitoring->sendAlert('security_event', $description, $severity);
        }
    }

    /**
     * Log user session
     */
    public function logUserSession($userId, $sessionId, $action = 'login')
    {
        $sessionData = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'action' => $action,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($action === 'login') {
            $sessionData['login_at'] = date('Y-m-d H:i:s');
        } elseif ($action === 'logout') {
            $sessionData['logout_at'] = date('Y-m-d H:i:s');
        }

        $this->logToDatabase('user_session_logs', $sessionData);
    }

    /**
     * Check rate limiting
     */
    public function checkRateLimit($endpoint, $limit = 100, $window = 3600)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;

        $sql = "SELECT request_count, first_request_at 
                FROM rate_limiting_logs 
                WHERE endpoint = :endpoint 
                AND ip_address = :ip_address 
                AND last_request_at >= DATE_SUB(NOW(), INTERVAL :window SECOND)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'endpoint' => $endpoint,
            'ip_address' => $ipAddress,
            'window' => $window
        ]);

        $result = $stmt->fetch();

        if ($result) {
            if ($result['request_count'] >= $limit) {
                // Rate limit exceeded
                $this->logSecurityEvent('rate_limit_exceeded', "Rate limit exceeded for endpoint: $endpoint", 'medium');
                return false;
            }

            // Update request count
            $sql = "UPDATE rate_limiting_logs 
                    SET request_count = request_count + 1, last_request_at = NOW()
                    WHERE endpoint = :endpoint AND ip_address = :ip_address";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                'endpoint' => $endpoint,
                'ip_address' => $ipAddress
            ]);
        } else {
            // Create new rate limit record
            $sql = "INSERT INTO rate_limiting_logs (endpoint, ip_address, user_id, request_count) 
                    VALUES (:endpoint, :ip_address, :user_id, 1)";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                'endpoint' => $endpoint,
                'ip_address' => $ipAddress,
                'user_id' => $userId
            ]);
        }

        return true;
    }

    /**
     * Log to database
     */
    private function logToDatabase($table, $data)
    {
        try {
            $columns      = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql  = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($data);
        } catch (\Exception $e) {
            // If database logging fails, fall back to file log via the public proxy
            $this->monitoring->writeToFile('database_error', [
                'error' => $e->getMessage(),
                'table' => $table,
            ]);
        }
    }

    /**
     * Get database connection
     */
    private function getConnection()
    {
        $model = new \App\Models\BaseModel();
        return $model->getConnection();
    }
} 