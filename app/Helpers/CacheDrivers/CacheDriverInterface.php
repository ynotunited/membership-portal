<?php

namespace App\Helpers\CacheDrivers;

interface CacheDriverInterface
{
    /**
     * Get cached data
     */
    public function get($key);

    /**
     * Set cached data
     */
    public function set($key, $value, $ttl = null);

    /**
     * Delete cached data
     */
    public function delete($key);

    /**
     * Check if key exists
     */
    public function has($key);

    /**
     * Clear all cache
     */
    public function clear();

    /**
     * Clear cache by pattern
     */
    public function clearPattern($pattern);

    /**
     * Get cache statistics
     */
    public function getStats();

    /**
     * Get cache keys by pattern
     */
    public function getKeys($pattern = '*');

    /**
     * Increment counter
     */
    public function increment($key, $value = 1);

    /**
     * Decrement counter
     */
    public function decrement($key, $value = 1);
} 