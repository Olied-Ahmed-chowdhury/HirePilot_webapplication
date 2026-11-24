<?php
/**
 * HirePilot AI — Notification Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Models\Notification;

class NotificationController
{
    private Notification $notifModel;

    public function __construct()
    {
        Auth::require();
        $this->notifModel = new Notification();
    }

    // ── GET /notifications/fetch (AJAX) ───────────────────────────────────────
    public function fetch(array $params = []): void
    {
        $userId = Auth::id();
        $unread = $this->notifModel->getUnreadCount($userId);
        $items  = $this->notifModel->getRecentByUser($userId, 10);

        header('Content-Type: application/json');
        echo json_encode([
            'unread'        => $unread,
            'notifications' => $items,
        ]);
        exit;
    }

    // ── POST /notifications/{id}/read (AJAX) ──────────────────────────────────
    public function markRead(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $this->notifModel->markAsRead($id, $userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // ── POST /notifications/read-all (AJAX) ───────────────────────────────────
    public function markAllRead(array $params = []): void
    {
        $userId = Auth::id();
        $this->notifModel->markAllAsRead($userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
