<?php

namespace App\Models;

use App\Helpers\Cache;

class CachedPaymentModel extends BaseModel
{
    private $cache;
    private $cacheTTL = 900; // 15 minutes
    private $cachePrefix = 'payment_';

    public function __construct()
    {
        parent::__construct();
        $this->cache = Cache::getInstance();
    }

    /**
     * Get payment by reference with caching
     */
    public function getByReference($reference)
    {
        $cacheKey = $this->cachePrefix . 'ref_' . $reference;
        
        return $this->cache->remember($cacheKey, function() use ($reference) {
            $sql = "SELECT * FROM payment_transactions WHERE reference = :reference";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(['reference' => $reference]);
            return $stmt->fetch();
        }, $this->cacheTTL);
    }

    /**
     * Get payment by ID with caching
     */
    public function getById($id)
    {
        $cacheKey = $this->cachePrefix . 'id_' . $id;
        
        return $this->cache->remember($cacheKey, function() use ($id) {
            $sql = "SELECT * FROM payment_transactions WHERE id = :id";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        }, $this->cacheTTL);
    }

    /**
     * Get payments by user with caching
     */
    public function getByUserId($userId, $limit = 50, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'user_' . $userId . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($userId, $limit, $offset) {
            $sql = "SELECT * FROM payment_transactions 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, $this->cacheTTL);
    }

    /**
     * Get payment statistics with caching
     */
    public function getPaymentStats($period = 'month')
    {
        $cacheKey = $this->cachePrefix . 'stats_' . $period;
        
        return $this->cache->remember($cacheKey, function() use ($period) {
            $dateFilter = $this->getDateFilter($period);
            
            $sql = "SELECT 
                        COUNT(*) as total_transactions,
                        SUM(amount) as total_amount,
                        AVG(amount) as avg_amount,
                        COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_transactions,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
                        SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as successful_amount
                    FROM payment_transactions 
                    WHERE created_at >= :date_filter";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(['date_filter' => $dateFilter]);
            
            return $stmt->fetch();
        }, 1800); // 30 minutes for stats
    }

    /**
     * Get recent payments with caching
     */
    public function getRecentPayments($limit = 20)
    {
        $cacheKey = $this->cachePrefix . 'recent_' . $limit;
        
        return $this->cache->remember($cacheKey, function() use ($limit) {
            $sql = "SELECT pt.*, m.name as member_name, m.email as member_email 
                    FROM payment_transactions pt
                    LEFT JOIN members m ON pt.user_id = m.id
                    ORDER BY pt.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, 600); // 10 minutes for recent payments
    }

    /**
     * Get payments by status with caching
     */
    public function getPaymentsByStatus($status, $limit = 50, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'status_' . $status . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($status, $limit, $offset) {
            $sql = "SELECT pt.*, m.name as member_name, m.email as member_email 
                    FROM payment_transactions pt
                    LEFT JOIN members m ON pt.user_id = m.id
                    WHERE pt.status = :status
                    ORDER BY pt.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, $this->cacheTTL);
    }

    /**
     * Get payment count with caching
     */
    public function getPaymentCount($status = null)
    {
        $cacheKey = $this->cachePrefix . 'count_' . ($status ?? 'all');
        
        return $this->cache->remember($cacheKey, function() use ($status) {
            $sql = "SELECT COUNT(*) as count FROM payment_transactions";
            $params = [];
            
            if ($status) {
                $sql .= " WHERE status = :status";
                $params['status'] = $status;
            }
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['count'] ?? 0;
        }, 600); // 10 minutes for counts
    }

    /**
     * Get payment trends with caching
     */
    public function getPaymentTrends($days = 30)
    {
        $cacheKey = $this->cachePrefix . 'trends_' . $days;
        
        return $this->cache->remember($cacheKey, function() use ($days) {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as transaction_count,
                        SUM(amount) as total_amount,
                        COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_count,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
                    FROM payment_transactions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, 1800); // 30 minutes for trends
    }

    /**
     * Create payment and clear related cache
     */
    public function createPayment($data)
    {
        $sql = "INSERT INTO payment_transactions (
                    user_id, amount, currency, gateway, reference, 
                    status, description, created_at
                ) VALUES (
                    :user_id, :amount, :currency, :gateway, :reference,
                    :status, :description, NOW()
                )";
        
        $stmt = $this->getConnection()->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            $this->clearPaymentCache();
        }
        
        return $result;
    }

    /**
     * Update payment and clear related cache
     */
    public function updatePayment($id, $data)
    {
        $sql = "UPDATE payment_transactions SET 
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->getConnection()->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            $this->clearPaymentCache($id);
        }
        
        return $result;
    }

    /**
     * Clear payment cache
     */
    private function clearPaymentCache($paymentId = null)
    {
        if ($paymentId) {
            $this->cache->delete($this->cachePrefix . 'id_' . $paymentId);
        }
        
        // Clear list caches
        $this->cache->clearPattern($this->cachePrefix . 'user_*');
        $this->cache->clearPattern($this->cachePrefix . 'status_*');
        $this->cache->clearPattern($this->cachePrefix . 'recent_*');
        $this->cache->clearPattern($this->cachePrefix . 'trends_*');
        
        // Clear counts and stats
        $this->cache->delete($this->cachePrefix . 'count_all');
        $this->cache->delete($this->cachePrefix . 'count_success');
        $this->cache->delete($this->cachePrefix . 'count_failed');
        $this->cache->delete($this->cachePrefix . 'count_pending');
        $this->cache->clearPattern($this->cachePrefix . 'stats_*');
    }

    /**
     * Get date filter for statistics
     */
    private function getDateFilter($period)
    {
        switch ($period) {
            case 'day':
                return date('Y-m-d 00:00:00');
            case 'week':
                return date('Y-m-d 00:00:00', strtotime('-7 days'));
            case 'month':
                return date('Y-m-d 00:00:00', strtotime('-30 days'));
            case 'year':
                return date('Y-m-d 00:00:00', strtotime('-1 year'));
            default:
                return date('Y-m-d 00:00:00', strtotime('-30 days'));
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        $keys = $this->cache->getKeys($this->cachePrefix . '*');
        $stats = $this->cache->getStats();
        
        return [
            'payment_cache_keys' => count($keys),
            'cache_stats' => $stats,
            'cache_keys' => array_slice($keys, 0, 20)
        ];
    }

    /**
     * Clear all payment cache
     */
    public function clearAllPaymentCache()
    {
        return $this->cache->clearPattern($this->cachePrefix . '*');
    }
} 