<?php
/**
 * HirePilot AI — Dashboard Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Database;
use HirePilot\Models\User;
use HirePilot\Services\JobMatchingService;

class DashboardController
{
    private Database $db;

    public function __construct()
    {
        Auth::require();
        $this->db = Database::getInstance();
    }

    public function index(array $params = []): void
    {
        $userId    = Auth::id();
        $userModel = new User();
        $user      = $userModel->findById($userId);

        $totalApps = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid",
            [':uid' => $userId]
        )['c'] ?? 0);

        $activeApps = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status IN ('applied','under_review','interview_scheduled')",
            [':uid' => $userId]
        )['c'] ?? 0);

        $interviews = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status = 'interview_scheduled'",
            [':uid' => $userId]
        )['c'] ?? 0);

        $aiDraftsCount = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM email_drafts WHERE user_id = :uid",
            [':uid' => $userId]
        )['c'] ?? 0);

        $recentApps = $this->db->fetchAll(
            "SELECT a.*,
                    COALESCE(j.title, a.job_title) AS job_title,
                    COALESCE(j.company, a.company_name) AS job_company,
                    j.location AS job_location,
                    j.job_type
             FROM applications a
             LEFT JOIN jobs j ON a.job_id = j.id
             WHERE a.user_id = :uid
             ORDER BY a.updated_at DESC
             LIMIT 5",
            [':uid' => $userId]
        );

        $rawJobs = $this->db->fetchAll(
            "SELECT * FROM jobs WHERE status = 'active' ORDER BY created_at DESC LIMIT 12"
        );

        $matchingService = new JobMatchingService();
        $userSkills      = !empty($user['skills'])
            ? (is_string($user['skills']) ? (json_decode($user['skills'], true) ?? []) : $user['skills'])
            : [];

        $recommendedJobs = $matchingService->rankJobs(
            $rawJobs,
            $userSkills,
            $user['work_preference'] ?? 'any'
        );
        $topJobs = array_slice($recommendedJobs, 0, 4);

        $dbDriver    = strtoupper($this->db->getDriver());
        $pageTitle   = 'Dashboard';
        $navbarTitle = 'Workspace Overview';

        ob_start();
        require VIEWS_PATH . '/dashboard/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }
}
