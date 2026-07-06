<?php
namespace App\Models;

/**
 * Password reset token model.
 *
 * Security:
 * - Only a SHA-256 hash of the token is persisted in the database.
 *   The raw token is only ever held in the URL / caller's memory.
 * - Tokens expire after 1 hour (enforced in both createResetToken and getResetToken).
 * - Old tokens for the same email are purged on each new request.
 */
class PasswordResetModel extends BaseModel
{
    /** Token lifetime in seconds (1 hour). */
    private const TTL = 3600;

    /**
     * Create a new reset token entry.
     * Deletes any existing token for the same email first.
     *
     * @param string $email  The account email address
     * @param string $token  Raw (unhashed) token to store as a hash
     */
    public function createResetToken(string $email, string $token): bool
    {
        $db = $this->getConnection();

        // Remove any stale token for this email
        $db->prepare('DELETE FROM password_resets WHERE email = :email')
           ->execute(['email' => $email]);

        $hashedToken = hash('sha256', $token);
        $expiresAt   = date('Y-m-d H:i:s', time() + self::TTL);

        $stmt = $db->prepare(
            'INSERT INTO password_resets (email, token, expires_at, created_at)
             VALUES (:email, :token, :expires_at, NOW())'
        );
        return $stmt->execute([
            'email'      => $email,
            'token'      => $hashedToken,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Look up a reset record by raw token.
     * Returns false if the token does not exist or has expired.
     *
     * @param string $token Raw (unhashed) token from the reset link
     * @return array|false
     */
    public function getResetToken(string $token)
    {
        $hashedToken = hash('sha256', $token);

        $stmt = $this->getConnection()->prepare(
            'SELECT * FROM password_resets
             WHERE token = :token
               AND expires_at > NOW()'
        );
        $stmt->execute(['token' => $hashedToken]);
        return $stmt->fetch();
    }

    /**
     * Delete a token after it has been used.
     *
     * @param string $token Raw (unhashed) token
     */
    public function deleteResetToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);

        $stmt = $this->getConnection()->prepare(
            'DELETE FROM password_resets WHERE token = :token'
        );
        return $stmt->execute(['token' => $hashedToken]);
    }

    /**
     * Purge all expired tokens (useful to call from a cron job).
     */
    public function purgeExpired(): void
    {
        $this->getConnection()
             ->prepare('DELETE FROM password_resets WHERE expires_at <= NOW()')
             ->execute();
    }
}
