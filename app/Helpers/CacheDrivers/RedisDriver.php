<?php

namespace App\Helpers\CacheDrivers;

class RedisDriver implements CacheDriverInterface
{
    private $config;
    private $redis;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public function get($key)
    {
        try {
            $value = $this->redis->get($key);
            
            if ($value === false) {
                return false;
            }
            
            return json_decode($value, true);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function set($key, $value, $ttl = null)
    {
        try {
            $serialized = json_encode($value);
            
            if ($ttl) {
                return $this->redis->setex($key, $ttl, $serialized);
            } else {
                return $this->redis->set($key, $serialized);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($key)
    {
        try {
            return $this->redis->del($key) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function has($key)
    {
        try {
            return $this->redis->exists($key);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function clear()
    {
        try {
            return $this->redis->flushdb();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function clearPattern($pattern)
    {
        try {
            $keys = $this->redis->keys($pattern);
            
            if (!empty($keys)) {
                return $this->redis->del($keys) > 0;
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getStats()
    {
        try {
            $info = $this->redis->info();
            
            return [
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'total_keys' => $info['db0'] ?? 0,
                'uptime' => $info['uptime_in_seconds'] ?? 0
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getKeys($pattern = '*')
    {
        try {
            return $this->redis->keys($pattern);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function increment($key, $value = 1)
    {
        try {
            return $this->redis->incrBy($key, $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function decrement($key, $value = 1)
    {
        try {
            return $this->redis->decrBy($key, $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function connect()
    {
        try {
            $this->redis = new \Redis();
            
            $this->redis->connect(
                $this->config['redis']['host'],
                $this->config['redis']['port']
            );
            
            if ($this->config['redis']['password']) {
                $this->redis->auth($this->config['redis']['password']);
            }
            
            if ($this->config['redis']['database']) {
                $this->redis->select($this->config['redis']['database']);
            }
            
        } catch (\Exception $e) {
            throw new \Exception('Redis connection failed: ' . $e->getMessage());
        }
    }
} 