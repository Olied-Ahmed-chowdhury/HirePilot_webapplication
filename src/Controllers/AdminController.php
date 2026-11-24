<?php
/**
 * HirePilot AI — Admin Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Enforces role = 'admin'.
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Database;
use HirePilot\Core\Flash;
use HirePilot\Models\Job;
use HirePilot\Models\User;

class AdminController
{
    private Database $db;
    private Job $jobModel;
    private User $userModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->db        = Database::getInstance();
        $this->jobModel  = new Job();
        $this->userModel = new User();
    }

    // ── GET /admin ────────────────────────────────────────────────────────────
    public function dashboard(array $params = []): void
    {
        // System metrics
        $totalUsers = (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
        $totalJobs  = (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM jobs")['c'] ?? 0);
        $totalApps  = (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM applications")['c'] ?? 0);
        $totalDrafts= (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM email_drafts")['c'] ?? 0);

        // Recent users
        $recentUsers = $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

        // Recent applications
        $recentApps = $this->db->fetchAll(
            "SELECT a.*, u.name AS user_name, u.email AS user_email, j.title AS job_title, j.company AS job_company
             FROM applications a
             JOIN users u ON a.user_id = u.id
             JOIN jobs j ON a.job_id = j.id
             ORDER BY a.created_at DESC LIMIT 5"
        );

        $pageTitle = 'Admin Console';
        $navbarTitle = 'System Administration';

        ob_start();
        require VIEWS_PATH . '/admin/dashboard.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── GET /admin/jobs ───────────────────────────────────────────────────────
    public function jobs(array $params = []): void
    {
        $jobs = $this->db->fetchAll("SELECT * FROM jobs ORDER BY created_at DESC");

        $pageTitle = 'Manage Job Postings';
        $navbarTitle = 'Admin: Job Management';

        ob_start();
        require VIEWS_PATH . '/admin/jobs.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── GET /admin/jobs/create ────────────────────────────────────────────────
    public function createJob(array $params = []): void
    {
        $job = null; // empty for creation
        $pageTitle = 'Post New Job';
        $navbarTitle = 'Admin: Create Job';

        ob_start();
        require VIEWS_PATH . '/admin/job_form.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /admin/jobs ──────────────────────────────────────────────────────
    public function storeJob(array $params = []): void
    {
        try {
            Csrf::verify();

            $title           = trim($_POST['title'] ?? '');
            $company         = trim($_POST['company'] ?? '');
            $location        = trim($_POST['location'] ?? '');
            $isRemote        = !empty($_POST['is_remote']);
            $jobType         = $_POST['job_type'] ?? 'full-time';
            $experienceLevel = $_POST['experience_level'] ?? 'mid';
            $salaryMin       = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
            $salaryMax       = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
            $salaryCurrency  = trim($_POST['salary_currency'] ?? 'BDT');
            $description     = trim($_POST['description'] ?? '');
            $requirements    = trim($_POST['requirements'] ?? '');
            $benefits        = trim($_POST['benefits'] ?? '');
            $deadline        = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
            $status          = $_POST['status'] ?? 'active';

            // Parse skills
            $rawSkills = $_POST['skills_required'] ?? '[]';
            $decoded = json_decode($rawSkills, true);
            if (!is_array($decoded)) {
                $decoded = array_filter(array_map('trim', explode(',', $rawSkills)));
            }
            $skillsJson = json_encode(array_values($decoded));

            if (empty($title) || empty($company) || empty($description)) {
                throw new \InvalidArgumentException('Title, Company, and Description are required.');
            }

            $jobId = $this->jobModel->create([
                'created_by'       => Auth::id(),
                'title'            => $title,
                'company'          => $company,
                'location'         => $location,
                'is_remote'        => $isRemote ? 1 : 0,
                'job_type'         => $jobType,
                'experience_level' => $experienceLevel,
                'salary_min'       => $salaryMin,
                'salary_max'       => $salaryMax,
                'salary_currency'  => $salaryCurrency,
                'description'      => $description,
                'requirements'     => $requirements,
                'benefits'         => $benefits,
                'skills_required'  => $skillsJson,
                'deadline'         => $deadline,
                'status'           => $status,
            ]);

            Flash::success("Job \"{$title}\" posted successfully!");
            Auth::redirect('/admin/jobs');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Auth::redirect('/admin/jobs/create');
        }
    }

    // ── GET /admin/jobs/{id}/edit ─────────────────────────────────────────────
    public function editJob(array $params = []): void
    {
        $id  = (int)($params['id'] ?? 0);
        $job = $this->jobModel->findById($id);

        if (!$job) {
            Flash::error('Job not found.');
            Auth::redirect('/admin/jobs');
        }

        $pageTitle = 'Edit Job: ' . $job['title'];
        $navbarTitle = 'Admin: Edit Job';

        ob_start();
        require VIEWS_PATH . '/admin/job_form.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /admin/jobs/{id} ─────────────────────────────────────────────────
    public function updateJob(array $params = []): void
    {
        try {
            Csrf::verify();
            $id = (int)($params['id'] ?? 0);

            $title           = trim($_POST['title'] ?? '');
            $company         = trim($_POST['company'] ?? '');
            $location        = trim($_POST['location'] ?? '');
            $isRemote        = !empty($_POST['is_remote']);
            $jobType         = $_POST['job_type'] ?? 'full-time';
            $experienceLevel = $_POST['experience_level'] ?? 'mid';
            $salaryMin       = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
            $salaryMax       = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
            $salaryCurrency  = trim($_POST['salary_currency'] ?? 'BDT');
            $description     = trim($_POST['description'] ?? '');
            $requirements    = trim($_POST['requirements'] ?? '');
            $benefits        = trim($_POST['benefits'] ?? '');
            $deadline        = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
            $status          = $_POST['status'] ?? 'active';

            // Parse skills
            $rawSkills = $_POST['skills_required'] ?? '[]';
            $decoded = json_decode($rawSkills, true);
            if (!is_array($decoded)) {
                $decoded = array_filter(array_map('trim', explode(',', $rawSkills)));
            }
            $skillsJson = json_encode(array_values($decoded));

            $this->jobModel->update($id, [
                'title'            => $title,
                'company'          => $company,
                'location'         => $location,
                'is_remote'        => $isRemote ? 1 : 0,
                'job_type'         => $jobType,
                'experience_level' => $experienceLevel,
                'salary_min'       => $salaryMin,
                'salary_max'       => $salaryMax,
                'salary_currency'  => $salaryCurrency,
                'description'      => $description,
                'requirements'     => $requirements,
                'benefits'         => $benefits,
                'skills_required'  => $skillsJson,
                'deadline'         => $deadline,
                'status'           => $status,
            ]);

            Flash::success("Job \"{$title}\" updated successfully.");
            Auth::redirect('/admin/jobs');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Auth::redirect("/admin/jobs/{$id}/edit");
        }
    }

    // ── POST /admin/jobs/{id}/toggle ──────────────────────────────────────────
    public function toggleJobStatus(array $params = []): void
    {
        try {
            Csrf::verify();
            $id  = (int)($params['id'] ?? 0);
            $job = $this->jobModel->findById($id);

            if ($job) {
                $newStatus = $job['status'] === 'active' ? 'paused' : 'active';
                $this->jobModel->update($id, ['status' => $newStatus]);
                Flash::success("Job status set to {$newStatus}.");
            }
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/admin/jobs');
    }

    // ── POST /admin/jobs/{id}/delete ──────────────────────────────────────────
    public function deleteJob(array $params = []): void
    {
        try {
            Csrf::verify();
            $id = (int)($params['id'] ?? 0);
            $this->jobModel->delete($id);
            Flash::success('Job deleted successfully.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/admin/jobs');
    }

    // ── GET /admin/users ──────────────────────────────────────────────────────
    public function users(array $params = []): void
    {
        $users = $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

        $pageTitle = 'Manage Users';
        $navbarTitle = 'Admin: User Management';

        ob_start();
        require VIEWS_PATH . '/admin/users.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /admin/users/{id}/toggle ─────────────────────────────────────────
    public function toggleUserStatus(array $params = []): void
    {
        try {
            Csrf::verify();
            $id = (int)($params['id'] ?? 0);
            if ($id === Auth::id()) {
                throw new \RuntimeException('Cannot deactivate your own administrator account.');
            }
            $this->userModel->toggleActive($id);
            Flash::success('User account status toggled.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/admin/users');
    }
}
