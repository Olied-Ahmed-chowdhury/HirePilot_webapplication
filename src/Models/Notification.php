<?php
/**
 * HirePilot AI — Notification Model
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class Notification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO notifications
                (user_id, type, title, message, link, reference_id, reference_type, is_read, created_at)
             VALUES
                (:user_id, :type, :title, :message, :link, :reference_id, :reference_type, 0, NOW())",
            [
                ':user_id'        => $data['user_id'],
                ':type'           => $data['type'],
                ':title'          => $data['title'],
                ':message'        => $data['message'],
                ':link'           => $data['link'] ?? null,
                ':reference_id'   => $data['reference_id'] ?? null,
                ':reference_type' => $data['reference_type'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function getRecentByUser(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim",
            [':uid' => $userId, ':lim' => $limit]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM notifications WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        );

        return (int)($row['c'] ?? 0);
    }

    public function markAsRead(int $id, int $userId): bool
    {
        return $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        ) > 0;
    }
}
