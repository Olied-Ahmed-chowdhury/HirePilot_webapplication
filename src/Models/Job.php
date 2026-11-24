<?php
/**
 * HirePilot AI — Job Model
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

    /**
     * Search and filter jobs with pagination.
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $conditions = ["status = 'active'"];
        $params = [];

        // Keyword search (title, company, description, requirements)
        if (!empty($filters['q'])) {
            $conditions[] = "(title LIKE :q OR company LIKE :q OR description LIKE :q OR location LIKE :q)";
            $params[':q'] = '%' . trim($filters['q']) . '%';
        }

        // Job type
        if (!empty($filters['job_type'])) {
            $conditions[] = "job_type = :job_type";
            $params[':job_type'] = $filters['job_type'];
        }

        // Experience level
        if (!empty($filters['experience_level'])) {
            $conditions[] = "experience_level = :experience_level";
            $params[':experience_level'] = $filters['experience_level'];
        }

        // Remote only
        if (isset($filters['is_remote']) && $filters['is_remote'] !== '') {
            $conditions[] = "is_remote = :is_remote";
            $params[':is_remote'] = (int) $filters['is_remote'];
        }

        // Location
        if (!empty($filters['location'])) {
            $conditions[] = "location LIKE :location";
            $params[':location'] = '%' . trim($filters['location']) . '%';
        }

        $whereClause = implode(' AND ', $conditions);

        // Count total matching
        $countSql = "SELECT COUNT(*) AS total FROM jobs WHERE {$whereClause}";
        $total = (int)($this->db->fetchOne($countSql, $params)['total'] ?? 0);

        // Fetch paginated jobs
        $offset = max(0, ($page - 1) * $perPage);
        $fetchSql = "SELECT * FROM jobs WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";

        $jobs = $this->db->fetchAll($fetchSql, $params);

        return [
            'jobs'        => $jobs,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO jobs
                (created_by, title, company, company_logo, location, is_remote, job_type,
                 experience_level, salary_min, salary_max, salary_currency, description,
                 requirements, benefits, skills_required, tags, application_url, deadline, status, created_at)
             VALUES
                (:created_by, :title, :company, :company_logo, :location, :is_remote, :job_type,
                 :experience_level, :salary_min, :salary_max, :salary_currency, :description,
                 :requirements, :benefits, :skills_required, :tags, :application_url, :deadline, :status, NOW())",
            [
                ':created_by'       => $data['created_by'],
                ':title'            => $data['title'],
                ':company'          => $data['company'],
                ':company_logo'     => $data['company_logo'] ?? null,
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
                ':tags'             => $data['tags'] ?? null,
                ':application_url'  => $data['application_url'] ?? null,
                ':deadline'         => $data['deadline'] ?? null,
                ':status'           => $data['status'] ?? 'active',
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = [
            'title', 'company', 'company_logo', 'location', 'is_remote', 'job_type',
            'experience_level', 'salary_min', 'salary_max', 'salary_currency',
            'description', 'requirements', 'benefits', 'skills_required', 'tags',
            'application_url', 'deadline', 'status'
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
        return (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM jobs")['c'] ?? 0);
    }
}
