<?php

namespace App\Helpers;

/**
 * SecurityLogger
 *
 * Centralised, always-available security event logger.
 * Writes to both a flat log file (never web-accessible) and the DB audit table.
 * Designed to be dependency-free so it can be called very early in the bootstrap
 * before models or sessions are fully initialised.
 *
 * Log levels used:
 *   INFO    – normal but noteworthy events (successful login, logout)
 *   WARNING – suspicious but not yet confirmed malicious (failed login)
 *   ALERT   – confirmed policy violation (IDOR attempt, rate-limit breach)
 *   CRITICAL– attack pattern or system integrity concern
 */
class SecurityLogger
{
    /** Absolute path to the log directory (outside public webroot). */
    private static string $logDir = '';

    /** Cached PDO connection – lazy-loaded. */
    private static ?\PDO $db = null;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /** Successful login. */
    public static function loginSuccess(string $identifier, string $role): void
    {
        self::write('INFO', 'auth.login_success', [
            'identifier' => self::mask($identifier),
            'role'       => $role,
        ]);
        self::dbLog('login_success', $identifier, $role);
    }

    /** Failed login attempt. */
    public static function loginFailure(string $identifier, string $reason = ''): void
    {
        self::write('WARNING', 'auth.login_failure', [
            'identifier' => self::mask($identifier),
            'reason'     => $reason,
        ]);
        self::dbLog('login_failure', $identifier);
    }

    /** Logout. */
    public static function logout(string $identifier): void
    {
        self::write('INFO', 'auth.logout', [
            'identifier' => self::mask($identifier),
        ]);
        self::dbLog('logout', $identifier);
    }

    /** Password reset requested. */
    public static function passwordResetRequested(string $email): void
    {
        self::write('INFO', 'auth.password_reset_requested', [
            'email' => self::mask($email),
        ]);
    }

    /** Password changed successfully. */
    public static function passwordChanged(int $userId): void
    {
        self::write('INFO', 'auth.password_changed', ['user_id' => $userId]);
        self::dbLog('password_changed', (string)$userId);
    }

    /** Rate-limit hit. */
    public static function rateLimitExceeded(string $action, string $key): void
    {
        self::write('ALERT', 'rate_limit.exceeded', [
            'action' => $action,
            'key'    => self::mask($key),
        ]);
        self::dbLog('rate_limit_exceeded', $key);
    }

    /** IDOR attempt detected. */
    public static function idorAttempt(string $resource, $ownerId, $requestedId): void
    {
        self::write('ALERT', 'idor.attempt', [
            'resource'     => $resource,
            'owner_id'     => $ownerId,
            'requested_id' => $requestedId,
        ]);
        self::dbLog('idor_attempt', "resource={$resource} owner={$ownerId} requested={$requestedId}");
    }

    /** CSRF validation failed. */
    public static function csrfFailure(string $uri): void
    {
        self::write('ALERT', 'csrf.failure', ['uri' => $uri]);
        self::dbLog('csrf_failure', $uri);
    }

    /** External API error (payment gateway, AI, etc.). */
    public static function apiError(string $service, int $httpCode, string $detail = ''): void
    {
        self::write('WARNING', 'api.error', [
            'service'   => $service,
            'http_code' => $httpCode,
            'detail'    => substr($detail, 0, 300), // never log full responses
        ]);
    }

    /** Unusual traffic – high request volume, scanner signatures, etc. */
    public static function unusualTraffic(string $reason, array $context = []): void
    {
        self::write('WARNING', 'traffic.unusual', array_merge(
            ['reason' => $reason],
            $context
        ));
        self::dbLog('unusual_traffic', $reason);
    }

    /** Generic security event for anything that doesn't fit the above. */
    public static function event(
        string $level,
        string $event,
        array  $context = []
    ): void {
        self::write($level, $event, $context);
        self::dbLog($event, json_encode($context));
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Write one line to the daily security log file.
     * Format: ISO-timestamp TAB level TAB event TAB JSON-context TAB ip TAB user_agent
     */
    private static function write(string $level, string $event, array $context): void
    {
        $dir  = self::getLogDir();
        $file = $dir . '/security_' . date('Y-m-d') . '.log';

        $line = implode("\t", [
            date('Y-m-d H:i:s'),
            $level,
            $event,
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            self::ip(),
            self::userAgent(),
        ]) . PHP_EOL;

        // FILE_APPEND + LOCK_EX is safe for concurrent writes
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Write to the audit_logs table if DB is available.
     * Silently swallows any DB error — logging must never break the request.
     */
    private static function dbLog(string $action, string $detail = ''): void
    {
        try {
            $db = self::getDb();
            if (!$db) {
                return;
            }

            $stmt = $db->prepare(
                "INSERT INTO audit_logs
                    (user_id, action, details, ip_address, user_agent, request_uri, request_method, created_at)
                 VALUES
                    (:uid, :action, :details, :ip, :ua, :uri, :method, NOW())"
            );
            $stmt->execute([
                ':uid'     => $_SESSION['user_id'] ?? null,
                ':action'  => $action,
                ':details' => $detail,
                ':ip'      => self::ip(),
                ':ua'      => self::userAgent(),
                ':uri'     => $_SERVER['REQUEST_URI'] ?? '',
                ':method'  => $_SERVER['REQUEST_METHOD'] ?? '',
            ]);
        } catch (\Throwable $e) {
            // DB unavailable – the file log is still intact; don't throw
            error_log('SecurityLogger::dbLog failed: ' . $e->getMessage());
        }
    }

    /** Lazy-load PDO so the logger works before the app is fully bootstrapped. */
    private static function getDb(): ?\PDO
    {
        if (self::$db !== null) {
            return self::$db;
        }
        try {
            $host   = getenv('DB_HOST')     ?: '127.0.0.1';
            $dbname = getenv('DB_DATABASE') ?: '';
            $user   = getenv('DB_USERNAME') ?: 'root';
            $pass   = getenv('DB_PASSWORD') ?: '';

            self::$db = new \PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Throwable $e) {
            error_log('SecurityLogger: DB connection failed: ' . $e->getMessage());
            self::$db = null;
        }
        return self::$db;
    }

    private static function getLogDir(): string
    {
        if (self::$logDir === '') {
            // Two levels above public/ → project root, then logs/
            self::$logDir = dirname(__DIR__, 2) . '/logs/security';
        }
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0750, true);

            // Drop an .htaccess inside in case this ever ends up under a webroot
            file_put_contents(
                self::$logDir . '/.htaccess',
                "Order allow,deny\nDeny from all\n"
            );
        }
        return self::$logDir;
    }

    /** Client IP, respecting common reverse-proxy headers. */
    private static function ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        return 'unknown';
    }

    private static function userAgent(): string
    {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 250);
    }

    /**
     * Mask PII — show only first 3 chars + asterisks.
     * e.g. "user@example.com" → "use*************"
     */
    private static function mask(string $value): string
    {
        $len = mb_strlen($value);
        if ($len <= 3) {
            return str_repeat('*', $len);
        }
        return mb_substr($value, 0, 3) . str_repeat('*', $len - 3);
    }
}
