<?php
/**
 * HirePilot AI — Application Configuration
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Loads .env file (if present) and exposes a typed config() helper.
 * Usage: config('db.host') or config('app.debug')
 */

declare(strict_types=1);

// ── 1. Load .env from project root ───────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            // Strip surrounding quotes
            $value = trim($value, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// ── 2. Helper: read an env variable with optional default ─────────────────────
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key) ?: null;
    if ($value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true', '1', 'yes'  => true,
        'false', '0', 'no'  => false,
        'null', ''          => null,
        default             => $value,
    };
}

// ── 3. Central configuration array ───────────────────────────────────────────
$CONFIG = [

    'app' => [
        'name'      => env('APP_NAME', 'HirePilot AI'),
        'env'       => env('APP_ENV', 'development'),
        'debug'     => env('APP_DEBUG', true),
        'url'       => env('APP_URL', 'http://localhost/HirePilot_webapplication/public'),
        'version'   => env('APP_VERSION', '1.0.0'),
        'secret'    => env('APP_SECRET', 'default_secret_change_in_production'),
    ],

    'db' => [
        'host'    => env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => env('DB_NAME', 'hirepilot_db'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],

    'ai' => [
        'provider'    => env('AI_PROVIDER', 'mock'),
        'api_key'     => env('AI_API_KEY', ''),
        'api_url'     => env('AI_API_URL', 'https://api.x.ai/v1/chat/completions'),
        'model'       => env('AI_MODEL', 'grok-beta'),
        'max_tokens'  => (int) env('AI_MAX_TOKENS', 1200),
        'temperature' => (float) env('AI_TEMPERATURE', 0.7),
    ],

    'mail' => [
        'driver'     => env('MAIL_DRIVER', 'log'),
        'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
        'port'       => (int) env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
        'from_addr'  => env('MAIL_FROM_ADDRESS', 'noreply@hirepilot.ai'),
        'from_name'  => env('MAIL_FROM_NAME', 'HirePilot AI'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri'  => env('GOOGLE_REDIRECT_URI', ''),
    ],

    'upload' => [
        'max_size'      => (int) env('UPLOAD_MAX_SIZE', 5242880),
        'allowed_types' => explode(',', (string) env('UPLOAD_ALLOWED_TYPES', 'pdf,doc,docx')),
        'path'          => dirname(__DIR__) . '/' . env('UPLOAD_PATH', 'storage/uploads'),
    ],

    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 7200),
        'secure'   => env('SESSION_SECURE', false),
    ],
];

// ── 4. config() helper — dot-notation access ─────────────────────────────────
function config(string $key, mixed $default = null): mixed
{
    global $CONFIG;
    $keys  = explode('.', $key);
    $value = $CONFIG;
    foreach ($keys as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

// ── 5. Base-path / URL helpers ────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  config('app.url'));
define('VIEWS_PATH', BASE_PATH . '/views');
define('STORAGE_PATH', BASE_PATH . '/storage');

