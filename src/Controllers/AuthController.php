<?php
/**
 * HirePilot AI — Auth Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Routes:
 *   GET  /auth/login          → showLogin()
 *   POST /auth/login          → login()
 *   GET  /auth/register       → showRegister()
 *   POST /auth/register       → register()
 *   GET  /auth/logout         → logout()
 *   GET  /auth/google         → googleRedirect()
 *   GET  /auth/google/callback → googleCallback()
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\User;
use HirePilot\Services\GoogleAuthService;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ── GET /auth/login ───────────────────────────────────────────────────────
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

    // ── POST /auth/login ──────────────────────────────────────────────────────
    public function login(array $params = []): void
    {
        // JSON response for AJAX, redirect for form POST
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new \InvalidArgumentException('Email and password are required.');
            }

            $user = $this->userModel->findByEmail($email);

            if (!$user || !$user['is_active']) {
                throw new \RuntimeException('Invalid email or password.');
            }

            // Google-only accounts have no password
            if (empty($user['password_hash'])) {
                throw new \RuntimeException('This account uses Google Sign-In. Please use the Google button.');
            }

            if (!User::verifyPassword($password, $user['password_hash'])) {
                throw new \RuntimeException('Invalid email or password.');
            }

            // Log in
            Auth::login(User::forSession($user));
            $this->userModel->touchLastLogin((int)$user['id']);
            Csrf::regenerate();

            if ($isAjax) {
                $this->json(['success' => true, 'redirect' => BASE_URL . '/dashboard']);
                return;
            }

            Flash::success("Welcome back, {$user['name']}! 👋");
            Auth::redirect('/dashboard');

        } catch (\Throwable $e) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 422);
                return;
            }
            Flash::error($e->getMessage());
            Auth::redirect('/auth/login');
        }
    }

    // ── GET /auth/register ────────────────────────────────────────────────────
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

    // ── POST /auth/register ───────────────────────────────────────────────────
    public function register(array $params = []): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        try {
            Csrf::verify();

            $name     = trim($_POST['name'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';

            // Validation
            if (empty($name) || strlen($name) < 2) {
                throw new \InvalidArgumentException('Name must be at least 2 characters.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Please enter a valid email address.');
            }

            if (strlen($password) < 8) {
                throw new \InvalidArgumentException('Password must be at least 8 characters.');
            }

            if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                throw new \InvalidArgumentException('Password must contain at least one uppercase letter and one number.');
            }

            if ($password !== $confirm) {
                throw new \InvalidArgumentException('Passwords do not match.');
            }

            // Check existing
            if ($this->userModel->findByEmail($email)) {
                throw new \RuntimeException('An account with this email already exists.');
            }

            // Create user
            $userId = $this->userModel->create([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => User::hashPassword($password),
                'role'          => 'user',
            ]);

            // Auto-login
            $user = $this->userModel->findById($userId);
            Auth::login(User::forSession($user));
            Csrf::regenerate();

            if ($isAjax) {
                $this->json(['success' => true, 'redirect' => BASE_URL . '/dashboard']);
                return;
            }

            Flash::success("Welcome to HirePilot AI, {$name}! Your account is ready. 🚀");
            Auth::redirect('/dashboard');

        } catch (\Throwable $e) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 422);
                return;
            }
            Flash::error($e->getMessage());
            Auth::redirect('/auth/register');
        }
    }

    // ── GET /auth/logout ──────────────────────────────────────────────────────
    public function logout(array $params = []): void
    {
        Auth::logout();
        Flash::info('You have been logged out successfully.');
        Auth::redirect('/auth/login');
    }

    // ── GET /auth/google ──────────────────────────────────────────────────────
    public function googleRedirect(array $params = []): void
    {
        $service = new GoogleAuthService();
        $url     = $service->getAuthUrl();

        if (!$url) {
            Flash::error('Google Sign-In is not configured. Please use email/password.');
            Auth::redirect('/auth/login');
        }

        header('Location: ' . $url);
        exit;
    }

    // ── GET /auth/google/callback ─────────────────────────────────────────────
    public function googleCallback(array $params = []): void
    {
        try {
            $code = $_GET['code'] ?? '';
            if (empty($code)) {
                throw new \RuntimeException('Google authentication was cancelled or failed.');
            }

            $service    = new GoogleAuthService();
            $googleUser = $service->authenticate($code);

            if (!$googleUser) {
                throw new \RuntimeException('Failed to retrieve Google account information.');
            }

            // Check if user exists
            $user = $this->userModel->findByGoogleId($googleUser['id'])
                 ?? $this->userModel->findByEmail($googleUser['email']);

            if ($user) {
                // Update Google tokens
                $this->userModel->updateGoogleTokens(
                    (int)$user['id'],
                    $googleUser['id'],
                    $googleUser['token'] ?? null
                );
            } else {
                // Create new user from Google profile
                $userId = $this->userModel->create([
                    'name'          => $googleUser['name'],
                    'email'         => $googleUser['email'],
                    'google_id'     => $googleUser['id'],
                    'google_token'  => $googleUser['token'] ?? null,
                    'avatar'        => $googleUser['picture'] ?? null,
                    'role'          => 'user',
                ]);
                $user = $this->userModel->findById($userId);
            }

            Auth::login(User::forSession($user));
            $this->userModel->touchLastLogin((int)$user['id']);
            Csrf::regenerate();

            Flash::success("Welcome, {$user['name']}! Signed in with Google. 🎉");
            Auth::redirect('/dashboard');

        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Auth::redirect('/auth/login');
        }
    }

    // ── Helper: JSON response ─────────────────────────────────────────────────
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

