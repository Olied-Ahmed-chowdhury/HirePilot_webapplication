<?php
/**
 * HirePilot AI — Application Bootstrap
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Bootstraps: config → autoloader → session → error handling
 * Include this file at the top of every entry-point (public/index.php, etc.)
 */

declare(strict_types=1);

// ── 1. Absolute paths ─────────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));

// ── 2. Load configuration (also loads .env) ───────────────────────────────────
require ROOT_PATH . '/config/config.php';

// ── 3. Autoloader ─────────────────────────────────────────────────────────────
require ROOT_PATH . '/autoload.php';

// ── 4. Error / Exception handling ─────────────────────────────────────────────
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
        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:2rem;border-radius:8px;font-family:monospace">';
        echo '<strong style="color:#f38ba8">⚠ Uncaught Exception</strong>' . PHP_EOL;
        echo htmlspecialchars($e->getMessage()) . PHP_EOL;
        echo htmlspecialchars($e->getFile()) . ':' . $e->getLine() . PHP_EOL;
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        require ROOT_PATH . '/views/errors/500.php';
    }
    exit(1);
});

// ── 5. Session configuration & start ─────────────────────────────────────────
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (config('session.secure')) {
    ini_set('session.cookie_secure', '1');
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

// ── 6. Regenerate session ID periodically (session fixation protection) ───────
if (empty($_SESSION['_last_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['_last_regenerated'] = time();
} elseif (time() - $_SESSION['_last_regenerated'] > 300) {
    session_regenerate_id(true);
    $_SESSION['_last_regenerated'] = time();
}

// ── 7. Set default timezone ───────────────────────────────────────────────────
date_default_timezone_set('Asia/Dhaka');

