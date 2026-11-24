<?php
/**
 * HirePilot AI — User Model
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Find by ID ────────────────────────────────────────────────────────────
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = :id AND is_active = 1 LIMIT 1",
            [':id' => $id]
        );
    }

    // ── Find by Email ─────────────────────────────────────────────────────────
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            [':email' => strtolower(trim($email))]
        );
    }

    // ── Find by Google ID ─────────────────────────────────────────────────────
    public function findByGoogleId(string $googleId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE google_id = :gid AND is_active = 1 LIMIT 1",
            [':gid' => $googleId]
        );
    }

    // ── Create a new user ─────────────────────────────────────────────────────
    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO users
                (name, email, password_hash, role, google_id, google_token,
                 avatar, headline, email_verified, is_active, created_at)
             VALUES
                (:name, :email, :password_hash, :role, :google_id, :google_token,
                 :avatar, :headline, :email_verified, 1, NOW())",
            [
                ':name'           => trim($data['name']),
                ':email'          => strtolower(trim($data['email'])),
                ':password_hash'  => $data['password_hash'] ?? null,
                ':role'           => $data['role'] ?? 'user',
                ':google_id'      => $data['google_id'] ?? null,
                ':google_token'   => $data['google_token'] ?? null,
                ':avatar'         => $data['avatar'] ?? null,
                ':headline'       => $data['headline'] ?? null,
                ':email_verified' => isset($data['google_id']) ? 1 : 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    // ── Update profile ────────────────────────────────────────────────────────
    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['name','headline','bio','phone','location','remote_pref',
                    'target_role','experience_yrs','skills','linkedin_url',
                    'github_url','portfolio_url','avatar'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]          = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->db->execute($sql, $params) > 0;
    }

    // ── Update Google tokens ──────────────────────────────────────────────────
    public function updateGoogleTokens(int $id, string $googleId, ?string $token): bool
    {
        return $this->db->execute(
            "UPDATE users SET google_id = :gid, google_token = :token, email_verified = 1 WHERE id = :id",
            [':gid' => $googleId, ':token' => $token, ':id' => $id]
        ) > 0;
    }

    // ── Update password ───────────────────────────────────────────────────────
    public function updatePassword(int $id, string $newHash): bool
    {
        return $this->db->execute(
            "UPDATE users SET password_hash = :hash WHERE id = :id",
            [':hash' => $newHash, ':id' => $id]
        ) > 0;
    }

    // ── Update last login timestamp ───────────────────────────────────────────
    public function touchLastLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = :id",
            [':id' => $id]
        );
    }

    // ── Get all users (admin) ─────────────────────────────────────────────────
    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->fetchAll(
            "SELECT id, name, email, role, is_active, email_verified, last_login_at, created_at
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            [':limit' => $perPage, ':offset' => $offset]
        );
    }

    // ── Count total users ─────────────────────────────────────────────────────
    public function count(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS c FROM users");
        return (int)($row['c'] ?? 0);
    }

    // ── Toggle active status ──────────────────────────────────────────────────
    public function toggleActive(int $id): bool
    {
        return $this->db->execute(
            "UPDATE users SET is_active = NOT is_active WHERE id = :id",
            [':id' => $id]
        ) > 0;
    }

    // ── Verify password ───────────────────────────────────────────────────────
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    // ── Hash a password ───────────────────────────────────────────────────────
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // ── Sanitize user array for session (remove sensitive fields) ─────────────
    public static function forSession(array $user): array
    {
        unset($user['password_hash'], $user['google_token']);
        // Decode skills JSON
        if (isset($user['skills']) && is_string($user['skills'])) {
            $user['skills'] = json_decode($user['skills'], true) ?? [];
        }
        return $user;
    }
}

