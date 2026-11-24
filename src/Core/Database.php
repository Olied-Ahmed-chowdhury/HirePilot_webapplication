<?php
/**
 * HirePilot AI — PDO Database Singleton
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Usage:
 *   $db  = Database::getInstance();
 *   $pdo = $db->getConnection();
 *   $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
 *   $stmt->execute([':id' => 1]);
 */

declare(strict_types=1);

namespace HirePilot\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    // ── Private constructor (singleton) ───────────────────────────────────────
    private function __construct()
    {
        $host    = config('db.host');
        $port    = config('db.port');
        $name    = config('db.name');
        $user    = config('db.user');
        $pass    = config('db.pass');
        $charset = config('db.charset');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // In production, never expose credentials in messages
            if (config('app.debug')) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
            throw new \RuntimeException('Database connection failed. Please check your configuration.');
        }
    }

    // ── Prevent cloning / unserialization ─────────────────────────────────────
    private function __clone(): void {}
    public function __wakeup(): never
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }

    // ── Get singleton instance ────────────────────────────────────────────────
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    // ── Expose raw PDO ────────────────────────────────────────────────────────
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // ── Convenience wrappers ──────────────────────────────────────────────────

    /**
     * Prepare a statement.
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Prepare + execute in one step, returns PDOStatement.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row as associative array (or null).
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Fetch all rows as array of associative arrays.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute INSERT/UPDATE/DELETE, return affected row count.
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Return last inserted auto-increment ID.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back the current transaction.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Check if a transaction is active.
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}

