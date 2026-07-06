<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the caching system
    | including driver settings, TTL values, and cache strategies.
    |
    */

    // Default Cache Driver
    'default' => env('CACHE_DRIVER', 'file'),

    // Cache Drivers
    'drivers' => [
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_FILE_PATH', __DIR__ . '/../cache'),
            'permissions' => env('CACHE_FILE_PERMISSIONS', 0755),
            'prefix' => env('CACHE_PREFIX', 'gafconl_'),
        ],
        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_DATABASE', 0),
            'prefix' => env('CACHE_PREFIX', 'gafconl_'),
        ],
        'memory' => [
            'driver' => 'memory',
            'prefix' => env('CACHE_PREFIX', 'gafconl_'),
        ],
    ],

    // Cache TTL Settings (in seconds)
    'ttl' => [
        'default' => env('CACHE_TTL', 3600), // 1 hour
        'short' => 300, // 5 minutes
        'medium' => 1800, // 30 minutes
        'long' => 7200, // 2 hours
        'very_long' => 86400, // 24 hours
    ],

    // Model-specific Cache Settings
    'models' => [
        'member' => [
            'enabled' => env('CACHE_MEMBER_ENABLED', true),
            'ttl' => env('CACHE_MEMBER_TTL', 1800), // 30 minutes
            'prefix' => 'member_',
            'strategies' => [
                'individual' => true,
                'lists' => true,
                'counts' => true,
                'stats' => true,
            ],
        ],
        'payment' => [
            'enabled' => env('CACHE_PAYMENT_ENABLED', true),
            'ttl' => env('CACHE_PAYMENT_TTL', 900), // 15 minutes
            'prefix' => 'payment_',
            'strategies' => [
                'individual' => true,
                'lists' => true,
                'stats' => true,
                'trends' => true,
            ],
        ],
        'forum' => [
            'enabled' => env('CACHE_FORUM_ENABLED', true),
            'ttl' => env('CACHE_FORUM_TTL', 1200), // 20 minutes
            'prefix' => 'forum_',
            'strategies' => [
                'categories' => true,
                'topics' => true,
                'posts' => true,
                'recent' => true,
                'popular' => true,
                'stats' => true,
            ],
        ],
        'user' => [
            'enabled' => env('CACHE_USER_ENABLED', true),
            'ttl' => env('CACHE_USER_TTL', 3600), // 1 hour
            'prefix' => 'user_',
            'strategies' => [
                'profile' => true,
                'permissions' => true,
                'sessions' => true,
            ],
        ],
    ],

    // API Cache Settings
    'api' => [
        'enabled' => env('CACHE_API_ENABLED', true),
        'ttl' => env('CACHE_API_TTL', 600), // 10 minutes
        'prefix' => 'api_',
        'endpoints' => [
            'members' => [
                'enabled' => true,
                'ttl' => 300, // 5 minutes
                'methods' => ['GET'],
            ],
            'payments' => [
                'enabled' => true,
                'ttl' => 180, // 3 minutes
                'methods' => ['GET'],
            ],
            'forum' => [
                'enabled' => true,
                'ttl' => 600, // 10 minutes
                'methods' => ['GET'],
            ],
            'reports' => [
                'enabled' => true,
                'ttl' => 1800, // 30 minutes
                'methods' => ['GET'],
            ],
        ],
    ],

    // Page Cache Settings
    'pages' => [
        'enabled' => env('CACHE_PAGES_ENABLED', false),
        'ttl' => env('CACHE_PAGES_TTL', 3600), // 1 hour
        'prefix' => 'page_',
        'excluded_routes' => [
            '/admin/*',
            '/api/*',
            '/login',
            '/logout',
            '/register',
        ],
    ],

    // Session Cache Settings
    'sessions' => [
        'enabled' => env('CACHE_SESSIONS_ENABLED', true),
        'ttl' => env('CACHE_SESSIONS_TTL', 1800), // 30 minutes
        'prefix' => 'session_',
    ],

    // Query Cache Settings
    'queries' => [
        'enabled' => env('CACHE_QUERIES_ENABLED', true),
        'ttl' => env('CACHE_QUERIES_TTL', 300), // 5 minutes
        'prefix' => 'query_',
        'slow_query_threshold' => env('CACHE_SLOW_QUERY_THRESHOLD', 1000), // 1 second
    ],

    // Cache Invalidation Settings
    'invalidation' => [
        'enabled' => env('CACHE_INVALIDATION_ENABLED', true),
        'strategies' => [
            'time_based' => true,
            'event_based' => true,
            'manual' => true,
        ],
        'events' => [
            'member_created' => ['member_*', 'stats_*'],
            'member_updated' => ['member_*'],
            'member_deleted' => ['member_*', 'stats_*'],
            'payment_created' => ['payment_*', 'stats_*'],
            'payment_updated' => ['payment_*'],
            'forum_topic_created' => ['forum_*'],
            'forum_post_created' => ['forum_*'],
        ],
    ],

    // Cache Monitoring
    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),
        'log_operations' => env('CACHE_LOG_OPERATIONS', false),
        'log_hits' => env('CACHE_LOG_HITS', false),
        'log_misses' => env('CACHE_LOG_MISSES', false),
        'metrics' => [
            'hit_rate' => true,
            'miss_rate' => true,
            'memory_usage' => true,
            'key_count' => true,
        ],
    ],

    // Cache Compression
    'compression' => [
        'enabled' => env('CACHE_COMPRESSION_ENABLED', false),
        'threshold' => env('CACHE_COMPRESSION_THRESHOLD', 1024), // 1KB
        'algorithm' => env('CACHE_COMPRESSION_ALGORITHM', 'gzip'),
    ],

    // Cache Security
    'security' => [
        'encrypt_sensitive' => env('CACHE_ENCRYPT_SENSITIVE', true),
        'sensitive_keys' => [
            'user_*',
            'session_*',
            'auth_*',
        ],
        'encryption_key' => env('CACHE_ENCRYPTION_KEY', null),
    ],

    // Cache Maintenance
    'maintenance' => [
        'auto_cleanup' => env('CACHE_AUTO_CLEANUP', true),
        'cleanup_interval' => env('CACHE_CLEANUP_INTERVAL', 3600), // 1 hour
        'max_age' => env('CACHE_MAX_AGE', 86400), // 24 hours
        'max_size' => env('CACHE_MAX_SIZE', 1073741824), // 1GB
    ],

    // Cache Debug Settings
    'debug' => [
        'enabled' => env('CACHE_DEBUG_ENABLED', false),
        'log_queries' => env('CACHE_DEBUG_LOG_QUERIES', false),
        'show_hits' => env('CACHE_DEBUG_SHOW_HITS', false),
        'show_misses' => env('CACHE_DEBUG_SHOW_MISSES', false),
    ],
]; 