<?php
/**
 * HirePilot AI — Profile Controller (SRS 2.2)
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

    public function show(array $params = []): void
    {
        $user        = $this->userModel->findById(Auth::id());
        $pageTitle   = 'Profile & Preferences';
        $navbarTitle = 'Profile & Preferences';

        ob_start();
        require VIEWS_PATH . '/profile/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    public function update(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();

            $name              = trim($_POST['name'] ?? '');
            $headline          = trim($_POST['headline'] ?? '');
            $bio               = trim($_POST['bio'] ?? '');
            $phone             = trim($_POST['phone'] ?? '');
            $preferredLocation = trim($_POST['preferred_location'] ?? '');
            $workPreference    = $_POST['work_preference'] ?? 'any';
            $targetRole        = trim($_POST['target_role'] ?? '');
            $experienceLevel   = $_POST['experience_level'] ?? 'mid';
            $experienceYrs     = max(0, (int) ($_POST['experience_yrs'] ?? 0));
            $careerGoals       = trim($_POST['career_goals'] ?? '');

            $rawSkills  = $_POST['skills'] ?? '[]';
            $decoded    = json_decode($rawSkills, true);
            $skillsJson = is_array($decoded)
                ? json_encode(array_values(array_filter(array_map('trim', $decoded))))
                : json_encode([]);

            if ($name === '') {
                throw new \InvalidArgumentException('Full Name cannot be empty.');
            }

            $this->userModel->updateProfile($userId, [
                'name'                 => $name,
                'headline'             => $headline,
                'bio'                  => $bio,
                'phone'                => $phone,
                'preferred_location'   => $preferredLocation,
                'work_preference'      => $workPreference,
                'target_role'          => $targetRole,
                'experience_level'     => $experienceLevel,
                'experience_yrs'       => $experienceYrs,
                'skills'               => $skillsJson,
                'career_goals'         => $careerGoals,
                'onboarding_completed' => 1,
            ]);

            $freshUser = $this->userModel->findById($userId);
            $_SESSION['user'] = User::forSession($freshUser);

            Flash::success('Profile and preferences saved.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/profile');
    }

    public function showChangePassword(array $params = []): void
    {
        $user        = $this->userModel->findById(Auth::id());
        $pageTitle   = 'Security Settings';
        $navbarTitle = 'Password & Security';

        ob_start();
        require VIEWS_PATH . '/profile/security.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    public function updatePassword(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();
            $user   = $this->userModel->findById($userId);

            $currentPass = $_POST['current_password'] ?? '';
            $newPass     = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (!User::verifyPassword($currentPass, $user['password_hash'])) {
                throw new \InvalidArgumentException('Current password does not match.');
            }

            if (strlen($newPass) < 8 || !preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
                throw new \InvalidArgumentException('New password must be at least 8 characters with 1 uppercase letter and 1 number.');
            }

            if ($newPass !== $confirmPass) {
                throw new \InvalidArgumentException('New password and confirmation do not match.');
            }

            $this->userModel->updatePassword($userId, User::hashPassword($newPass));
            Flash::success('Password updated successfully.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/profile/change-password');
    }
}
