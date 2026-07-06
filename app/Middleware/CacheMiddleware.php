<?php

namespace App\Middleware;

use App\Helpers\Cache;

class CacheMiddleware
{
    private $cache;
    private $config;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
        $this->config = $this->loadConfig();
    }

    /**
     * Handle the request and add caching
     */
    public function handle()
    {
        // Skip caching for non-GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        // Skip caching for excluded routes
        if ($this->isExcludedRoute()) {
            return;
        }

        // Check if response is cacheable
        if (!$this->isCacheable()) {
            return;
        }

        // Try to get cached response
        $cachedResponse = $this->getCachedResponse();
        if ($cachedResponse !== null) {
            $this->serveCachedResponse($cachedResponse);
        }

        // Start output buffering to capture response
        ob_start();
    }

    /**
     * Store response in cache
     */
    public function storeResponse()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        if ($this->isExcludedRoute()) {
            return;
        }

        if (!$this->isCacheable()) {
            return;
        }

        $response = ob_get_clean();
        
        if ($response && $this->shouldCacheResponse($response)) {
            $this->cacheResponse($response);
        }

        echo $response;
    }

    /**
     * Get cached response
     */
    private function getCachedResponse()
    {
        $cacheKey = $this->generateCacheKey();
        
        return $this->cache->get($cacheKey);
    }

    /**
     * Cache response
     */
    private function cacheResponse($response)
    {
        $cacheKey = $this->generateCacheKey();
        $ttl = $this->getCacheTTL();
        
        $cacheData = [
            'content' => $response,
            'headers' => $this->getResponseHeaders(),
            'timestamp' => time(),
            'ttl' => $ttl
        ];
        
        $this->cache->set($cacheKey, $cacheData, $ttl);
        
        $this->logCacheOperation('store', $cacheKey, $ttl);
    }

    /**
     * Serve cached response
     */
    private function serveCachedResponse($cachedData)
    {
        if (isset($cachedData['headers'])) {
            foreach ($cachedData['headers'] as $header) {
                header($header);
            }
        }
        
        echo $cachedData['content'];
        
        $this->logCacheOperation('hit', $this->generateCacheKey());
        
        exit;
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $query = $_SERVER['QUERY_STRING'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Include user role in cache key for personalized content
        $userRole = $_SESSION['user_role'] ?? 'guest';
        
        $key = 'page_' . md5($uri . $query . $userAgent . $userRole);
        
        return $key;
    }

    /**
     * Get cache TTL
     */
    private function getCacheTTL()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // API endpoints
        if (strpos($uri, '/api/') === 0) {
            $endpoint = $this->getApiEndpoint($uri);
            return $this->config['api']['endpoints'][$endpoint]['ttl'] ?? $this->config['api']['ttl'];
        }
        
        // Forum pages
        if (strpos($uri, '/forum/') === 0) {
            return $this->config['models']['forum']['ttl'];
        }
        
        // Member pages
        if (strpos($uri, '/members/') === 0) {
            return $this->config['models']['member']['ttl'];
        }
        
        // Payment pages
        if (strpos($uri, '/payments/') === 0) {
            return $this->config['models']['payment']['ttl'];
        }
        
        // Default page cache
        return $this->config['pages']['ttl'];
    }

    /**
     * Get API endpoint from URI
     */
    private function getApiEndpoint($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        if (count($segments) >= 3 && $segments[0] === 'api') {
            return $segments[1]; // e.g., 'members', 'payments', 'forum'
        }
        
        return 'default';
    }

    /**
     * Check if route is excluded from caching
     */
    private function isExcludedRoute()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        foreach ($this->config['pages']['excluded_routes'] as $pattern) {
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('/^' . $pattern . '$/', $uri)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if request is cacheable
     */
    private function isCacheable()
    {
        // Don't cache for authenticated users with sensitive data
        if (isset($_SESSION['user_id']) && $this->hasSensitiveData()) {
            return false;
        }
        
        // Don't cache if cache is disabled
        if (!$this->config['pages']['enabled']) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if response should be cached
     */
    private function shouldCacheResponse($response)
    {
        // Don't cache error responses
        if (http_response_code() >= 400) {
            return false;
        }
        
        // Don't cache empty responses
        if (empty(trim($response))) {
            return false;
        }
        
        // Don't cache responses that are too large
        if (strlen($response) > 1024 * 1024) { // 1MB
            return false;
        }
        
        return true;
    }

    /**
     * Check if request has sensitive data
     */
    private function hasSensitiveData()
    {
        $sensitivePatterns = [
            '/admin/',
            '/profile/',
            '/settings/',
            '/logout',
        ];
        
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        foreach ($sensitivePatterns as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get response headers
     */
    private function getResponseHeaders()
    {
        $headers = [];
        
        foreach (headers_list() as $header) {
            if (strpos($header, 'HTTP/') !== 0) {
                $headers[] = $header;
            }
        }
        
        return $headers;
    }

    /**
     * Load cache configuration
     */
    private function loadConfig()
    {
        $configFile = __DIR__ . '/../../config/cache.php';
        
        if (file_exists($configFile)) {
            return require $configFile;
        }
        
        return [
            'pages' => ['enabled' => false, 'ttl' => 3600],
            'api' => ['enabled' => true, 'ttl' => 600],
            'models' => [
                'member' => ['ttl' => 1800],
                'payment' => ['ttl' => 900],
                'forum' => ['ttl' => 1200],
            ],
        ];
    }

    /**
     * Log cache operations
     */
    private function logCacheOperation($operation, $key = null, $ttl = null)
    {
        if ($this->config['monitoring']['enabled'] ?? false) {
            $monitoring = \App\Helpers\Monitoring::getInstance();
            $monitoring->logInfo("Cache {$operation}", [
                'key' => $key,
                'ttl' => $ttl,
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            ]);
        }
    }
} 