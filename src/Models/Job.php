<?php
/**
 * HirePilot AI — Job Model (ANSI SQL: MySQL + SQLite)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Models;

use HirePilot\Core\Database;

class Job
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT j.*, u.name AS poster_name, u.email AS poster_email
             FROM jobs j
             LEFT JOIN users u ON j.created_by = u.id
             WHERE j.id = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public function incrementViews(int $id): void
    {
        $this->db->execute("UPDATE jobs SET view_count = view_count + 1 WHERE id = :id", [':id' => $id]);
    }

    public function incrementApplications(int $id): void
    {
        $this->db->execute("UPDATE jobs SET apply_count = apply_count + 1 WHERE id = :id", [':id' => $id]);
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $conditions = ["status = 'active'"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(title LIKE :q1 OR company LIKE :q2 OR description LIKE :q3 OR location LIKE :q4 OR skills_required LIKE :q5)";
            $kw = '%' . trim((string) $filters['q']) . '%';
            $params[':q1'] = $kw;
            $params[':q2'] = $kw;
            $params[':q3'] = $kw;
            $params[':q4'] = $kw;
            $params[':q5'] = $kw;
        }

        if (!empty($filters['job_type'])) {
            $conditions[] = "job_type = :job_type";
            $params[':job_type'] = $filters['job_type'];
        }

        if (!empty($filters['experience_level'])) {
            $conditions[] = "experience_level = :experience_level";
            $params[':experience_level'] = $filters['experience_level'];
        }

        if (isset($filters['is_remote']) && $filters['is_remote'] !== '') {
            $conditions[] = "is_remote = :is_remote";
            $params[':is_remote'] = (int) $filters['is_remote'];
        }

        if (!empty($filters['location'])) {
            $conditions[] = "location LIKE :location";
            $params[':location'] = '%' . trim((string) $filters['location']) . '%';
        }

        $whereClause = implode(' AND ', $conditions);

        $countSql = "SELECT COUNT(*) AS total FROM jobs WHERE {$whereClause}";
        $total = (int) ($this->db->fetchOne($countSql, $params)['total'] ?? 0);

        $page    = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset  = ($page - 1) * $perPage;

        $fetchSql = "SELECT * FROM jobs WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $jobs = $this->db->fetchAll($fetchSql, $params);

        return [
            'jobs'        => $jobs,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO jobs
                (created_by, title, company, location, is_remote, job_type,
                 experience_level, salary_min, salary_max, salary_currency, description,
                 requirements, benefits, skills_required, external_url, deadline, status, created_at)
             VALUES
                (:created_by, :title, :company, :location, :is_remote, :job_type,
                 :experience_level, :salary_min, :salary_max, :salary_currency, :description,
                 :requirements, :benefits, :skills_required, :external_url, :deadline, :status, :created_at)",
            [
                ':created_by'       => $data['created_by'],
                ':title'            => $data['title'],
                ':company'          => $data['company'],
                ':location'         => $data['location'] ?? null,
                ':is_remote'        => !empty($data['is_remote']) ? 1 : 0,
                ':job_type'         => $data['job_type'] ?? 'full-time',
                ':experience_level' => $data['experience_level'] ?? 'mid',
                ':salary_min'       => $data['salary_min'] ?? null,
                ':salary_max'       => $data['salary_max'] ?? null,
                ':salary_currency'  => $data['salary_currency'] ?? 'BDT',
                ':description'      => $data['description'],
                ':requirements'     => $data['requirements'] ?? null,
                ':benefits'         => $data['benefits'] ?? null,
                ':skills_required'  => $data['skills_required'] ?? null,
                ':external_url'     => $data['external_url'] ?? null,
                ':deadline'         => $data['deadline'] ?? null,
                ':status'           => $data['status'] ?? 'active',
                ':created_at'       => date('Y-m-d H:i:s'),
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = [
            'title', 'company', 'location', 'is_remote', 'job_type',
            'experience_level', 'salary_min', 'salary_max', 'salary_currency',
            'description', 'requirements', 'benefits', 'skills_required',
            'external_url', 'deadline', 'status'
        ];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE jobs SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->db->execute($sql, $params) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM jobs WHERE id = :id", [':id' => $id]) > 0;
    }

    public function count(): int
    {
        return (int) ($this->db->fetchOne("SELECT COUNT(*) AS c FROM jobs")['c'] ?? 0);
    }

    public function countExternal(): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM jobs WHERE external_url IS NOT NULL AND external_url != ''"
        )['c'] ?? 0);
    }

    /**
     * Insert a live external job if not already present (matched by external_url or title+company).
     *
     * @return array{id:int, inserted:bool}
     */
    public function upsertExternal(array $data): array
    {
        if (!empty($data['external_url'])) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM jobs WHERE external_url = :url LIMIT 1",
                [':url' => $data['external_url']]
            );
            if ($existing) {
                return ['id' => (int) $existing['id'], 'inserted' => false];
            }
        }

        $existingByTitle = $this->db->fetchOne(
            "SELECT id FROM jobs WHERE title = :title AND company = :company LIMIT 1",
            [':title' => $data['title'], ':company' => $data['company']]
        );
        if ($existingByTitle) {
            return ['id' => (int) $existingByTitle['id'], 'inserted' => false];
        }

        $id = $this->create($data);
        return ['id' => $id, 'inserted' => true];
    }
}
