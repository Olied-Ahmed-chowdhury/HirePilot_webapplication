<?php
/**
 * HirePilot AI — Email Draft Model (ANSI SQL: MySQL + SQLite)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class EmailDraft
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id, int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT d.*, j.title AS job_title, j.company AS job_company, r.label AS resume_label
             FROM email_drafts d
             LEFT JOIN jobs j ON d.job_id = j.id
             LEFT JOIN resumes r ON d.resume_id = r.id
             WHERE d.id = :id AND d.user_id = :uid LIMIT 1",
            [':id' => $id, ':uid' => $userId]
        );
    }

    public function getAllByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT d.*, j.title AS job_title, j.company AS job_company
             FROM email_drafts d
             LEFT JOIN jobs j ON d.job_id = j.id
             WHERE d.user_id = :uid
             ORDER BY d.updated_at DESC",
            [':uid' => $userId]
        );
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->execute(
            "INSERT INTO email_drafts
                (user_id, application_id, job_id, resume_id, draft_type, subject, body,
                 ai_model_used, status, recipient_email, created_at, updated_at)
             VALUES
                (:user_id, :application_id, :job_id, :resume_id, :draft_type, :subject, :body,
                 :ai_model_used, :status, :recipient_email, :ca, :ua)",
            [
                ':user_id'         => $data['user_id'],
                ':application_id'  => $data['application_id'] ?? null,
                ':job_id'          => $data['job_id'] ?? null,
                ':resume_id'       => $data['resume_id'] ?? null,
                ':draft_type'      => $data['draft_type'] ?? 'cover_letter',
                ':subject'         => $data['subject'] ?? null,
                ':body'            => $data['body'],
                ':ai_model_used'   => $data['ai_model_used'] ?? null,
                ':status'          => $data['status'] ?? 'draft',
                ':recipient_email' => $data['recipient_email'] ?? null,
                ':ca'              => $now,
                ':ua'              => $now,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $fields = ['updated_at = :ua'];
        $params = [':id' => $id, ':uid' => $userId, ':ua' => date('Y-m-d H:i:s')];

        $allowed = ['subject', 'body', 'status', 'recipient_email', 'sent_at', 'send_log'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }

        $sql = "UPDATE email_drafts SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :uid";
        return $this->db->execute($sql, $params) > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->db->execute(
            "DELETE FROM email_drafts WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function markSent(int $id, int $userId, string $recipient, string $logMsg): bool
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->execute(
            "UPDATE email_drafts
             SET status = 'sent', recipient_email = :rec, sent_at = :sa, send_log = :log, updated_at = :ua
             WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId, ':rec' => $recipient, ':sa' => $now, ':log' => $logMsg, ':ua' => $now]
        ) > 0;
    }
}
