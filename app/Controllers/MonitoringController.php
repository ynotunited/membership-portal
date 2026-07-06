<?php

namespace App\Controllers;

use App\Helpers\Monitoring;
use App\Models\ErrorLogModel;
use App\Models\AuditLogModel;

class MonitoringController extends BaseController
{
    private $monitoring;
    private $errorLogModel;
    private $auditLogModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->monitoring = Monitoring::getInstance();
        $this->errorLogModel = new ErrorLogModel();
        $this->auditLogModel = new AuditLogModel();
    }

    /**
     * Display monitoring dashboard
     */
    public function index()
    {
        $data = [
            'system_health' => $this->monitoring->getSystemHealth(),
            'performance_metrics' => $this->monitoring->getPerformanceMetrics(),
            'recent_errors' => $this->errorLogModel->getMostCommonErrors(10),
            'error_stats' => $this->getErrorStats(),
            'api_usage' => $this->getApiUsageStats(),
            'user_activity' => $this->getUserActivityStats()
        ];

        $this->render('admin/monitoring/dashboard', $data);
    }

    /**
     * Display error logs
     */
    public function errors()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';

        $data = [
            'errors' => $this->errorLogModel->getErrorsByType($type, $perPage),
            'error_types' => $this->getErrorTypes(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $this->errorLogModel->getRecentErrorCount()
            ]
        ];

        $this->render('admin/monitoring/errors', $data);
    }

    /**
     * Display performance metrics
     */
    public function performance()
    {
        $data = [
            'performance_metrics' => $this->monitoring->getPerformanceMetrics(),
            'performance_stats' => $this->getPerformanceStats(),
            'database_performance' => $this->getDatabasePerformance(),
            'api_performance' => $this->getApiPerformance()
        ];

        $this->render('admin/monitoring/performance', $data);
    }

    /**
     * Display system health
     */
    public function health()
    {
        $data = [
            'system_health' => $this->monitoring->getSystemHealth(),
            'health_history' => $this->getHealthHistory(),
            'alerts' => $this->getRecentAlerts()
        ];

        $this->render('admin/monitoring/health', $data);
    }

    /**
     * Display API usage statistics
     */
    public function apiUsage()
    {
        $data = [
            'api_usage' => $this->getApiUsageStats(),
            'rate_limiting' => $this->getRateLimitingStats(),
            'endpoint_performance' => $this->getEndpointPerformance()
        ];

        $this->render('admin/monitoring/api_usage', $data);
    }

    /**
     * Display user activity logs
     */
    public function userActivity()
    {
        $data = [
            'user_activity' => $this->getUserActivityStats(),
            'session_logs' => $this->getSessionLogs(),
            'security_events' => $this->getSecurityEvents()
        ];

        $this->render('admin/monitoring/user_activity', $data);
    }

    /**
     * Generate monitoring report
     */
    public function generateReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $report = $this->monitoring->generateReport($startDate, $endDate);

        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            echo json_encode($report);
            exit;
        }

        $this->render('admin/monitoring/report', [
            'report' => $report,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $alertId = $_POST['alert_id'] ?? null;
        $resolution = $_POST['resolution'] ?? '';

        if ($alertId) {
            $this->acknowledgeAlertById($alertId, $resolution);
            $this->setFlashMessage('success', 'Alert acknowledged successfully');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/monitoring/health');
        exit;
    }

    /**
     * Mark error as resolved
     */
    public function resolveError()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $errorId = $_POST['error_id'] ?? null;
        $resolution = $_POST['resolution'] ?? '';

        if ($errorId) {
            $this->errorLogModel->markErrorResolved($errorId, $resolution);
            $this->setFlashMessage('success', 'Error marked as resolved');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/monitoring/errors');
        exit;
    }

    /**
     * Get system statistics via AJAX
     */
    public function getStats()
    {
        header('Content-Type: application/json');

        $stats = [
            'system_health' => $this->monitoring->getSystemHealth(),
            'performance' => $this->monitoring->getPerformanceMetrics(),
            'recent_errors' => $this->errorLogModel->getRecentErrorCount(1),
            'active_users' => $this->getActiveUsersCount(),
            'api_requests' => $this->getApiRequestsCount()
        ];

        echo json_encode($stats);
        exit;
    }

    /**
     * Export monitoring data
     */
    public function export()
    {
        $type = $_GET['type'] ?? 'errors';
        $format = $_GET['format'] ?? 'csv';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = $this->getExportData($type, $startDate, $endDate);
        $this->exportData($data, $type, $format);
    }

    // Private helper methods

    private function getErrorStats()
    {
        $sql = "SELECT 
                    error_type,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as affected_users,
                    MAX(created_at) as last_occurrence
                FROM error_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY error_type
                ORDER BY count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getApiUsageStats()
    {
        $sql = "SELECT 
                    endpoint,
                    method,
                    COUNT(*) as request_count,
                    AVG(duration) as avg_duration,
                    COUNT(DISTINCT user_id) as unique_users
                FROM api_usage_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint, method
                ORDER BY request_count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getUserActivityStats()
    {
        $sql = "SELECT 
                    user_id,
                    COUNT(*) as action_count,
                    MAX(created_at) as last_action
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY user_id
                ORDER BY action_count DESC
                LIMIT 20";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getErrorTypes()
    {
        $sql = "SELECT DISTINCT error_type FROM error_logs ORDER BY error_type";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getPerformanceStats()
    {
        $sql = "SELECT 
                    operation,
                    COUNT(*) as execution_count,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration
                FROM performance_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY operation
                ORDER BY avg_duration DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getDatabasePerformance()
    {
        $sql = "SELECT 
                    query,
                    COUNT(*) as execution_count,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration
                FROM database_query_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY query
                ORDER BY avg_duration DESC
                LIMIT 20";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getApiPerformance()
    {
        $sql = "SELECT 
                    endpoint,
                    method,
                    AVG(duration) as avg_duration,
                    MAX(duration) as max_duration,
                    COUNT(*) as request_count
                FROM api_usage_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint, method
                ORDER BY avg_duration DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getHealthHistory()
    {
        $sql = "SELECT 
                    check_type,
                    status,
                    created_at
                FROM system_health_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getRecentAlerts()
    {
        $sql = "SELECT * FROM alert_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY created_at DESC
                LIMIT 20";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getRateLimitingStats()
    {
        $sql = "SELECT 
                    endpoint,
                    ip_address,
                    request_count,
                    last_request_at
                FROM rate_limiting_logs 
                WHERE last_request_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY request_count DESC
                LIMIT 20";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getEndpointPerformance()
    {
        $sql = "SELECT 
                    endpoint,
                    method,
                    status_code,
                    COUNT(*) as request_count,
                    AVG(duration) as avg_duration
                FROM api_usage_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint, method, status_code
                ORDER BY request_count DESC";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getSessionLogs()
    {
        $sql = "SELECT 
                    user_id,
                    session_id,
                    ip_address,
                    login_at,
                    logout_at,
                    duration_seconds
                FROM user_session_logs 
                WHERE login_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY login_at DESC
                LIMIT 50";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getSecurityEvents()
    {
        $sql = "SELECT 
                    event_type,
                    description,
                    ip_address,
                    user_id,
                    severity,
                    created_at
                FROM security_event_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY created_at DESC
                LIMIT 50";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function acknowledgeAlertById($alertId, $resolution)
    {
        $sql = "UPDATE alert_logs 
                SET acknowledged = 1, acknowledged_by = :user_id, acknowledged_at = NOW()
                WHERE id = :alert_id";

        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute([
            'alert_id' => $alertId,
            'user_id' => $_SESSION['user_id']
        ]);
    }

    private function getActiveUsersCount()
    {
        $sql = "SELECT COUNT(DISTINCT user_id) as count 
                FROM user_session_logs 
                WHERE login_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    private function getApiRequestsCount()
    {
        $sql = "SELECT COUNT(*) as count 
                FROM api_usage_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    private function getExportData($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'errors':
                return $this->errorLogModel->getErrorStats($startDate, $endDate);
            case 'performance':
                return $this->getPerformanceStats();
            case 'api_usage':
                return $this->getApiUsageStats();
            case 'user_activity':
                return $this->getUserActivityStats();
            default:
                return [];
        }
    }

    private function exportData($data, $type, $format)
    {
        $filename = $type . '_' . date('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            if (!empty($data)) {
                // Write headers
                fputcsv($output, array_keys($data[0]));
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fclose($output);
        } elseif ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        
        exit;
    }

    private function getConnection()
    {
        $model = new \App\Models\BaseModel();
        return $model->getConnection();
    }
} 