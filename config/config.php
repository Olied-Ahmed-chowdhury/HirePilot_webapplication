<?php
/**
 * HirePilot AI — Application Configuration
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

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
            $value = trim($value, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true', '1', 'yes' => true,
        'false', '0', 'no' => false,
        'null', ''         => null,
        default            => $value,
    };
}

$CONFIG = [
    'app' => [
        'name'    => env('APP_NAME', 'HirePilot AI'),
        'env'     => env('APP_ENV', 'development'),
        'debug'   => env('APP_DEBUG', true),
        'url'     => env('APP_URL', 'http://localhost/HirePilot_webapplication/public'),
        'version' => env('APP_VERSION', '1.3.0'),
        'secret'  => env('APP_SECRET', 'default_secret_change_in_production'),
    ],

    'db' => [
        'host'        => env('DB_HOST', '127.0.0.1'),
        'port'        => (int) env('DB_PORT', 3306),
        'name'        => env('DB_NAME', 'hirepilot_db'),
        'user'        => env('DB_USER', 'root'),
        'pass'        => env('DB_PASS', ''),
        'charset'     => env('DB_CHARSET', 'utf8mb4'),
        'sqlite_path' => dirname(__DIR__) . '/storage/database/hirepilot.sqlite',
    ],

    'ai' => [
        'provider'    => env('AI_PROVIDER', 'groq'),
        'api_key'     => env('AI_API_KEY', ''),
        'api_url'     => env('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
        'model'       => env('AI_MODEL', 'llama-3.3-70b-versatile'),
        'max_tokens'  => (int) env('AI_MAX_TOKENS', 1200),
        'temperature' => (float) env('AI_TEMPERATURE', 0.6),
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

    'upload' => [
        'max_size'      => (int) env('UPLOAD_MAX_SIZE', 5242880),
        'allowed_types' => explode(',', (string) env('UPLOAD_ALLOWED_TYPES', 'pdf,doc,docx')),
        'path'          => dirname(__DIR__) . '/' . env('UPLOAD_PATH', 'storage/uploads'),
    ],

    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 7200),
        'secure'   => env('SESSION_SECURE', false),
    ],

    'jobs_api' => [
        'fantastic_key' => env('FANTASTIC_JOBS_API_KEY', ''),
        'fantastic_url' => env('FANTASTIC_JOBS_API_URL', 'https://data.fantastic.jobs/v1/active-ats'),
        'apify_token'   => env('APIFY_API_TOKEN', ''),
        'apify_actor'   => env('APIFY_LINKEDIN_ACTOR', 'bebity~linkedin-jobs-scraper'),
    ],
];

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

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', rtrim((string) config('app.url'), '/'));
define('VIEWS_PATH', BASE_PATH . '/views');
define('STORAGE_PATH', BASE_PATH . '/storage');
