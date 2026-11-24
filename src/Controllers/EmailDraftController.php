<?php
/**
 * HirePilot AI — Email & Cover Letter Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\EmailDraft;
use HirePilot\Models\Job;
use HirePilot\Models\Resume;
use HirePilot\Models\User;
use HirePilot\Services\AiDraftingService;
use HirePilot\Services\EmailService;

class EmailDraftController
{
    private EmailDraft $draftModel;
    private AiDraftingService $aiService;
    private EmailService $emailService;

    public function __construct()
    {
        Auth::require();
        $this->draftModel   = new EmailDraft();
        $this->aiService    = new AiDraftingService();
        $this->emailService = new EmailService();
    }

    // ── GET /emails ───────────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId = Auth::id();
        $drafts = $this->draftModel->getAllByUser($userId);

        $pageTitle = 'AI Application Drafts';
        $navbarTitle = 'AI Cover Letters & Outreach';

        ob_start();
        require VIEWS_PATH . '/emails/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── GET /emails/create ────────────────────────────────────────────────────
    public function create(array $params = []): void
    {
        $userId      = Auth::id();
        $jobModel    = new Job();
        $resumeModel = new Resume();

        $selectedJobId = (int)($_GET['job_id'] ?? 0);
        $selectedJob   = $selectedJobId ? $jobModel->findById($selectedJobId) : null;

        $jobs    = $jobModel->search([], 1, 50)['jobs'];
        $resumes = $resumeModel->getAllByUser($userId);

        $pageTitle = 'Draft AI Application';
        $navbarTitle = 'AI Writing Workspace';

        ob_start();
        require VIEWS_PATH . '/emails/create.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /emails/generate ─────────────────────────────────────────────────
    public function generate(array $params = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();
            $userId = Auth::id();

            $jobId     = (int)($_POST['job_id'] ?? 0);
            $resumeId  = (int)($_POST['resume_id'] ?? 0);
            $draftType = $_POST['draft_type'] ?? 'cover_letter';
            $tone      = $_POST['tone'] ?? 'professional';

            $jobModel = new Job();
            $job      = $jobModel->findById($jobId);
            if (!$job) {
                throw new \InvalidArgumentException('Please select a valid job listing.');
            }

            $userModel = new User();
            $user      = $userModel->findById($userId);

            $resumeModel = new Resume();
            $resume      = $resumeId ? $resumeModel->findById($resumeId, $userId) : $resumeModel->getPrimary($userId);
            $resumeText  = $resume['parsed_text'] ?? '';

            // Generate content via Grok AI / local mock
            $aiResult = $this->aiService->generateDraft($user, $job, $resumeText, $draftType, $tone);

            $draftId = $this->draftModel->create([
                'user_id'         => $userId,
                'job_id'          => $job['id'],
                'resume_id'       => $resume['id'] ?? null,
                'draft_type'      => $draftType,
                'subject'         => $aiResult['subject'],
                'body'            => $aiResult['body'],
                'ai_prompt_used'  => $aiResult['prompt'],
                'ai_model_used'   => $aiResult['model_used'],
                'status'          => 'draft',
                'recipient_email' => $job['poster_email'] ?? null,
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'draft_id' => $draftId,
                    'subject'  => $aiResult['subject'],
                    'body'     => $aiResult['body'],
                    'model'    => $aiResult['model_used'],
                    'redirect' => BASE_URL . "/emails/{$draftId}",
                ]);
                exit;
            }

            Flash::success('AI draft generated successfully! Review, edit, and approve below.');
            Auth::redirect("/emails/{$draftId}");
        } catch (\Throwable $e) {
            if ($isAjax) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            Flash::error($e->getMessage());
            Auth::redirect('/emails/create' . (!empty($jobId) ? "?job_id={$jobId}" : ''));
        }
    }

    // ── GET /emails/{id} ──────────────────────────────────────────────────────
    public function show(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $draft  = $this->draftModel->findById($id, $userId);

        if (!$draft) {
            Flash::error('Draft not found.');
            Auth::redirect('/emails');
        }

        $pageTitle = 'Review Draft: ' . ($draft['job_title'] ?? 'Application');
        $navbarTitle = 'Draft Preview & Approval';

        ob_start();
        require VIEWS_PATH . '/emails/show.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /emails/{id}/update ──────────────────────────────────────────────
    public function update(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $subject   = trim($_POST['subject'] ?? '');
            $body      = trim($_POST['body'] ?? '');
            $recipient = trim($_POST['recipient_email'] ?? '');

            if (empty($body)) {
                throw new \InvalidArgumentException('Body cannot be empty.');
            }

            $this->draftModel->update($id, $userId, [
                'subject'         => $subject,
                'body'            => $body,
                'recipient_email' => $recipient,
            ]);

            Flash::success('Draft saved successfully.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect("/emails/{$id}");
    }

    // ── POST /emails/{id}/approve ─────────────────────────────────────────────
    public function approve(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $this->draftModel->update($id, $userId, ['status' => 'approved']);
            Flash::success('Draft marked as Approved! You can now send it.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect("/emails/{$id}");
    }

    // ── POST /emails/{id}/send ────────────────────────────────────────────────
    public function send(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();
            $draft  = $this->draftModel->findById($id, $userId);

            if (!$draft) {
                throw new \RuntimeException('Draft not found.');
            }

            $to = trim($_POST['recipient_email'] ?? $draft['recipient_email'] ?? '');
            if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Please enter a valid recipient email address.');
            }

            // Execute email sending / simulation
            $sendResult = $this->emailService->send($to, $draft['subject'] ?: 'Job Application', $draft['body']);

            $this->draftModel->markSent(
                $id,
                $userId,
                $to,
                json_encode($sendResult)
            );

            Flash::success($sendResult['message']);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect("/emails/{$id}");
    }

    // ── POST /emails/{id}/delete ──────────────────────────────────────────────
    public function delete(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $this->draftModel->delete($id, $userId);
            Flash::success('Draft deleted.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/emails');
    }
}
