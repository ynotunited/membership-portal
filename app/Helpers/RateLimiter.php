<?php

namespace App\Helpers;

use App\Models\Database;

/**
 * Sliding-window rate limiter backed by the rate_limit_attempts table.
 *
 * Design notes:
 * - Keys are (action, key) pairs. The key is typically an IP address, but can
 *   be any string — e.g. a user_id for per-account limits.
 * - Cleanup only removes records older than the window to avoid removing
 *   records that are still inside the window.
 * - All public helpers return the right response type for both HTML pages
 *   (redirect + flash) and JSON API endpoints (429 JSON body).
 */
class RateLimiter
{
    // -------------------------------------------------------------------------
    // Core primitives
    // -------------------------------------------------------------------------

    /**
     * Record an attempt and return whether it is allowed.
     *
     * @param string $action        e.g. 'login', 'register', 'ai_chat'
     * @param string $key           identifier — IP address or user_id
     * @param int    $maxAttempts   max requests allowed in the window
     * @param int    $windowSeconds sliding window length in seconds
     * @return bool  true = allowed, false = blocked
     */
    public static function attempt(
        string $action,
        string $key,
        int $maxAttempts = 5,
        int $windowSeconds = 900
    ): bool {
        // Try Distributed Redis Rate Limiting First
        try {
            $redis = new RedisClient($_ENV['REDIS_HOST'] ?? '127.0.0.1', $_ENV['REDIS_PORT'] ?? 6379, 1.0);
            $redisKey = "ratelimit:{$action}:{$key}";
            $now = microtime(true);
            $windowStart = $now - $windowSeconds;
            
            $redis->zremrangebyscore($redisKey, 0, $windowStart);
            $count = $redis->zcard($redisKey);
            
            if ($count >= $maxAttempts) {
                return false;
            }
            
            $redis->zadd($redisKey, $now, $now);
            $redis->expire($redisKey, $windowSeconds);
            return true;
        } catch (\Exception $e) {
            // Fallback to MySQL if Redis is unavailable
            $db = Database::getInstance()->getConnection();
            $windowSeconds = (int) $windowSeconds;
            $windowInterval = "INTERVAL {$windowSeconds} SECOND";

            $db->prepare(
                "DELETE FROM rate_limit_attempts
                 WHERE action = :action AND rate_key = :key
                 AND attempted_at < NOW() - {$windowInterval}"
            )->execute([':action' => $action, ':key' => $key]);

            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM rate_limit_attempts
                 WHERE action = :action AND rate_key = :key
                 AND attempted_at >= NOW() - {$windowInterval}"
            );
            $stmt->execute([':action' => $action, ':key' => $key]);
            $count = (int) $stmt->fetchColumn();

            if ($count >= $maxAttempts) {
                return false; 
            }

            $db->prepare(
                "INSERT INTO rate_limit_attempts (action, rate_key, attempted_at)
                 VALUES (:action, :key, NOW())"
            )->execute([':action' => $action, ':key' => $key]);

            return true;
        }
    }

    /**
     * Clear all attempts for a (action, key) pair — call after a successful
     * authentication so the counter resets.
     */
    public static function clear(string $action, string $key): void
    {
        try {
            $redis = new RedisClient($_ENV['REDIS_HOST'] ?? '127.0.0.1', $_ENV['REDIS_PORT'] ?? 6379, 1.0);
            $redis->del("ratelimit:{$action}:{$key}");
        } catch (\Exception $e) {
            // Ignore Redis error, fall back to DB
        }

        Database::getInstance()->getConnection()
            ->prepare("DELETE FROM rate_limit_attempts WHERE action = :action AND rate_key = :key")
            ->execute([':action' => $action, ':key' => $key]);
    }

    /**
     * Seconds until the oldest in-window attempt falls out of the window.
     * Returns 0 when not currently blocked.
     */
    public static function secondsUntilUnlocked(
        string $action,
        string $key,
        int $windowSeconds = 900
    ): int {
        try {
            $redis = new RedisClient($_ENV['REDIS_HOST'] ?? '127.0.0.1', $_ENV['REDIS_PORT'] ?? 6379, 1.0);
            $redisKey = "ratelimit:{$action}:{$key}";
            
            // Get the oldest record in the window
            $now = microtime(true);
            $windowStart = $now - $windowSeconds;
            
            // Note: ZRANGEBYSCORE isn't implemented in our simple client, so we just return the full window
            // as a simplification for Redis fallback.
            return $windowSeconds;
            
        } catch (\Exception $e) {
            // Fallback to MySQL
            $stmt = Database::getInstance()->getConnection()->prepare(
                "SELECT MIN(attempted_at) FROM rate_limit_attempts
                 WHERE action = :action AND rate_key = :key
                 AND attempted_at >= NOW() - INTERVAL :window SECOND"
            );
            $stmt->execute([':action' => $action, ':key' => $key, ':window' => $windowSeconds]);
            $oldest = $stmt->fetchColumn();

            if (!$oldest) {
                return 0;
            }

            return max(0, strtotime($oldest) + $windowSeconds - time());
        }
    }

    // -------------------------------------------------------------------------
    // Per-user rate limiting (authenticated endpoints)
    // -------------------------------------------------------------------------

    /**
     * Rate-limit by authenticated user_id rather than IP.
     * Falls back to IP if no session exists.
     */
    public static function attemptByUser(
        string $action,
        int $maxAttempts,
        int $windowSeconds
    ): bool {
        $key = isset($_SESSION['user_id'])
            ? 'user_' . $_SESSION['user_id']
            : 'ip_' . (self::clientIp());

        return self::attempt($action, $key, $maxAttempts, $windowSeconds);
    }

    // -------------------------------------------------------------------------
    // Response helpers — enforce a limit and immediately respond if blocked
    // -------------------------------------------------------------------------

    /**
     * For HTML (form) endpoints: if blocked, set flash message and redirect.
     *
     * @param string $action
     * @param string $key
     * @param int    $maxAttempts
     * @param int    $windowSeconds
     * @param string $redirectUrl   Where to send the user when blocked
     * @param string $flashKey      Session key for the error message
     */
    public static function enforceForHtml(
        string $action,
        string $key,
        int    $maxAttempts,
        int    $windowSeconds,
        string $redirectUrl,
        string $flashKey = 'login_error'
    ): void {
        if (!self::attempt($action, $key, $maxAttempts, $windowSeconds)) {
            $wait = (int) ceil(self::secondsUntilUnlocked($action, $key, $windowSeconds) / 60);
            $wait = max(1, $wait);

            SecurityLogger::rateLimitExceeded($action, $key);

            $_SESSION[$flashKey] = "Too many requests. Please try again in {$wait} minute(s).";
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * For JSON/API endpoints: if blocked, emit a 429 JSON response and exit.
     *
     * @param string $action
     * @param string $key
     * @param int    $maxAttempts
     * @param int    $windowSeconds
     */
    public static function enforceForApi(
        string $action,
        string $key,
        int    $maxAttempts,
        int    $windowSeconds
    ): void {
        if (!self::attempt($action, $key, $maxAttempts, $windowSeconds)) {
            $retryAfter = self::secondsUntilUnlocked($action, $key, $windowSeconds);

            SecurityLogger::rateLimitExceeded($action, $key);

            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . $retryAfter);
            echo json_encode([
                'error'       => 'Too many requests.',
                'retry_after' => $retryAfter,
            ]);
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // Convenience: named limits for all app endpoints
    // -------------------------------------------------------------------------

    /**
     * Rate limit definitions per action.
     * Override via .env: RATE_LIMIT_<ACTION>=max:window
     * e.g. RATE_LIMIT_AI_CHAT=10:60
     *
     * Returns [maxAttempts, windowSeconds]
     */
    public static function limitsFor(string $action): array
    {
        $defaults = [
            'login'           => [5,  900],   // 5 / 15 min
            'register'        => [3,  3600],  // 3 / 1 hr  per IP
            'password_reset'  => [3,  3600],  // 3 / 1 hr
            'ai_chat'         => [20, 60],    // 20 / 1 min per user
            'ai_chat_ip'      => [30, 60],    // 30 / 1 min per IP  (unauthenticated fallback)
            'ai_paid_api'     => [20, 3600],  // 20 / 1 hr (Strict limit for paid APIs)
            'forum_new_topic' => [5,  300],   // 5  / 5 min
            'forum_reply'     => [10, 60],    // 10 / 1 min
            'forum_reaction'  => [30, 60],    // 30 / 1 min
            'payment_init'    => [5,  300],   // 5  / 5 min
            'member_export'   => [5,  300],   // 5  / 5 min
            'search'          => [30, 60],    // 30 / 1 min
        ];

        $cfg = $defaults[$action] ?? [60, 60]; // safe fallback

        // Allow env overrides: RATE_LIMIT_AI_CHAT=10:60
        $envKey = 'RATE_LIMIT_' . strtoupper($action);
        $override = getenv($envKey);
        if ($override && preg_match('/^(\d+):(\d+)$/', $override, $m)) {
            $cfg = [(int)$m[1], (int)$m[2]];
        }

        return $cfg;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                return $_SERVER[$h];
            }
        }
        return '0.0.0.0';
    }
}
