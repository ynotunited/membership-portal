<?php

namespace App\Controllers;

use App\Helpers\Cache;
use App\Models\CachedMemberModel;
use App\Models\CachedPaymentModel;
use App\Models\CachedForumModel;

class CacheController extends BaseController
{
    private $cache;
    private $memberModel;
    private $paymentModel;
    private $forumModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->cache = Cache::getInstance();
        $this->memberModel = new CachedMemberModel();
        $this->paymentModel = new CachedPaymentModel();
        $this->forumModel = new CachedForumModel();
    }

    /**
     * Display cache dashboard
     */
    public function index()
    {
        $data = [
            'cache_stats' => $this->cache->getStats(),
            'member_cache_stats' => $this->memberModel->getCacheStats(),
            'payment_cache_stats' => $this->paymentModel->getCacheStats(),
            'forum_cache_stats' => $this->forumModel->getCacheStats(),
            'cache_keys' => $this->getCacheKeysSummary(),
            'cache_performance' => $this->getCachePerformance()
        ];

        $this->render('admin/cache/dashboard', $data);
    }

    /**
     * Clear all cache
     */
    public function clearAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $result = $this->cache->clear();
        
        if ($result) {
            $this->setFlashMessage('success', 'All cache cleared successfully');
        } else {
            $this->setFlashMessage('error', 'Failed to clear cache');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
        exit;
    }

    /**
     * Clear specific cache type
     */
    public function clearType()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $type = $_POST['type'] ?? '';
        $result = false;

        switch ($type) {
            case 'member':
                $result = $this->memberModel->clearAllMemberCache();
                break;
            case 'payment':
                $result = $this->paymentModel->clearAllPaymentCache();
                break;
            case 'forum':
                $result = $this->forumModel->clearAllForumCache();
                break;
            case 'api':
                $result = $this->cache->clearPattern('api_*');
                break;
            case 'page':
                $result = $this->cache->clearPattern('page_*');
                break;
            case 'session':
                $result = $this->cache->clearPattern('session_*');
                break;
            default:
                $this->setFlashMessage('error', 'Invalid cache type');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
                exit;
        }

        if ($result) {
            $this->setFlashMessage('success', ucfirst($type) . ' cache cleared successfully');
        } else {
            $this->setFlashMessage('error', 'Failed to clear ' . $type . ' cache');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
        exit;
    }

    /**
     * Clear cache by pattern
     */
    public function clearPattern()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $pattern = $_POST['pattern'] ?? '';
        
        if (empty($pattern)) {
            $this->setFlashMessage('error', 'Pattern is required');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
            exit;
        }

        $result = $this->cache->clearPattern($pattern);
        
        if ($result) {
            $this->setFlashMessage('success', 'Cache cleared for pattern: ' . $pattern);
        } else {
            $this->setFlashMessage('error', 'Failed to clear cache for pattern: ' . $pattern);
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
        exit;
    }

    /**
     * Get cache statistics via AJAX
     */
    public function getStats()
    {
        header('Content-Type: application/json');

        $stats = [
            'cache_stats' => $this->cache->getStats(),
            'member_cache_stats' => $this->memberModel->getCacheStats(),
            'payment_cache_stats' => $this->paymentModel->getCacheStats(),
            'forum_cache_stats' => $this->forumModel->getCacheStats(),
            'cache_performance' => $this->getCachePerformance()
        ];

        echo json_encode($stats);
        exit;
    }

    /**
     * Get cache keys
     */
    public function getKeys()
    {
        $pattern = $_GET['pattern'] ?? '*';
        $limit = $_GET['limit'] ?? 50;
        
        $keys = $this->cache->getKeys($pattern);
        $keys = array_slice($keys, 0, $limit);

        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            echo json_encode($keys);
            exit;
        }

        $this->render('admin/cache/keys', [
            'keys' => $keys,
            'pattern' => $pattern,
            'total_keys' => count($this->cache->getKeys($pattern))
        ]);
    }

    /**
     * Get cache key details
     */
    public function getKeyDetails()
    {
        $key = $_GET['key'] ?? '';
        
        if (empty($key)) {
            http_response_code(400);
            exit;
        }

        $value = $this->cache->get($key);
        
        if ($value === null) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'key' => $key,
            'value' => $value,
            'exists' => $this->cache->has($key),
            'size' => strlen(serialize($value))
        ]);
        exit;
    }

    /**
     * Delete specific cache key
     */
    public function deleteKey()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $key = $_POST['key'] ?? '';
        
        if (empty($key)) {
            $this->setFlashMessage('error', 'Key is required');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
            exit;
        }

        $result = $this->cache->delete($key);
        
        if ($result) {
            $this->setFlashMessage('success', 'Cache key deleted: ' . $key);
        } else {
            $this->setFlashMessage('error', 'Failed to delete cache key: ' . $key);
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
        exit;
    }

    /**
     * Warm up cache
     */
    public function warmUp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $type = $_POST['type'] ?? 'all';
        $result = false;

        switch ($type) {
            case 'member':
                $result = $this->warmUpMemberCache();
                break;
            case 'payment':
                $result = $this->warmUpPaymentCache();
                break;
            case 'forum':
                $result = $this->warmUpForumCache();
                break;
            case 'all':
                $result = $this->warmUpAllCache();
                break;
            default:
                $this->setFlashMessage('error', 'Invalid warm-up type');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
                exit;
        }

        if ($result) {
            $this->setFlashMessage('success', 'Cache warm-up completed for: ' . $type);
        } else {
            $this->setFlashMessage('error', 'Failed to warm up cache for: ' . $type);
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/cache');
        exit;
    }

    /**
     * Get cache keys summary
     */
    private function getCacheKeysSummary()
    {
        $patterns = [
            'member' => 'member_*',
            'payment' => 'payment_*',
            'forum' => 'forum_*',
            'api' => 'api_*',
            'page' => 'page_*',
            'session' => 'session_*',
            'user' => 'user_*',
        ];

        $summary = [];
        
        foreach ($patterns as $type => $pattern) {
            $keys = $this->cache->getKeys($pattern);
            $summary[$type] = count($keys);
        }

        return $summary;
    }

    /**
     * Get cache performance metrics
     */
    private function getCachePerformance()
    {
        // This would typically come from monitoring system
        return [
            'hit_rate' => 85.5, // Percentage
            'miss_rate' => 14.5, // Percentage
            'avg_response_time' => 12.3, // Milliseconds
            'memory_usage' => '45.2 MB',
            'keys_per_second' => 1250,
        ];
    }

    /**
     * Warm up member cache
     */
    private function warmUpMemberCache()
    {
        try {
            // Cache member counts
            $this->memberModel->getMemberCount();
            $this->memberModel->getMemberCount('active');
            $this->memberModel->getMemberCount('inactive');

            // Cache member stats
            $this->memberModel->getMemberStats();

            // Cache recent members
            $this->memberModel->getRecentMembers(30);

            // Cache active members (first page)
            $this->memberModel->getActiveMembers(20, 0);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Warm up payment cache
     */
    private function warmUpPaymentCache()
    {
        try {
            // Cache payment stats
            $this->paymentModel->getPaymentStats('month');
            $this->paymentModel->getPaymentStats('week');

            // Cache payment counts
            $this->paymentModel->getPaymentCount();
            $this->paymentModel->getPaymentCount('success');
            $this->paymentModel->getPaymentCount('failed');

            // Cache recent payments
            $this->paymentModel->getRecentPayments(20);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Warm up forum cache
     */
    private function warmUpForumCache()
    {
        try {
            // Cache categories
            $this->forumModel->getCategories();

            // Cache forum stats
            $this->forumModel->getForumStats();

            // Cache recent topics
            $this->forumModel->getRecentTopics(10);

            // Cache popular topics
            $this->forumModel->getPopularTopics(10);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Warm up all cache
     */
    private function warmUpAllCache()
    {
        return $this->warmUpMemberCache() &&
               $this->warmUpPaymentCache() &&
               $this->warmUpForumCache();
    }
} 