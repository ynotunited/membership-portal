<?php

namespace App\Models;

use App\Helpers\Cache;

class CachedMemberModel extends MemberModel
{
    private $cache;
    private $cacheTTL = 1800; // 30 minutes
    private $cachePrefix = 'member_';

    public function __construct()
    {
        parent::__construct();
        $this->cache = Cache::getInstance();
    }

    /**
     * Get member by ID with caching
     */
    public function getById($id)
    {
        $cacheKey = $this->cachePrefix . 'id_' . $id;
        
        return $this->cache->remember($cacheKey, function() use ($id) {
            return parent::getById($id);
        }, $this->cacheTTL);
    }

    /**
     * Get member by email with caching
     */
    public function getByEmail($email)
    {
        $cacheKey = $this->cachePrefix . 'email_' . md5($email);
        
        return $this->cache->remember($cacheKey, function() use ($email) {
            return parent::getByEmail($email);
        }, $this->cacheTTL);
    }

    /**
     * Get member by phone with caching
     */
    public function getByPhone($phone)
    {
        $cacheKey = $this->cachePrefix . 'phone_' . md5($phone);
        
        return $this->cache->remember($cacheKey, function() use ($phone) {
            return parent::getByPhone($phone);
        }, $this->cacheTTL);
    }

    /**
     * Get active members with caching
     */
    public function getActiveMembers($limit = null, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'active_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($limit, $offset) {
            return parent::getActiveMembers($limit, $offset);
        }, $this->cacheTTL);
    }

    /**
     * Get member count with caching
     */
    public function getMemberCount($status = null)
    {
        $cacheKey = $this->cachePrefix . 'count_' . ($status ?? 'all');
        
        return $this->cache->remember($cacheKey, function() use ($status) {
            return parent::getMemberCount($status);
        }, 600); // 10 minutes for counts
    }

    /**
     * Get recent members with caching
     */
    public function getRecentMembers($days = 30)
    {
        $cacheKey = $this->cachePrefix . 'recent_' . $days;
        
        return $this->cache->remember($cacheKey, function() use ($days) {
            return parent::getRecentMembers($days);
        }, 1800); // 30 minutes
    }

    /**
     * Get member statistics with caching
     */
    public function getMemberStats()
    {
        $cacheKey = $this->cachePrefix . 'stats';
        
        return $this->cache->remember($cacheKey, function() {
            return parent::getMemberStats();
        }, 3600); // 1 hour for stats
    }

    /**
     * Search members with caching
     */
    public function searchMembers($query, $limit = 50, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'search_' . md5($query) . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($query, $limit, $offset) {
            return parent::searchMembers($query, $limit, $offset);
        }, 900); // 15 minutes for search results
    }

    /**
     * Get members by status with caching
     */
    public function getMembersByStatus($status, $limit = null, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'status_' . $status . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($status, $limit, $offset) {
            return parent::getMembersByStatus($status, $limit, $offset);
        }, $this->cacheTTL);
    }

    /**
     * Get members by role with caching
     */
    public function getMembersByRole($role, $limit = null, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'role_' . $role . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($role, $limit, $offset) {
            return parent::getMembersByRole($role, $limit, $offset);
        }, $this->cacheTTL);
    }

    /**
     * Create member and clear related cache
     */
    public function createMember($data)
    {
        $result = parent::createMember($data);
        
        if ($result) {
            $this->clearMemberCache();
        }
        
        return $result;
    }

    /**
     * Update member and clear related cache
     */
    public function updateMember($id, $data)
    {
        $result = parent::updateMember($id, $data);
        
        if ($result) {
            $this->clearMemberCache($id);
        }
        
        return $result;
    }

    /**
     * Delete member and clear related cache
     */
    public function deleteMember($id)
    {
        $result = parent::deleteMember($id);
        
        if ($result) {
            $this->clearMemberCache($id);
        }
        
        return $result;
    }

    /**
     * Clear member cache
     */
    private function clearMemberCache($memberId = null)
    {
        if ($memberId) {
            // Clear specific member cache
            $this->cache->delete($this->cachePrefix . 'id_' . $memberId);
            
            // Get member data to clear email/phone cache
            $member = parent::getById($memberId);
            if ($member) {
                if ($member['email']) {
                    $this->cache->delete($this->cachePrefix . 'email_' . md5($member['email']));
                }
                if ($member['phone']) {
                    $this->cache->delete($this->cachePrefix . 'phone_' . md5($member['phone']));
                }
            }
        }
        
        // Clear list caches
        $this->cache->clearPattern($this->cachePrefix . 'active_*');
        $this->cache->clearPattern($this->cachePrefix . 'status_*');
        $this->cache->clearPattern($this->cachePrefix . 'role_*');
        $this->cache->clearPattern($this->cachePrefix . 'recent_*');
        $this->cache->clearPattern($this->cachePrefix . 'search_*');
        
        // Clear count and stats
        $this->cache->delete($this->cachePrefix . 'count_all');
        $this->cache->delete($this->cachePrefix . 'count_active');
        $this->cache->delete($this->cachePrefix . 'count_inactive');
        $this->cache->delete($this->cachePrefix . 'stats');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        $keys = $this->cache->getKeys($this->cachePrefix . '*');
        $stats = $this->cache->getStats();
        
        return [
            'member_cache_keys' => count($keys),
            'cache_stats' => $stats,
            'cache_keys' => array_slice($keys, 0, 20) // First 20 keys
        ];
    }

    /**
     * Clear all member cache
     */
    public function clearAllMemberCache()
    {
        return $this->cache->clearPattern($this->cachePrefix . '*');
    }
} 