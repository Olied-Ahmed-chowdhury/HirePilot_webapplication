<?php
/**
 * HirePilot AI — Auth Guard Middleware
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Core;

class Auth
{
    /**
     * Check if a user is logged in.
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get the currently logged-in user's ID.
     */
    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Get the full session user array.
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if logged-in user has a given role.
     */
    public static function hasRole(string $role): bool
    {
        return ($_SESSION['user']['role'] ?? '') === $role;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Require authentication — redirect to login if not logged in.
     */
    public static function require(): void
    {
        if (!self::check()) {
            Flash::warning('Please log in to continue.');
            self::redirect('/auth/login');
        }
    }

    /**
     * Require admin role — 403 if not.
     */
    public static function requireAdmin(): void
    {
        self::require();
        if (!self::isAdmin()) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Log the user in (store in session).
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']          = $user['id'];
        $_SESSION['user']             = $user;
        $_SESSION['_last_regenerated'] = time();
    }

    /**
     * Log the user out.
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();

        // Clear session cookie
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    /**
     * Redirect helper.
     */
    public static function redirect(string $path): never
    {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }
}

