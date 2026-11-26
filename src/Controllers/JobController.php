<?php
/**
 * HirePilot AI — Job Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\Job;
use HirePilot\Models\Resume;
use HirePilot\Models\User;
use HirePilot\Services\JobMatchingService;
use HirePilot\Core\Database;

class JobController
{
    private Job $jobModel;
    private JobMatchingService $matchingService;

    public function __construct()
    {
        Auth::require();
        $this->jobModel = new Job();
        $this->matchingService = new JobMatchingService();
    }

    // ── GET /jobs ─────────────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId = Auth::id();
        $userModel = new User();
        $user = $userModel->findById($userId);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'q'                => $_GET['q'] ?? '',
            'job_type'         => $_GET['job_type'] ?? '',
            'experience_level' => $_GET['experience_level'] ?? '',
            'is_remote'        => $_GET['is_remote'] ?? '',
            'location'         => $_GET['location'] ?? '',
        ];

        $result = $this->jobModel->search($filters, $page, 9);

        // Calculate relevance scores for current candidate
        $userSkills = [];
        if (!empty($user['skills'])) {
            $userSkills = is_string($user['skills']) ? json_decode($user['skills'], true) : $user['skills'];
        }

        $rankedJobs = $this->matchingService->rankJobs(
            $result['jobs'],
            $userSkills ?: [],
            $user['work_preference'] ?? 'any'
        );

        $result['jobs'] = $rankedJobs;

        $pageTitle = 'Explore Jobs';
        $navbarTitle = 'Job Discovery';

        ob_start();
        require VIEWS_PATH . '/jobs/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── GET /jobs/{id} ────────────────────────────────────────────────────────
    public function show(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $job    = $this->jobModel->findById($id);

        if (!$job) {
            Flash::error('Job posting not found or has been removed.');
            Auth::redirect('/jobs');
        }

        $this->jobModel->incrementViews($id);

        // Check if user already applied
        $db = Database::getInstance();
        $existingApp = $db->fetchOne(
            "SELECT * FROM applications WHERE user_id = :uid AND job_id = :jid LIMIT 1",
            [':uid' => $userId, ':jid' => $id]
        );

        // Fetch candidate's resumes
        $resumeModel = new Resume();
        $resumes     = $resumeModel->getAllByUser($userId);

        // Calculate relevance score
        $userModel  = new User();
        $user       = $userModel->findById($userId);
        $userSkills = !empty($user['skills']) ? (is_string($user['skills']) ? json_decode($user['skills'], true) : $user['skills']) : [];
        $matchScore = $this->matchingService->calculateScore($job, $userSkills ?: [], $user['work_preference'] ?? 'any');

        $pageTitle   = $job['title'] . ' at ' . $job['company'];
        $navbarTitle = 'Job Details';

        ob_start();
        require VIEWS_PATH . '/jobs/show.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }
}
