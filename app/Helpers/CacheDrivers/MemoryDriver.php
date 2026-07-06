<?php

namespace App\Helpers\CacheDrivers;

class MemoryDriver implements CacheDriverInterface
{
    private $config;
    private $data = [];
    private $expires = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }
        
        // Check if expired
        if (isset($this->expires[$key]) && time() > $this->expires[$key]) {
            $this->delete($key);
            return false;
        }
        
        return $this->data[$key];
    }

    public function set($key, $value, $ttl = null)
    {
        $this->data[$key] = $value;
        
        if ($ttl) {
            $this->expires[$key] = time() + $ttl;
        } else {
            unset($this->expires[$key]);
        }
        
        return true;
    }

    public function delete($key)
    {
        unset($this->data[$key]);
        unset($this->expires[$key]);
        
        return true;
    }

    public function has($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }
        
        // Check if expired
        if (isset($this->expires[$key]) && time() > $this->expires[$key]) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }

    public function clear()
    {
        $this->data = [];
        $this->expires = [];
        
        return true;
    }

    public function clearPattern($pattern)
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        foreach ($this->data as $key => $value) {
            if (preg_match($pattern, $key)) {
                $this->delete($key);
            }
        }
        
        return true;
    }

    public function getStats()
    {
        $expiredCount = 0;
        $validCount = 0;
        
        foreach ($this->expires as $key => $expires) {
            if (time() > $expires) {
                $expiredCount++;
            } else {
                $validCount++;
            }
        }
        
        return [
            'total_keys' => count($this->data),
            'valid_keys' => $validCount,
            'expired_keys' => $expiredCount,
            'memory_usage' => memory_get_usage(true)
        ];
    }

    public function getKeys($pattern = '*')
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        $keys = [];
        
        foreach ($this->data as $key => $value) {
            if (preg_match($pattern, $key)) {
                $keys[] = $key;
            }
        }
        
        return $keys;
    }

    public function increment($key, $value = 1)
    {
        $current = $this->get($key);
        
        if ($current === false) {
            $current = 0;
        }
        
        $newValue = $current + $value;
        $this->set($key, $newValue);
        
        return $newValue;
    }

    public function decrement($key, $value = 1)
    {
        return $this->increment($key, -$value);
    }
} 