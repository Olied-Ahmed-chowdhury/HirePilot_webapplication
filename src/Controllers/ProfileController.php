<?php
/**
 * HirePilot AI — Profile Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\User;

class ProfileController
{
    private User $userModel;

    public function __construct()
    {
        Auth::require();
        $this->userModel = new User();
    }

    // ── GET /profile ──────────────────────────────────────────────────────────
    public function show(array $params = []): void
    {
        $user = $this->userModel->findById(Auth::id());
        $pageTitle = 'My Profile';
        $navbarTitle = 'Profile & Preferences';

        ob_start();
        require VIEWS_PATH . '/profile/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /profile ─────────────────────────────────────────────────────────
    public function update(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();

            $name           = trim($_POST['name'] ?? '');
            $headline       = trim($_POST['headline'] ?? '');
            $bio            = trim($_POST['bio'] ?? '');
            $phone          = trim($_POST['phone'] ?? '');
            $location       = trim($_POST['location'] ?? '');
            $remotePref     = $_POST['remote_pref'] ?? 'any';
            $targetRole     = trim($_POST['target_role'] ?? '');
            $experienceYrs  = (int)($_POST['experience_yrs'] ?? 0);
            $linkedinUrl    = trim($_POST['linkedin_url'] ?? '');
            $githubUrl      = trim($_POST['github_url'] ?? '');
            $portfolioUrl   = trim($_POST['portfolio_url'] ?? '');

            // Skills JSON parsing
            $rawSkills = $_POST['skills'] ?? '[]';
            $decoded = json_decode($rawSkills, true);
            $skillsJson = is_array($decoded) ? json_encode(array_values(array_filter(array_map('trim', $decoded)))) : json_encode([]);

            if (empty($name)) {
                throw new \InvalidArgumentException('Full Name cannot be empty.');
            }

            $this->userModel->updateProfile($userId, [
                'name'           => $name,
                'headline'       => $headline,
                'bio'            => $bio,
                'phone'          => $phone,
                'location'       => $location,
                'remote_pref'    => $remotePref,
                'target_role'    => $targetRole,
                'experience_yrs' => $experienceYrs,
                'skills'         => $skillsJson,
                'linkedin_url'   => $linkedinUrl,
                'github_url'     => $githubUrl,
                'portfolio_url'  => $portfolioUrl,
            ]);

            // Update session data
            $freshUser = $this->userModel->findById($userId);
            $_SESSION['user'] = User::forSession($freshUser);

            Flash::success('Profile updated successfully!');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/profile');
    }

    // ── GET /profile/change-password ──────────────────────────────────────────
    public function showChangePassword(array $params = []): void
    {
        $user = $this->userModel->findById(Auth::id());
        $pageTitle = 'Security Settings';
        $navbarTitle = 'Password & Security';

        ob_start();
        require VIEWS_PATH . '/profile/security.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /profile/change-password ─────────────────────────────────────────
    public function updatePassword(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();
            $user = $this->userModel->findById($userId);

            $currentPass = $_POST['current_password'] ?? '';
            $newPass     = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            // If account has password, verify current password
            if (!empty($user['password_hash'])) {
                if (!User::verifyPassword($currentPass, $user['password_hash'])) {
                    throw new \InvalidArgumentException('Current password does not match.');
                }
            }

            if (strlen($newPass) < 8 || !preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
                throw new \InvalidArgumentException('New password must be at least 8 characters with 1 uppercase letter and 1 number.');
            }

            if ($newPass !== $confirmPass) {
                throw new \InvalidArgumentException('New password and confirmation do not match.');
            }

            $newHash = User::hashPassword($newPass);
            $this->userModel->updatePassword($userId, $newHash);

            Flash::success('Password updated successfully! Please keep it secure.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/profile/change-password');
    }
}
