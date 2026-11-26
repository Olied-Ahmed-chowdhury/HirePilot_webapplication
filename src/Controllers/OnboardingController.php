<?php
/**
 * HirePilot AI — Onboarding & AI Follow-Up Inquiry Controller (SRS 2.3)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\User;
use HirePilot\Services\AiInquiryService;

class OnboardingController
{
    private User $userModel;
    private AiInquiryService $inquiryService;

    public function __construct()
    {
        Auth::require();
        $this->userModel      = new User();
        $this->inquiryService = new AiInquiryService();
    }

    // ── GET /onboarding ───────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId = Auth::id();
        $user   = $this->userModel->findById($userId);

        $history = !empty($user['ai_inquiry_history'])
            ? (json_decode((string) $user['ai_inquiry_history'], true) ?: [])
            : [];

        if (empty($history)) {
            $history[] = [
                'role'    => 'assistant',
                'content' => 'Welcome to HirePilot AI. To tailor job match scores and application drafts, please tell me your target role, years of experience, key technical skills, and preferred work mode (remote, hybrid, or on-site).',
            ];
            $this->userModel->updateProfile($userId, [
                'ai_inquiry_history' => json_encode($history),
            ]);
        }

        $missingFields = $this->inquiryService->getMissingFields($user);
        $pageTitle     = 'AI Profile Inquiry';
        $navbarTitle   = 'Profile Onboarding & AI Inquiry';

        ob_start();
        require VIEWS_PATH . '/profile/onboarding.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /onboarding/reply (AJAX JSON) ────────────────────────────────────
    public function reply(array $params = []): void
    {
        header('Content-Type: application/json');

        try {
            Csrf::verify();
            $userId = Auth::id();
            $user   = $this->userModel->findById($userId);

            $raw     = file_get_contents('php://input');
            $json    = json_decode((string) $raw, true);
            $message = trim((string) ($json['message'] ?? $_POST['message'] ?? ''));

            if ($message === '') {
                throw new \InvalidArgumentException('Please enter a response before submitting.');
            }

            $history = !empty($user['ai_inquiry_history'])
                ? (json_decode((string) $user['ai_inquiry_history'], true) ?: [])
                : [];

            // External API call happens strictly OUTSIDE any DB transaction
            $aiResult = $this->inquiryService->processTurn($user, $history, $message);

            if (!empty($aiResult['api_error'])) {
                http_response_code(502);
                echo json_encode([
                    'success' => false,
                    'message' => $aiResult['api_error'],
                ]);
                exit;
            }

            // Append turn to history
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $aiResult['reply']];

            $extracted = $aiResult['extracted'];
            $updates   = ['ai_inquiry_history' => json_encode($history)];

            if (!empty($extracted['target_role'])) {
                $updates['target_role'] = trim((string) $extracted['target_role']);
            }
            if (!empty($extracted['experience_level']) && in_array($extracted['experience_level'], ['entry', 'mid', 'senior', 'lead'], true)) {
                $updates['experience_level'] = $extracted['experience_level'];
            }
            if (!empty($extracted['work_preference']) && in_array($extracted['work_preference'], ['remote', 'hybrid', 'onsite', 'any'], true)) {
                $updates['work_preference'] = $extracted['work_preference'];
            }
            if (!empty($extracted['preferred_location'])) {
                $updates['preferred_location'] = trim((string) $extracted['preferred_location']);
            }
            if (!empty($extracted['career_goals'])) {
                $updates['career_goals'] = trim((string) $extracted['career_goals']);
            }
            if (!empty($extracted['skills']) && is_array($extracted['skills'])) {
                $existingSkills = !empty($user['skills'])
                    ? (is_string($user['skills']) ? (json_decode($user['skills'], true) ?? []) : $user['skills'])
                    : [];
                $merged = array_values(array_unique(array_merge($existingSkills, array_map('trim', $extracted['skills']))));
                $updates['skills'] = json_encode($merged);
            }

            if ($aiResult['completed'] || (!empty($updates['target_role']) && !empty($updates['skills']))) {
                $updates['onboarding_completed'] = 1;
            }

            $this->userModel->updateProfile($userId, $updates);
            $freshUser = $this->userModel->findById($userId);
            $_SESSION['user'] = User::forSession($freshUser);

            echo json_encode([
                'success'   => true,
                'reply'     => $aiResult['reply'],
                'completed' => (bool) ($freshUser['onboarding_completed'] ?? false),
                'profile'   => [
                    'target_role'        => $freshUser['target_role'] ?? '',
                    'experience_level'   => $freshUser['experience_level'] ?? '',
                    'work_preference'    => $freshUser['work_preference'] ?? 'any',
                    'preferred_location' => $freshUser['preferred_location'] ?? '',
                    'career_goals'       => $freshUser['career_goals'] ?? '',
                    'skills'             => json_decode((string) ($freshUser['skills'] ?? '[]'), true) ?: [],
                ],
            ]);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ── POST /onboarding/skip ─────────────────────────────────────────────────
    public function skip(array $params = []): void
    {
        Csrf::verify();
        $this->userModel->updateProfile(Auth::id(), ['onboarding_completed' => 1]);
        $freshUser = $this->userModel->findById(Auth::id());
        $_SESSION['user'] = User::forSession($freshUser);
        Flash::info('You can complete or refine your profile anytime from Profile Settings.');
        Auth::redirect('/dashboard');
    }
}
