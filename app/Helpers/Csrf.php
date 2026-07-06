<?php
namespace App\Helpers;

/**
 * CSRF token helper.
 *
 * Security properties:
 * - One token per session (regenerated on each form render keeps it fresh)
 * - Compared with hash_equals() to prevent timing attacks
 * - Token is rotated after each successful validation so a stolen token
 *   from a previous request cannot be replayed.
 */
class Csrf
{
    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Always issue a fresh token on each page render
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $_SESSION['csrf_token'];
    }

    public static function validateToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            \App\Helpers\SecurityLogger::csrfFailure($_SERVER['REQUEST_URI'] ?? 'unknown');
            return false;
        }

        $valid = hash_equals($_SESSION['csrf_token'], $token);

        if ($valid) {
            // Rotate the token after use so it cannot be replayed
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            \App\Helpers\SecurityLogger::csrfFailure($_SERVER['REQUEST_URI'] ?? 'unknown');
        }

        return $valid;
    }
}
