<?php

namespace App\Helpers;

class Cache
{
    private static $instance = null;
    private $driver;
    private $config;
    private $prefix = 'gafconl_';
    private $defaultTTL = 3600; // 1 hour

    private function __construct()
    {
        $this->config = $this->loadConfig();
        $this->driver = $this->getDriver();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get cached data
     */
    public function get($key, $default = null)
    {
        $fullKey = $this->prefix . $key;
        
        try {
            $data = $this->driver->get($fullKey);
            
            if ($data === false || $data === null) {
                return $default;
            }
            
            return $data;
        } catch (\Exception $e) {
            $this->logError('Cache get error: ' . $e->getMessage(), [
                'key' => $key,
                'driver' => $this->config['driver']
            ]);
            return $default;
        }
    }

    /**
     * Set cached data
     */
    public function set($key, $value, $ttl = null)
    {
        $fullKey = $this->prefix . $key;
        $ttl = $ttl ?? $this->defaultTTL;
        
        try {
            $result = $this->driver->set($fullKey, $value, $ttl);
            
            if ($result) {
                $this->logCacheOperation('set', $key, $ttl);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError('Cache set error: ' . $e->getMessage(), [
                'key' => $key,
                'ttl' => $ttl,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Delete cached data
     */
    public function delete($key)
    {
        $fullKey = $this->prefix . $key;
        
        try {
            $result = $this->driver->delete($fullKey);
            
            if ($result) {
                $this->logCacheOperation('delete', $key);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError('Cache delete error: ' . $e->getMessage(), [
                'key' => $key,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Check if key exists
     */
    public function has($key)
    {
        $fullKey = $this->prefix . $key;
        
        try {
            return $this->driver->has($fullKey);
        } catch (\Exception $e) {
            $this->logError('Cache has error: ' . $e->getMessage(), [
                'key' => $key,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Get or set cached data
     */
    public function remember($key, $callback, $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * Clear all cache
     */
    public function clear()
    {
        try {
            $result = $this->driver->clear();
            
            if ($result) {
                $this->logCacheOperation('clear');
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError('Cache clear error: ' . $e->getMessage(), [
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Clear cache by pattern
     */
    public function clearPattern($pattern)
    {
        try {
            $result = $this->driver->clearPattern($this->prefix . $pattern);
            
            if ($result) {
                $this->logCacheOperation('clear_pattern', $pattern);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError('Cache clear pattern error: ' . $e->getMessage(), [
                'pattern' => $pattern,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        try {
            return $this->driver->getStats();
        } catch (\Exception $e) {
            $this->logError('Cache stats error: ' . $e->getMessage(), [
                'driver' => $this->config['driver']
            ]);
            return [];
        }
    }

    /**
     * Get cache keys by pattern
     */
    public function getKeys($pattern = '*')
    {
        try {
            $keys = $this->driver->getKeys($this->prefix . $pattern);
            
            // Remove prefix from keys
            return array_map(function($key) {
                return str_replace($this->prefix, '', $key);
            }, $keys);
        } catch (\Exception $e) {
            $this->logError('Cache get keys error: ' . $e->getMessage(), [
                'pattern' => $pattern,
                'driver' => $this->config['driver']
            ]);
            return [];
        }
    }

    /**
     * Increment counter
     */
    public function increment($key, $value = 1)
    {
        $fullKey = $this->prefix . $key;
        
        try {
            return $this->driver->increment($fullKey, $value);
        } catch (\Exception $e) {
            $this->logError('Cache increment error: ' . $e->getMessage(), [
                'key' => $key,
                'value' => $value,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Decrement counter
     */
    public function decrement($key, $value = 1)
    {
        $fullKey = $this->prefix . $key;
        
        try {
            return $this->driver->decrement($fullKey, $value);
        } catch (\Exception $e) {
            $this->logError('Cache decrement error: ' . $e->getMessage(), [
                'key' => $key,
                'value' => $value,
                'driver' => $this->config['driver']
            ]);
            return false;
        }
    }

    /**
     * Get cache driver
     */
    private function getDriver()
    {
        $driver = $this->config['driver'] ?? 'file';
        
        switch ($driver) {
            case 'redis':
                return new CacheDrivers\RedisDriver($this->config);
            case 'memory':
                return new CacheDrivers\MemoryDriver($this->config);
            case 'file':
            default:
                return new CacheDrivers\FileDriver($this->config);
        }
    }

    /**
     * Load cache configuration
     */
    private function loadConfig()
    {
        $config = [
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            'prefix' => $_ENV['CACHE_PREFIX'] ?? 'gafconl_',
            'ttl' => $_ENV['CACHE_TTL'] ?? 3600,
            'file' => [
                'path' => $_ENV['CACHE_FILE_PATH'] ?? __DIR__ . '/../../cache',
                'permissions' => 0755
            ],
            'redis' => [
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                'database' => $_ENV['REDIS_DATABASE'] ?? 0
            ]
        ];

        return $config;
    }

    /**
     * Log cache operations
     */
    private function logCacheOperation($operation, $key = null, $ttl = null)
    {
        if ($this->config['log_operations'] ?? false) {
            $monitoring = Monitoring::getInstance();
            $monitoring->logInfo("Cache {$operation}", [
                'key' => $key,
                'ttl' => $ttl,
                'driver' => $this->config['driver']
            ]);
        }
    }

    /**
     * Log cache errors
     */
    private function logError($message, $context = [])
    {
        $monitoring = Monitoring::getInstance();
        $monitoring->logError(new \Exception($message), $context);
    }
} 