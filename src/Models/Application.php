<?php
/**
 * HirePilot AI — Application Model (ANSI SQL: MySQL + SQLite)
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
            "SELECT a.*,
                    COALESCE(j.title, a.job_title) AS job_title,
                    COALESCE(j.company, a.company_name) AS job_company,
                    j.location AS job_location,
                    j.job_type,
                    j.is_remote,
                    j.external_url,
                    r.label AS resume_label,
                    r.original_name AS resume_file
             FROM applications a
             LEFT JOIN jobs j ON a.job_id = j.id
             LEFT JOIN resumes r ON a.resume_id = r.id
             WHERE a.id = :id AND a.user_id = :uid LIMIT 1",
            [':id' => $id, ':uid' => $userId]
        );
    }

    public function getAllByUser(int $userId, ?string $statusFilter = null): array
    {
        $sql = "SELECT a.*,
                       COALESCE(j.title, a.job_title) AS job_title,
                       COALESCE(j.company, a.company_name) AS job_company,
                       j.location AS job_location,
                       j.job_type,
                       j.is_remote
                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
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
        $now = date('Y-m-d H:i:s');

        // Portable duplicate check for user + job
        if (!empty($data['job_id'])) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM applications WHERE user_id = :uid AND job_id = :jid LIMIT 1",
                [':uid' => $data['user_id'], ':jid' => $data['job_id']]
            );
            if ($existing) {
                $this->updateDetails((int) $existing['id'], (int) $data['user_id'], [
                    'resume_id'    => $data['resume_id'] ?? null,
                    'status'       => $data['status'] ?? 'applied',
                    'cover_letter' => $data['cover_letter'] ?? null,
                    'custom_notes' => $data['custom_notes'] ?? null,
                ]);
                return (int) $existing['id'];
            }
        }

        $this->db->execute(
            "INSERT INTO applications
                (user_id, job_id, resume_id, company_name, job_title, status, cover_letter, custom_notes, applied_at, created_at, updated_at)
             VALUES
                (:user_id, :job_id, :resume_id, :company_name, :job_title, :status, :cover_letter, :custom_notes, :applied_at, :ca, :ua)",
            [
                ':user_id'      => $data['user_id'],
                ':job_id'       => $data['job_id'] ?? null,
                ':resume_id'    => $data['resume_id'] ?? null,
                ':company_name' => $data['company_name'] ?? null,
                ':job_title'    => $data['job_title'] ?? null,
                ':status'       => $data['status'] ?? 'saved',
                ':cover_letter' => $data['cover_letter'] ?? null,
                ':custom_notes' => $data['custom_notes'] ?? null,
                ':applied_at'   => ($data['status'] ?? '') === 'applied' ? $now : null,
                ':ca'           => $now,
                ':ua'           => $now,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        $now = date('Y-m-d H:i:s');
        $params = [':id' => $id, ':uid' => $userId, ':status' => $status, ':ua' => $now];
        $appliedClause = '';

        if ($status === 'applied') {
            $appliedClause = ", applied_at = COALESCE(applied_at, :aa)";
            $params[':aa'] = $now;
        }

        $sql = "UPDATE applications SET status = :status {$appliedClause}, updated_at = :ua WHERE id = :id AND user_id = :uid";
        return $this->db->execute($sql, $params) > 0;
    }

    public function updateDetails(int $id, int $userId, array $data): bool
    {
        $fields = ['updated_at = :ua'];
        $params = [':id' => $id, ':uid' => $userId, ':ua' => date('Y-m-d H:i:s')];

        $allowed = ['status', 'cover_letter', 'custom_notes', 'company_response', 'interview_date', 'offer_amount', 'resume_id', 'company_name', 'job_title'];
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
        return (int) ($this->db->fetchOne("SELECT COUNT(*) AS c FROM applications")['c'] ?? 0);
    }
}
