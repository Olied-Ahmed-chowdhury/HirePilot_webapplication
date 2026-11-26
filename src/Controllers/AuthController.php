<?php
/**
 * HirePilot AI — Auth Controller (SRS 2.1: Session-Hardened Native Auth)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\User;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(array $params = []): void
    {
        if (Auth::check()) {
            Auth::redirect('/dashboard');
        }

        $pageTitle = 'Sign In';
        ob_start();
        require VIEWS_PATH . '/auth/login.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public function login(array $params = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                throw new \InvalidArgumentException('Email and password are required.');
            }

            $user = $this->userModel->findByEmail($email);

            if (!$user || !(int) $user['is_active']) {
                throw new \RuntimeException('Invalid email or password.');
            }

            if (empty($user['password_hash']) || !User::verifyPassword($password, $user['password_hash'])) {
                throw new \RuntimeException('Invalid email or password.');
            }

            Auth::login(User::forSession($user));
            $this->userModel->touchLastLogin((int) $user['id']);
            Csrf::regenerate();

            $target = empty($user['onboarding_completed']) && $user['role'] !== 'admin'
                ? '/onboarding'
                : '/dashboard';

            if ($isAjax) {
                $this->json(['success' => true, 'redirect' => BASE_URL . $target]);
                return;
            }

            Flash::success("Signed in as {$user['name']}.");
            Auth::redirect($target);
        } catch (\Throwable $e) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 422);
                return;
            }
            Flash::error($e->getMessage());
            Auth::redirect('/auth/login');
        }
    }

    public function showRegister(array $params = []): void
    {
        if (Auth::check()) {
            Auth::redirect('/dashboard');
        }

        $pageTitle = 'Create Account';
        ob_start();
        require VIEWS_PATH . '/auth/register.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public function register(array $params = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();

            $name     = trim($_POST['name'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';

            if (strlen($name) < 2) {
                throw new \InvalidArgumentException('Full name must be at least 2 characters.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Please enter a valid email address.');
            }

            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                throw new \InvalidArgumentException('Password must be at least 8 characters and include an uppercase letter and a number.');
            }

            if ($password !== $confirm) {
                throw new \InvalidArgumentException('Passwords do not match.');
            }

            if ($this->userModel->findByEmail($email)) {
                throw new \RuntimeException('An account with this email already exists.');
            }

            $userId = $this->userModel->create([
                'name'                 => $name,
                'email'                => $email,
                'password_hash'        => User::hashPassword($password),
                'role'                 => 'user',
                'onboarding_completed' => 0,
            ]);

            $user = $this->userModel->findById($userId);
            Auth::login(User::forSession($user));
            Csrf::regenerate();

            if ($isAjax) {
                $this->json(['success' => true, 'redirect' => BASE_URL . '/onboarding']);
                return;
            }

            Flash::success('Account created. Let us complete your profile.');
            Auth::redirect('/onboarding');
        } catch (\Throwable $e) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 422);
                return;
            }
            Flash::error($e->getMessage());
            Auth::redirect('/auth/register');
        }
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        Flash::info('You have been signed out.');
        Auth::redirect('/auth/login');
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
