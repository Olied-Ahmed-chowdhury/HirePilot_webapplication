<?php
/**
 * HirePilot AI — Application Model
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class Application
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id, int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT a.*, j.title AS job_title, j.company AS job_company, j.location AS job_location,
                    j.job_type, j.is_remote, r.label AS resume_label, r.original_name AS resume_file
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             LEFT JOIN resumes r ON a.resume_id = r.id
             WHERE a.id = :id AND a.user_id = :uid LIMIT 1",
            [':id' => $id, ':uid' => $userId]
        );
    }

    public function getAllByUser(int $userId, ?string $statusFilter = null): array
    {
        $sql = "SELECT a.*, j.title AS job_title, j.company AS job_company, j.location AS job_location,
                       j.job_type, j.is_remote
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                WHERE a.user_id = :uid";
        $params = [':uid' => $userId];

        if (!empty($statusFilter)) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY a.updated_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getCountsByStatus(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM applications WHERE user_id = :uid GROUP BY status",
            [':uid' => $userId]
        );

        $counts = [
            'saved'               => 0,
            'applied'             => 0,
            'under_review'        => 0,
            'interview_scheduled' => 0,
            'offer_received'      => 0,
            'rejected'            => 0,
            'withdrawn'           => 0,
        ];

        foreach ($rows as $r) {
            $counts[$r['status']] = (int) $r['cnt'];
        }

        return $counts;
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO applications
                (user_id, job_id, resume_id, email_draft_id, status, cover_letter, custom_notes, applied_at, created_at)
             VALUES
                (:user_id, :job_id, :resume_id, :email_draft_id, :status, :cover_letter, :custom_notes, :applied_at, NOW())
             ON DUPLICATE KEY UPDATE
                resume_id = VALUES(resume_id),
                status = VALUES(status),
                cover_letter = VALUES(cover_letter),
                custom_notes = VALUES(custom_notes),
                updated_at = NOW()",
            [
                ':user_id'        => $data['user_id'],
                ':job_id'         => $data['job_id'],
                ':resume_id'      => $data['resume_id'] ?? null,
                ':email_draft_id' => $data['email_draft_id'] ?? null,
                ':status'         => $data['status'] ?? 'saved',
                ':cover_letter'   => $data['cover_letter'] ?? null,
                ':custom_notes'   => $data['custom_notes'] ?? null,
                ':applied_at'     => ($data['status'] ?? '') === 'applied' ? date('Y-m-d H:i:s') : null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        $params = [':id' => $id, ':uid' => $userId, ':status' => $status];
        $appliedClause = '';

        if ($status === 'applied') {
            $appliedClause = ", applied_at = COALESCE(applied_at, NOW())";
        }

        $sql = "UPDATE applications SET status = :status {$appliedClause}, updated_at = NOW() WHERE id = :id AND user_id = :uid";
        return $this->db->execute($sql, $params) > 0;
    }

    public function updateDetails(int $id, int $userId, array $data): bool
    {
        $fields = ['updated_at = NOW()'];
        $params = [':id' => $id, ':uid' => $userId];

        $allowed = ['status', 'custom_notes', 'company_response', 'interview_date', 'offer_amount', 'resume_id'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }

        $sql = "UPDATE applications SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :uid";
        return $this->db->execute($sql, $params) > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->db->execute(
            "DELETE FROM applications WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function count(): int
    {
        return (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM applications")['c'] ?? 0);
    }
}
