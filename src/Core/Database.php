<?php
/**
 * HirePilot AI — Dual-Mode PDO Database Manager (MySQL + SQLite WAL Fallback)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Automatically connects to MySQL when available. If MySQL is stopped,
 * disabled, or unreachable, seamlessly fails over to an embedded SQLite
 * database with WAL mode and busy_timeout for high-traffic concurrency.
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
    private string $driver = 'mysql';

    private function __construct()
    {
        $connected = $this->tryMySql();

        if (!$connected) {
            $this->connectSqlite();
        }

        // Auto-create portable schema & seed data if tables do not exist yet
        SchemaInitializer::ensureInitialized($this);
    }

    private function tryMySql(): bool
    {
        $host    = (string) config('db.host', '127.0.0.1');
        $port    = (int) config('db.port', 3306);
        $name    = (string) config('db.name', 'hirepilot_db');
        $user    = (string) config('db.user', 'root');
        $pass    = (string) config('db.pass', '');
        $charset = (string) config('db.charset', 'utf8mb4');

        if ($host === '' || $name === '' || !extension_loaded('pdo_mysql')) {
            return false;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];

        try {
            // Try connecting directly to the target database
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->driver = 'mysql';
            return true;
        } catch (PDOException $e) {
            // If database itself doesn't exist yet (1049), try creating it
            if ((int) $e->getCode() === 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
                    $tmpPdo = new PDO($serverDsn, $user, $pass, $options);
                    $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
                    $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->pdo = new PDO("mysql:host={$host};port={$port};dbname={$safeName};charset={$charset}", $user, $pass, $options);
                    $this->driver = 'mysql';
                    return true;
                } catch (PDOException) {
                    return false;
                }
            }
            return false;
        }
    }

    private function connectSqlite(): void
    {
        $sqlitePath = (string) config('db.sqlite_path', STORAGE_PATH . '/database/hirepilot.sqlite');
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        // Crash-safe high-traffic concurrency pragmas (SRS 2.9)
        $this->pdo->exec('PRAGMA journal_mode = WAL;');
        $this->pdo->exec('PRAGMA synchronous = NORMAL;');
        $this->pdo->exec('PRAGMA busy_timeout = 5000;');
        $this->pdo->exec('PRAGMA foreign_keys = ON;');

        $this->driver = 'sqlite';
    }

    private function __clone(): void {}
    public function __wakeup(): never
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
