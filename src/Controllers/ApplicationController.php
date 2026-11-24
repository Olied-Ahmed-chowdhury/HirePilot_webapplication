<?php
/**
 * HirePilot AI — Application Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\Application;
use HirePilot\Models\Job;
use HirePilot\Models\Notification;
use HirePilot\Models\Resume;

class ApplicationController
{
    private Application $appModel;
    private Notification $notifModel;

    public function __construct()
    {
        Auth::require();
        $this->appModel   = new Application();
        $this->notifModel = new Notification();
    }

    // ── GET /applications ─────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId = Auth::id();
        $status = $_GET['status'] ?? null;

        $applications = $this->appModel->getAllByUser($userId, $status);
        $statusCounts = $this->appModel->getCountsByStatus($userId);

        $pageTitle = 'Application Pipeline';
        $navbarTitle = 'Applications & Status Tracker';

        ob_start();
        require VIEWS_PATH . '/applications/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /applications ────────────────────────────────────────────────────
    public function store(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();

            $jobId        = (int)($_POST['job_id'] ?? 0);
            $resumeId     = !empty($_POST['resume_id']) ? (int)$_POST['resume_id'] : null;
            $status       = $_POST['status'] ?? 'applied';
            $coverLetter  = trim($_POST['cover_letter'] ?? '');
            $customNotes  = trim($_POST['custom_notes'] ?? '');

            $jobModel = new Job();
            $job      = $jobModel->findById($jobId);
            if (!$job) {
                throw new \InvalidArgumentException('Invalid job selected.');
            }

            $appId = $this->appModel->create([
                'user_id'      => $userId,
                'job_id'       => $jobId,
                'resume_id'    => $resumeId,
                'status'       => $status,
                'cover_letter' => $coverLetter,
                'custom_notes' => $customNotes,
            ]);

            $jobModel->incrementApplications($jobId);

            // Log event in notifications
            $this->notifModel->create([
                'user_id'        => $userId,
                'type'           => 'application_status',
                'title'          => "Application Saved: {$job['title']}",
                'message'        => "Your application for {$job['title']} at {$job['company']} was recorded with status: " . ucfirst(str_replace('_', ' ', $status)),
                'link'           => "/applications/{$appId}",
                'reference_id'   => $appId,
                'reference_type' => 'application',
            ]);

            Flash::success("Application for {$job['title']} at {$job['company']} recorded successfully!");
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/applications');
    }

    // ── GET /applications/{id} ────────────────────────────────────────────────
    public function show(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $app    = $this->appModel->findById($id, $userId);

        if (!$app) {
            Flash::error('Application record not found.');
            Auth::redirect('/applications');
        }

        $resumeModel = new Resume();
        $resumes     = $resumeModel->getAllByUser($userId);

        $pageTitle = 'Application: ' . $app['job_title'];
        $navbarTitle = 'Application Details';

        ob_start();
        require VIEWS_PATH . '/applications/show.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /applications/{id}/status (AJAX Supported) ───────────────────────
    public function updateStatus(array $params = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            // Support JSON payload or Form data
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true);
            $status   = $jsonData['status'] ?? $_POST['status'] ?? '';

            $validStatuses = ['saved', 'applied', 'under_review', 'interview_scheduled', 'offer_received', 'rejected', 'withdrawn'];
            if (!in_array($status, $validStatuses, true)) {
                throw new \InvalidArgumentException('Invalid status value.');
            }

            $app = $this->appModel->findById($id, $userId);
            if (!$app) {
                throw new \RuntimeException('Application not found.');
            }

            $this->appModel->updateStatus($id, $userId, $status);

            // Create notification for audit trail
            $readableStatus = ucfirst(str_replace('_', ' ', $status));
            $this->notifModel->create([
                'user_id'        => $userId,
                'type'           => 'application_status',
                'title'          => "Status Update: {$app['job_title']}",
                'message'        => "Status transitioned to \"{$readableStatus}\"",
                'link'           => "/applications/{$id}",
                'reference_id'   => $id,
                'reference_type' => 'application',
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'status' => $status, 'readable' => $readableStatus]);
                exit;
            }

            Flash::success("Application status updated to {$readableStatus}.");
        } catch (\Throwable $e) {
            if ($isAjax) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            Flash::error($e->getMessage());
        }

        Auth::redirect("/applications/{$id}");
    }

    // ── POST /applications/{id}/details ───────────────────────────────────────
    public function updateDetails(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $status          = $_POST['status'] ?? 'saved';
            $customNotes     = trim($_POST['custom_notes'] ?? '');
            $companyResponse = trim($_POST['company_response'] ?? '');
            $interviewDate   = !empty($_POST['interview_date']) ? $_POST['interview_date'] : null;
            $offerAmount     = !empty($_POST['offer_amount']) ? (float)$_POST['offer_amount'] : null;
            $resumeId        = !empty($_POST['resume_id']) ? (int)$_POST['resume_id'] : null;

            $this->appModel->updateDetails($id, $userId, [
                'status'           => $status,
                'custom_notes'     => $customNotes,
                'company_response' => $companyResponse,
                'interview_date'   => $interviewDate,
                'offer_amount'     => $offerAmount,
                'resume_id'        => $resumeId,
            ]);

            Flash::success('Application notes and details saved successfully.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect("/applications/{$id}");
    }

    // ── POST /applications/{id}/delete ────────────────────────────────────────
    public function delete(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $this->appModel->delete($id, $userId);
            Flash::success('Application removed from tracking.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/applications');
    }
}
