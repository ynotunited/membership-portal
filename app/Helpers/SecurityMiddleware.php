<?php

namespace App\Helpers;

/**
 * Acts as a local API Gateway, Web Application Firewall (WAF), and Behavior-based detector.
 */
class SecurityMiddleware
{
    public static function handle()
    {
        $ip = RateLimiter::clientIp();
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // 1. Basic WAF: Block common SQLi / XSS patterns in URL / Query
        $badPatterns = [
            '/(?:\%3C|<)script(?:\%3E|>)/i',    // Basic XSS
            '/UNION(?:\s+|%20)+SELECT/i',       // SQLi
            '/WAITFOR(?:\s+|%20)+DELAY/i',      // SQLi Time
            '/(?:\.\.\/)+/',                    // Path traversal
        ];

        foreach ($badPatterns as $pattern) {
            if (preg_match($pattern, $uri) || preg_match($pattern, urldecode($uri))) {
                self::blockAndLog($ip, 'WAF violation detected');
            }
        }

        // 2. Behavior-based detection (Velocity / Anomaly)
        // If an IP makes more than 300 requests in 5 minutes, block them entirely
        if (!RateLimiter::attempt('global_gateway', $ip, 300, 300)) {
            self::blockAndLog($ip, 'Behavior Anomaly: Excessive global request velocity');
        }

        // Check if IP is permanently blocked in Redis/DB
        if (self::isBlocked($ip)) {
            http_response_code(403);
            die("Access Denied: Your IP has been temporarily blocked due to suspicious activity.");
        }
    }

    private static function blockAndLog($ip, $reason)
    {
        SecurityLogger::apiError('WAF', 403, "IP: $ip - Reason: $reason");
        
        try {
            $redis = new RedisClient($_ENV['REDIS_HOST'] ?? '127.0.0.1');
            $redis->executeCommand(['SETEX', "blocked_ip:{$ip}", "3600", "1"]); // Block for 1 hour
        } catch (\Exception $e) {
            // Log fallback
            error_log("Failed to block IP in Redis: " . $e->getMessage());
        }

        http_response_code(403);
        die("Forbidden");
    }

    private static function isBlocked($ip)
    {
        try {
            $redis = new RedisClient($_ENV['REDIS_HOST'] ?? '127.0.0.1');
            return (bool) $redis->executeCommand(['GET', "blocked_ip:{$ip}"]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
