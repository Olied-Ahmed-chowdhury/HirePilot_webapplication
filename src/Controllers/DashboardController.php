<?php
/**
 * HirePilot AI — Dashboard Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Database;
use HirePilot\Services\JobMatchingService;

class DashboardController
{
    private Database $db;

    public function __construct()
    {
        Auth::require();
        $this->db = Database::getInstance();
    }

    // ── GET /dashboard ────────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId = Auth::id();
        $user   = $this->db->fetchOne("SELECT * FROM users WHERE id = :id", [':id' => $userId]);

        // Key Metrics
        $totalApps = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid",
            [':uid' => $userId]
        )['c'] ?? 0);

        $activeApps = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status IN ('applied','under_review','interview_scheduled')",
            [':uid' => $userId]
        )['c'] ?? 0);

        $interviews = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status = 'interview_scheduled'",
            [':uid' => $userId]
        )['c'] ?? 0);

        $aiDraftsCount = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM email_drafts WHERE user_id = :uid",
            [':uid' => $userId]
        )['c'] ?? 0);

        // Recent Applications
        $recentApps = $this->db->fetchAll(
            "SELECT a.*, j.title AS job_title, j.company AS job_company, j.location AS job_location, j.job_type
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             WHERE a.user_id = :uid
             ORDER BY a.updated_at DESC
             LIMIT 5",
            [':uid' => $userId]
        );

        // Recommended / Matching Jobs
        $rawJobs = $this->db->fetchAll(
            "SELECT * FROM jobs WHERE status = 'active' ORDER BY created_at DESC LIMIT 10"
        );

        $matchingService = new JobMatchingService();
        $userSkills = !empty($user['skills']) ? (is_string($user['skills']) ? json_decode($user['skills'], true) : $user['skills']) : [];
        $recommendedJobs = $matchingService->rankJobs($rawJobs, $userSkills ?: [], $user['remote_pref'] ?? 'any');
        $topJobs = array_slice($recommendedJobs, 0, 4);

        $pageTitle = 'Dashboard';
        $navbarTitle = 'Dashboard Overview';

        ob_start();
        require VIEWS_PATH . '/dashboard/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }
}
