<?php

namespace App\Models;

/**
 * RowPolicy — PHP-layer Row Level Security context manager.
 *
 * Usage in member-facing models:
 *
 *   use App\Models\RowPolicy;
 *
 *   // In a method that must be scoped to the current member:
 *   RowPolicy::requireMemberContext();          // throws if not set
 *   $id = RowPolicy::memberId();               // returns int
 *
 *   // In BaseController / UserDashboardController, after authenticating:
 *   RowPolicy::setMember($_SESSION['user_id']);
 *
 *   // On logout / session destroy:
 *   RowPolicy::clear();
 *
 * The trait also synchronises @app_member_id on the MySQL connection so
 * the eight rls_* views enforce the same boundary at the database level.
 */
class RowPolicy
{
    /** The authenticated member's ID for this request. */
    private static ?int $memberId = null;

    /** Whether the DB session variable has been pushed for this connection. */
    private static bool $dbSynced = false;

    // -------------------------------------------------------------------------
    // Context setters
    // -------------------------------------------------------------------------

    /**
     * Set the active member context.
     * Call this immediately after a successful member login or session restore.
     *
     * @param int $memberId  members.id of the authenticated user
     */
    public static function setMember(int $memberId): void
    {
        self::$memberId = $memberId;
        self::$dbSynced = false; // Force re-push on next DB access
        self::syncToDb();
    }

    /**
     * Clear the member context (on logout or session expiry).
     */
    public static function clear(): void
    {
        self::$memberId = null;
        self::$dbSynced = false;
        self::pushToDb(null);
    }

    // -------------------------------------------------------------------------
    // Context accessors
    // -------------------------------------------------------------------------

    /**
     * Return the active member ID, or null if not set.
     */
    public static function memberId(): ?int
    {
        return self::$memberId;
    }

    /**
     * Assert that a member context is active.
     * Throws if called without a session — prevents accidental unscoped queries.
     *
     * @throws \RuntimeException
     */
    public static function requireMemberContext(): int
    {
        if (self::$memberId === null) {
            throw new \RuntimeException(
                'RowPolicy: member context is not set. ' .
                'Call RowPolicy::setMember() after authentication before running member-scoped queries.'
            );
        }
        return self::$memberId;
    }

    /**
     * Return true only when the given $ownerId matches the active member.
     * Use this to perform an ownership assertion before any UPDATE or DELETE.
     *
     * @throws \RuntimeException  if context not set
     * @throws \DomainException   if IDs do not match
     */
    public static function assertOwns(int $ownerId, string $resource = 'record'): void
    {
        $current = self::requireMemberContext();
        if ($current !== $ownerId) {
            \App\Helpers\SecurityLogger::idorAttempt($resource, $current, $ownerId);
            throw new \DomainException(
                "RowPolicy: member {$current} attempted to access {$resource} owned by {$ownerId}."
            );
        }
    }

    // -------------------------------------------------------------------------
    // DB synchronisation
    // -------------------------------------------------------------------------

    /**
     * Push the current member ID to the MySQL connection as @app_member_id.
     * Called automatically by setMember() and clear().
     * Also called lazily by syncToDb() before the first query on a new connection.
     */
    private static function syncToDb(): void
    {
        if (self::$dbSynced) {
            return;
        }
        self::pushToDb(self::$memberId);
        self::$dbSynced = true;
    }

    private static function pushToDb(?int $id): void
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SET @app_member_id = :mid');
            $stmt->execute([':mid' => $id]);
        } catch (\Throwable $e) {
            // Log but do not crash — the PHP-layer checks are still active
            error_log('RowPolicy::pushToDb failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the DB session variable is in sync.
     * Call this at the start of any member-scoped model method that queries
     * an rls_* view, to guard against connection pool re-use where the
     * previous connection had a different @app_member_id.
     */
    public static function ensureDbSync(): void
    {
        if (!self::$dbSynced) {
            self::syncToDb();
        }
    }
}
