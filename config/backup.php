<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the automated backup system
    | including scheduling, storage, compression, encryption, and notifications.
    |
    */

    // Enable/disable automated backups
    'enabled' => env('BACKUP_ENABLED', true),

    // Backup Scheduling
    'schedule' => [
        'frequency' => env('BACKUP_FREQUENCY', 'daily'), // hourly, daily, weekly, monthly
        'time' => env('BACKUP_TIME', '02:00'), // HH:MM format
        'timezone' => env('BACKUP_TIMEZONE', 'UTC'),
        'max_concurrent' => env('BACKUP_MAX_CONCURRENT', 1),
        'timeout' => env('BACKUP_TIMEOUT', 3600), // seconds
    ],

    // Backup Storage
    'storage' => [
        'path' => env('BACKUP_PATH', __DIR__ . '/../backups'),
        'max_size_mb' => env('BACKUP_MAX_SIZE_MB', 1024),
        'permissions' => env('BACKUP_FILE_PERMISSIONS', 0644),
        'directory_permissions' => env('BACKUP_DIR_PERMISSIONS', 0755),
    ],

    // Backup Compression
    'compression' => [
        'enabled' => env('BACKUP_COMPRESSION_ENABLED', true),
        'level' => env('BACKUP_COMPRESSION_LEVEL', 9), // 1-9
        'algorithm' => env('BACKUP_COMPRESSION_ALGORITHM', 'gzip'),
        'threshold' => env('BACKUP_COMPRESSION_THRESHOLD', 1024), // bytes
    ],

    // Backup Encryption
    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', false),
        'key' => env('BACKUP_ENCRYPTION_KEY', null),
        'algorithm' => env('BACKUP_ENCRYPTION_ALGORITHM', 'aes-256-cbc'),
        'sensitive_tables' => [
            'users',
            'payment_transactions',
            'audit_logs',
            'error_logs',
        ],
    ],

    // Backup Retention
    'retention' => [
        'max_age_days' => env('BACKUP_RETENTION_DAYS', 30),
        'max_size_mb' => env('BACKUP_RETENTION_SIZE_MB', 1024),
        'max_count' => env('BACKUP_RETENTION_COUNT', null),
        'keep_minimum' => env('BACKUP_RETENTION_MINIMUM', 3),
    ],

    // Backup Types
    'types' => [
        'full' => [
            'enabled' => true,
            'frequency' => 'daily',
            'compression' => true,
            'encryption' => false,
        ],
        'incremental' => [
            'enabled' => false,
            'frequency' => 'hourly',
            'compression' => true,
            'encryption' => false,
        ],
        'differential' => [
            'enabled' => false,
            'frequency' => 'weekly',
            'compression' => true,
            'encryption' => false,
        ],
    ],

    // Database Configuration
    'database' => [
        'mysql' => [
            'enabled' => true,
            'options' => [
                'single_transaction' => true,
                'routines' => true,
                'triggers' => true,
                'events' => true,
                'add_drop_table' => true,
                'add_drop_database' => false,
                'add_locks' => true,
                'add_inserts' => true,
                'complete_insert' => false,
                'delayed_insert' => false,
                'extended_insert' => true,
                'lock_tables' => false,
                'set_charset' => true,
                'skip_add_locks' => false,
                'skip_disable_keys' => false,
                'skip_extended_insert' => false,
                'skip_opt' => false,
                'skip_quote_names' => false,
                'skip_set_charset' => false,
                'skip_tz_utc' => false,
            ],
        ],
        'postgresql' => [
            'enabled' => true,
            'options' => [
                'verbose' => true,
                'clean' => true,
                'create' => true,
                'data_only' => false,
                'schema_only' => false,
                'no_owner' => true,
                'no_privileges' => true,
                'no_security_labels' => true,
                'no_tablespaces' => true,
                'no_unlogged_table_data' => true,
            ],
        ],
        'sqlite' => [
            'enabled' => true,
            'options' => [
                'backup_mode' => 'copy', // copy, dump
            ],
        ],
    ],

    // Backup Verification
    'verification' => [
        'enabled' => env('BACKUP_VERIFICATION_ENABLED', true),
        'types' => [
            'integrity' => true,
            'restore_test' => false, // Be careful with this in production
            'size_check' => true,
        ],
        'frequency' => 'after_backup', // after_backup, daily, weekly
        'timeout' => env('BACKUP_VERIFICATION_TIMEOUT', 300), // seconds
    ],

    // Backup Notifications
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATION_ENABLED', true),
        'channels' => [
            'email' => [
                'enabled' => true,
                'recipients' => [
                    env('BACKUP_NOTIFICATION_EMAIL', 'admin@gafconl.com'),
                ],
                'subject_prefix' => '[GAFCONL Backup]',
            ],
            'slack' => [
                'enabled' => env('BACKUP_SLACK_ENABLED', false),
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', null),
                'channel' => env('BACKUP_SLACK_CHANNEL', '#backups'),
                'username' => env('BACKUP_SLACK_USERNAME', 'Backup Bot'),
            ],
            'sms' => [
                'enabled' => env('BACKUP_SMS_ENABLED', false),
                'provider' => env('BACKUP_SMS_PROVIDER', 'twilio'),
                'recipients' => [
                    env('BACKUP_SMS_RECIPIENT', null),
                ],
            ],
        ],
        'events' => [
            'backup_success' => true,
            'backup_failure' => true,
            'backup_verification_failed' => true,
            'backup_cleanup' => false,
        ],
    ],

    // Backup Monitoring
    'monitoring' => [
        'enabled' => env('BACKUP_MONITORING_ENABLED', true),
        'metrics' => [
            'backup_duration' => true,
            'backup_size' => true,
            'backup_success_rate' => true,
            'storage_usage' => true,
            'verification_results' => true,
        ],
        'alerts' => [
            'backup_failure' => true,
            'backup_timeout' => true,
            'storage_full' => true,
            'verification_failed' => true,
        ],
    ],

    // Backup Logging
    'logging' => [
        'enabled' => env('BACKUP_LOGGING_ENABLED', true),
        'level' => env('BACKUP_LOG_LEVEL', 'info'), // debug, info, warning, error
        'file' => env('BACKUP_LOG_FILE', __DIR__ . '/../logs/backup.log'),
        'max_files' => env('BACKUP_LOG_MAX_FILES', 30),
        'max_size' => env('BACKUP_LOG_MAX_SIZE', 10), // MB
    ],

    // Backup Security
    'security' => [
        'encrypt_sensitive' => env('BACKUP_ENCRYPT_SENSITIVE', true),
        'sensitive_tables' => [
            'users',
            'payment_transactions',
            'audit_logs',
            'error_logs',
            'backup_logs',
        ],
        'access_control' => [
            'require_admin' => true,
            'ip_whitelist' => env('BACKUP_IP_WHITELIST', null),
            'api_key_required' => env('BACKUP_API_KEY_REQUIRED', false),
        ],
    ],

    // Backup Performance
    'performance' => [
        'max_memory_mb' => env('BACKUP_MAX_MEMORY_MB', 512),
        'max_execution_time' => env('BACKUP_MAX_EXECUTION_TIME', 3600), // seconds
        'chunk_size' => env('BACKUP_CHUNK_SIZE', 8192), // bytes
        'parallel_processing' => env('BACKUP_PARALLEL_PROCESSING', false),
    ],

    // Backup Testing
    'testing' => [
        'enabled' => env('BACKUP_TESTING_ENABLED', false),
        'test_restore' => env('BACKUP_TEST_RESTORE', false),
        'test_database' => env('BACKUP_TEST_DATABASE', 'gafconl_test'),
        'cleanup_after_test' => env('BACKUP_CLEANUP_AFTER_TEST', true),
    ],

    // Backup External Storage
    'external_storage' => [
        'enabled' => env('BACKUP_EXTERNAL_STORAGE_ENABLED', false),
        'providers' => [
            's3' => [
                'enabled' => env('BACKUP_S3_ENABLED', false),
                'bucket' => env('BACKUP_S3_BUCKET', null),
                'region' => env('BACKUP_S3_REGION', 'us-east-1'),
                'key' => env('BACKUP_S3_KEY', null),
                'secret' => env('BACKUP_S3_SECRET', null),
                'path' => env('BACKUP_S3_PATH', 'backups'),
            ],
            'ftp' => [
                'enabled' => env('BACKUP_FTP_ENABLED', false),
                'host' => env('BACKUP_FTP_HOST', null),
                'port' => env('BACKUP_FTP_PORT', 21),
                'username' => env('BACKUP_FTP_USERNAME', null),
                'password' => env('BACKUP_FTP_PASSWORD', null),
                'path' => env('BACKUP_FTP_PATH', '/backups'),
                'passive' => env('BACKUP_FTP_PASSIVE', true),
            ],
            'sftp' => [
                'enabled' => env('BACKUP_SFTP_ENABLED', false),
                'host' => env('BACKUP_SFTP_HOST', null),
                'port' => env('BACKUP_SFTP_PORT', 22),
                'username' => env('BACKUP_SFTP_USERNAME', null),
                'password' => env('BACKUP_SFTP_PASSWORD', null),
                'private_key' => env('BACKUP_SFTP_PRIVATE_KEY', null),
                'path' => env('BACKUP_SFTP_PATH', '/backups'),
            ],
        ],
    ],

    // Backup Maintenance
    'maintenance' => [
        'enabled' => env('BACKUP_MAINTENANCE_ENABLED', true),
        'cleanup_old_logs' => env('BACKUP_CLEANUP_LOGS', true),
        'cleanup_old_logs_days' => env('BACKUP_CLEANUP_LOGS_DAYS', 90),
        'optimize_tables' => env('BACKUP_OPTIMIZE_TABLES', false),
        'analyze_tables' => env('BACKUP_ANALYZE_TABLES', false),
    ],

    // Backup Debug Settings
    'debug' => [
        'enabled' => env('BACKUP_DEBUG_ENABLED', false),
        'log_commands' => env('BACKUP_DEBUG_LOG_COMMANDS', false),
        'log_sql' => env('BACKUP_DEBUG_LOG_SQL', false),
        'verbose_output' => env('BACKUP_DEBUG_VERBOSE', false),
    ],
]; 