<?php
/**
 * HirePilot AI — Resume Model (ANSI SQL: MySQL + SQLite)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class Resume
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id, int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM resumes WHERE id = :id AND user_id = :uid LIMIT 1",
            [':id' => $id, ':uid' => $userId]
        );
    }

    public function getAllByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM resumes WHERE user_id = :uid ORDER BY is_primary DESC, created_at DESC",
            [':uid' => $userId]
        );
    }

    public function getPrimary(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM resumes WHERE user_id = :uid AND is_primary = 1 LIMIT 1",
            [':uid' => $userId]
        );
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            if (!empty($data['is_primary'])) {
                $this->clearPrimary((int) $data['user_id']);
            }

            $this->db->execute(
                "INSERT INTO resumes
                    (user_id, filename, original_name, mime_type, file_size, is_primary, label, parsed_text, created_at)
                 VALUES
                    (:user_id, :filename, :original_name, :mime_type, :file_size, :is_primary, :label, :parsed_text, :ca)",
                [
                    ':user_id'       => $data['user_id'],
                    ':filename'      => $data['filename'],
                    ':original_name' => $data['original_name'],
                    ':mime_type'     => $data['mime_type'],
                    ':file_size'     => $data['file_size'],
                    ':is_primary'    => !empty($data['is_primary']) ? 1 : 0,
                    ':label'         => $data['label'] ?? null,
                    ':parsed_text'   => $data['parsed_text'] ?? null,
                    ':ca'            => date('Y-m-d H:i:s'),
                ]
            );

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function setPrimary(int $id, int $userId): bool
    {
        $this->db->beginTransaction();
        try {
            $this->clearPrimary($userId);
            $updated = $this->db->execute(
                "UPDATE resumes SET is_primary = 1 WHERE id = :id AND user_id = :uid",
                [':id' => $id, ':uid' => $userId]
            ) > 0;
            $this->db->commit();
            return $updated;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function clearPrimary(int $userId): void
    {
        $this->db->execute(
            "UPDATE resumes SET is_primary = 0 WHERE user_id = :uid",
            [':uid' => $userId]
        );
    }

    public function delete(int $id, int $userId): bool
    {
        $resume = $this->findById($id, $userId);
        if (!$resume) {
            return false;
        }

        $fullPath = STORAGE_PATH . '/uploads/' . $resume['filename'];
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        return $this->db->execute(
            "DELETE FROM resumes WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }
}
