<?php

namespace App\Helpers\CacheDrivers;

class FileDriver implements CacheDriverInterface
{
    private $config;
    private $path;

    public function __construct($config)
    {
        $this->config = $config;
        $this->path = $config['file']['path'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, $config['file']['permissions'], true);
        }
    }

    public function get($key)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return false;
        }
        
        // Check if expired
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            $this->delete($key);
            return false;
        }
        
        return $data['value'];
    }

    public function set($key, $value, $ttl = null)
    {
        $file = $this->getFilePath($key);
        $ttl = $ttl ?? $this->config['ttl'];
        
        $data = [
            'value' => $value,
            'created_at' => time(),
            'expires_at' => time() + $ttl,
            'ttl' => $ttl
        ];
        
        return file_put_contents($file, json_encode($data)) !== false;
    }

    public function delete($key)
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    public function has($key)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return false;
        }
        
        // Check if expired
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }

    public function clear()
    {
        $files = glob($this->path . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    public function clearPattern($pattern)
    {
        $files = glob($this->path . '/' . $pattern . '.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    public function getStats()
    {
        $files = glob($this->path . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $validCount = 0;
        
        foreach ($files as $file) {
            $size = filesize($file);
            $totalSize += $size;
            
            $data = json_decode(file_get_contents($file), true);
            
            if ($data && isset($data['expires_at']) && time() > $data['expires_at']) {
                $expiredCount++;
            } else {
                $validCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_files' => $validCount,
            'expired_files' => $expiredCount,
            'total_size' => $totalSize,
            'path' => $this->path
        ];
    }

    public function getKeys($pattern = '*')
    {
        $files = glob($this->path . '/' . $pattern . '.cache');
        $keys = [];
        
        foreach ($files as $file) {
            $key = basename($file, '.cache');
            $keys[] = $key;
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

    private function getFilePath($key)
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->path . '/' . $safeKey . '.cache';
    }
} 