<?php
/**
 * HirePilot AI — User & Profile Model (ANSI SQL: MySQL + SQLite)
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

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*,
                    p.headline, p.bio, p.phone, p.target_role, p.experience_level,
                    p.experience_yrs, p.skills, p.work_preference, p.preferred_location,
                    p.career_goals, p.ai_inquiry_history
             FROM users u
             LEFT JOIN user_profiles p ON u.id = p.user_id
             WHERE u.id = :id AND u.is_active = 1
             LIMIT 1",
            [':id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*,
                    p.headline, p.bio, p.phone, p.target_role, p.experience_level,
                    p.experience_yrs, p.skills, p.work_preference, p.preferred_location,
                    p.career_goals, p.ai_inquiry_history
             FROM users u
             LEFT JOIN user_profiles p ON u.id = p.user_id
             WHERE u.email = :email
             LIMIT 1",
            [':email' => strtolower(trim($email))]
        );
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();

        try {
            $this->db->execute(
                "INSERT INTO users (name, email, password_hash, role, onboarding_completed, is_active, created_at, updated_at)
                 VALUES (:name, :email, :hash, :role, :onb, 1, :ca, :ua)",
                [
                    ':name'  => trim($data['name']),
                    ':email' => strtolower(trim($data['email'])),
                    ':hash'  => $data['password_hash'],
                    ':role'  => $data['role'] ?? 'user',
                    ':onb'   => (int) ($data['onboarding_completed'] ?? 0),
                    ':ca'    => $now,
                    ':ua'    => $now,
                ]
            );

            $userId = (int) $this->db->lastInsertId();

            $this->db->execute(
                "INSERT INTO user_profiles (user_id, work_preference, skills, updated_at)
                 VALUES (:uid, 'any', '[]', :ua)",
                [':uid' => $userId, ':ua' => $now]
            );

            $this->db->commit();
            return $userId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateProfile(int $id, array $data): bool
    {
        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();

        try {
            if (array_key_exists('name', $data) || array_key_exists('onboarding_completed', $data)) {
                $userSets = ['updated_at = :ua'];
                $userParams = [':id' => $id, ':ua' => $now];

                if (array_key_exists('name', $data)) {
                    $userSets[] = 'name = :name';
                    $userParams[':name'] = trim((string) $data['name']);
                }
                if (array_key_exists('onboarding_completed', $data)) {
                    $userSets[] = 'onboarding_completed = :onb';
                    $userParams[':onb'] = (int) $data['onboarding_completed'];
                }

                $this->db->execute(
                    "UPDATE users SET " . implode(', ', $userSets) . " WHERE id = :id",
                    $userParams
                );
            }

            // Ensure profile row exists
            $exists = $this->db->fetchOne("SELECT user_id FROM user_profiles WHERE user_id = :id", [':id' => $id]);
            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO user_profiles (user_id, work_preference, skills, updated_at) VALUES (:id, 'any', '[]', :ua)",
                    [':id' => $id, ':ua' => $now]
                );
            }

            $profileAllowed = [
                'headline', 'bio', 'phone', 'target_role', 'experience_level',
                'experience_yrs', 'skills', 'work_preference', 'preferred_location',
                'career_goals', 'ai_inquiry_history'
            ];

            $sets = ['updated_at = :ua'];
            $params = [':id' => $id, ':ua' => $now];

            foreach ($profileAllowed as $field) {
                if (array_key_exists($field, $data)) {
                    $sets[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            $this->db->execute(
                "UPDATE user_profiles SET " . implode(', ', $sets) . " WHERE user_id = :id",
                $params
            );

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updatePassword(int $id, string $newHash): bool
    {
        return $this->db->execute(
            "UPDATE users SET password_hash = :hash, updated_at = :ua WHERE id = :id",
            [':hash' => $newHash, ':ua' => date('Y-m-d H:i:s'), ':id' => $id]
        ) > 0;
    }

    public function touchLastLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = :now WHERE id = :id",
            [':now' => date('Y-m-d H:i:s'), ':id' => $id]
        );
    }

    public function toggleActive(int $id): bool
    {
        return $this->db->execute(
            "UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = :ua WHERE id = :id",
            [':ua' => date('Y-m-d H:i:s'), ':id' => $id]
        ) > 0;
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function forSession(array $user): array
    {
        unset($user['password_hash']);
        if (isset($user['skills']) && is_string($user['skills'])) {
            $user['skills'] = json_decode($user['skills'], true) ?? [];
        } elseif (!isset($user['skills'])) {
            $user['skills'] = [];
        }
        return $user;
    }
}
