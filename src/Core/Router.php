<?php
/**
 * HirePilot AI — HTTP Router (Front Controller)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Lightweight regex router.  Full routes registered in Milestone 2.
 * Usage:
 *   Router::get('/jobs', [JobController::class, 'index']);
 *   Router::post('/jobs/{id}/apply', [ApplicationController::class, 'store']);
 *   Router::dispatch();
 */

declare(strict_types=1);

namespace HirePilot\Core;

class Router
{
    private static array $routes = [];

    public static function get(string $pattern, callable|array $handler): void
    {
        self::add('GET', $pattern, $handler);
    }

    public static function post(string $pattern, callable|array $handler): void
    {
        self::add('POST', $pattern, $handler);
    }

    public static function put(string $pattern, callable|array $handler): void
    {
        self::add('PUT', $pattern, $handler);
    }

    public static function delete(string $pattern, callable|array $handler): void
    {
        self::add('DELETE', $pattern, $handler);
    }

    private static function add(string $method, string $pattern, callable|array $handler): void
    {
        // Convert {param} → named capture groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        self::$routes[] = compact('method', 'pattern', 'regex', 'handler');
    }

    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support method spoofing via _method POST field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $path     = '/' . ltrim(str_replace($basePath, '', $uri), '/') ?: '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                self::invoke($route['handler'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            require VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }

    private static function invoke(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func($handler, $params);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($params);
        }
    }
}

