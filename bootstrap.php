<?php
/**
 * HirePilot AI — Application Bootstrap
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

define('ROOT_PATH', __DIR__);

require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/autoload.php';

if (config('app.debug')) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

set_exception_handler(function (Throwable $e): void {
    if (config('app.debug')) {
        echo '<div style="font-family:monospace;background:#0f172a;color:#e2e8f0;padding:1.5rem;margin:1rem;border-radius:8px;border:1px solid #334155">';
        echo '<strong style="color:#f87171">Application Error</strong><br><br>';
        echo htmlspecialchars($e->getMessage()) . '<br>';
        echo '<small style="color:#94a3b8">' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
        echo '</div>';
    } else {
        http_response_code(500);
        require ROOT_PATH . '/views/errors/500.php';
    }
    exit(1);
});

// Session configuration & hardening (SRS 2.1, 4)
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Ensure writable local session directory if default /tmp is inaccessible
$sessionDir = STORAGE_PATH . '/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0755, true);
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}

session_set_cookie_params([
    'lifetime' => config('session.lifetime'),
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (bool) config('session.secure'),
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Dhaka');
