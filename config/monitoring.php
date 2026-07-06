<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the monitoring system
    | including error tracking, performance monitoring, and alerting.
    |
    */

    // Error Logging Configuration
    'error_logging' => [
        'enabled' => env('ERROR_LOGGING_ENABLED', true),
        'retention_days' => env('ERROR_LOG_RETENTION_DAYS', 30),
        'log_to_file' => env('ERROR_LOG_TO_FILE', true),
        'log_to_database' => env('ERROR_LOG_TO_DATABASE', true),
        'external_service' => env('ERROR_EXTERNAL_SERVICE', false),
    ],

    // Performance Monitoring Configuration
    'performance_monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        'retention_days' => env('PERFORMANCE_LOG_RETENTION_DAYS', 7),
        'log_database_queries' => env('LOG_DATABASE_QUERIES', true),
        'log_api_usage' => env('LOG_API_USAGE', true),
        'log_payment_transactions' => env('LOG_PAYMENT_TRANSACTIONS', true),
    ],

    // Alert Configuration
    'alerts' => [
        'enabled' => env('ALERTS_ENABLED', true),
        'email_enabled' => env('ALERT_EMAIL_ENABLED', true),
        'admin_email' => env('ADMIN_EMAIL', 'admin@gafconl.com'),
        'external_service' => env('EXTERNAL_MONITORING_ENABLED', false),
        'external_url' => env('MONITORING_URL', ''),
        'external_api_key' => env('MONITORING_API_KEY', ''),
    ],

    // Rate Limiting Configuration
    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        'default_limit' => env('RATE_LIMIT_DEFAULT', 100),
        'default_window' => env('RATE_LIMIT_WINDOW', 3600), // 1 hour
        'api_limits' => [
            'authentication' => ['limit' => 5, 'window' => 60], // 5 requests per minute
            'member_operations' => ['limit' => 100, 'window' => 3600], // 100 requests per hour
            'payment_operations' => ['limit' => 50, 'window' => 3600], // 50 requests per hour
            'forum_operations' => ['limit' => 200, 'window' => 3600], // 200 requests per hour
            'ai_chat' => ['limit' => 30, 'window' => 60], // 30 requests per minute
        ],
    ],

    // System Health Thresholds
    'health_thresholds' => [
        'error_rate' => env('ERROR_RATE_THRESHOLD', 5), // 5% error rate
        'memory_usage' => env('MEMORY_USAGE_THRESHOLD', 80), // 80% memory usage
        'disk_usage' => env('DISK_USAGE_THRESHOLD', 85), // 85% disk usage
        'response_time' => env('RESPONSE_TIME_THRESHOLD', 2000), // 2 seconds
        'database_connections' => env('DB_CONNECTION_THRESHOLD', 50),
    ],

    // Security Monitoring
    'security_monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        'log_failed_logins' => env('LOG_FAILED_LOGINS', true),
        'log_suspicious_activity' => env('LOG_SUSPICIOUS_ACTIVITY', true),
        'log_rate_limit_violations' => env('LOG_RATE_LIMIT_VIOLATIONS', true),
        'alert_on_security_events' => env('ALERT_ON_SECURITY_EVENTS', true),
    ],

    // User Activity Monitoring
    'user_activity_monitoring' => [
        'enabled' => env('USER_ACTIVITY_MONITORING_ENABLED', true),
        'log_user_actions' => env('LOG_USER_ACTIONS', true),
        'log_session_data' => env('LOG_SESSION_DATA', true),
        'retention_days' => env('USER_ACTIVITY_RETENTION_DAYS', 90),
    ],

    // Database Monitoring
    'database_monitoring' => [
        'enabled' => env('DATABASE_MONITORING_ENABLED', true),
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // 1 second
        'log_connection_errors' => env('LOG_CONNECTION_ERRORS', true),
        'log_deadlocks' => env('LOG_DEADLOCKS', true),
    ],

    // Payment Monitoring
    'payment_monitoring' => [
        'enabled' => env('PAYMENT_MONITORING_ENABLED', true),
        'log_all_transactions' => env('LOG_ALL_TRANSACTIONS', true),
        'log_failed_payments' => env('LOG_FAILED_PAYMENTS', true),
        'alert_on_payment_failures' => env('ALERT_ON_PAYMENT_FAILURES', true),
        'retention_days' => env('PAYMENT_LOG_RETENTION_DAYS', 30),
    ],

    // API Monitoring
    'api_monitoring' => [
        'enabled' => env('API_MONITORING_ENABLED', true),
        'log_all_requests' => env('LOG_ALL_API_REQUESTS', true),
        'log_response_times' => env('LOG_API_RESPONSE_TIMES', true),
        'log_error_responses' => env('LOG_API_ERROR_RESPONSES', true),
        'retention_days' => env('API_LOG_RETENTION_DAYS', 14),
    ],

    // Log File Configuration
    'log_files' => [
        'directory' => env('LOG_DIRECTORY', __DIR__ . '/../logs'),
        'max_file_size' => env('LOG_MAX_FILE_SIZE', 10485760), // 10MB
        'max_files' => env('LOG_MAX_FILES', 30),
        'permissions' => env('LOG_PERMISSIONS', 0755),
    ],

    // Monitoring Dashboard
    'dashboard' => [
        'enabled' => env('MONITORING_DASHBOARD_ENABLED', true),
        'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // seconds
        'max_display_items' => env('DASHBOARD_MAX_ITEMS', 100),
        'chart_data_points' => env('DASHBOARD_CHART_POINTS', 24),
    ],

    // External Monitoring Services
    'external_services' => [
        'sentry' => [
            'enabled' => env('SENTRY_ENABLED', false),
            'dsn' => env('SENTRY_DSN', ''),
        ],
        'new_relic' => [
            'enabled' => env('NEW_RELIC_ENABLED', false),
            'license_key' => env('NEW_RELIC_LICENSE_KEY', ''),
            'app_name' => env('NEW_RELIC_APP_NAME', 'GAFCONL'),
        ],
        'datadog' => [
            'enabled' => env('DATADOG_ENABLED', false),
            'api_key' => env('DATADOG_API_KEY', ''),
            'app_key' => env('DATADOG_APP_KEY', ''),
        ],
    ],

    // Notification Channels
    'notifications' => [
        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
            'from_address' => env('NOTIFICATION_FROM_EMAIL', 'noreply@gafconl.com'),
            'from_name' => env('NOTIFICATION_FROM_NAME', 'GAFCONL System'),
        ],
        'slack' => [
            'enabled' => env('SLACK_NOTIFICATIONS_ENABLED', false),
            'webhook_url' => env('SLACK_WEBHOOK_URL', ''),
            'channel' => env('SLACK_CHANNEL', '#alerts'),
        ],
        'sms' => [
            'enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
            'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
            'from_number' => env('TWILIO_FROM_NUMBER', ''),
        ],
    ],

    // Maintenance Mode
    'maintenance_mode' => [
        'enabled' => env('MAINTENANCE_MODE_ENABLED', false),
        'allowed_ips' => explode(',', env('MAINTENANCE_ALLOWED_IPS', '127.0.0.1')),
        'message' => env('MAINTENANCE_MESSAGE', 'System is under maintenance. Please try again later.'),
    ],

    // Debug Mode
    'debug' => [
        'enabled' => env('APP_DEBUG', false),
        'log_queries' => env('DEBUG_LOG_QUERIES', false),
        'log_requests' => env('DEBUG_LOG_REQUESTS', false),
        'log_responses' => env('DEBUG_LOG_RESPONSES', false),
    ],
]; 