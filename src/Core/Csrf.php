<?php
/**
 * HirePilot AI — CSRF Protection Helper
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Core;

class Csrf
{
    private const TOKEN_KEY    = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Generate (or retrieve existing) CSRF token for the session.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Render a hidden CSRF input field.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate a submitted CSRF token (timing-safe comparison).
     */
    public static function validate(?string $submitted = null): bool
    {
        $submitted ??= $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $expected    = $_SESSION[self::TOKEN_KEY] ?? '';

        if ($expected === '' || $submitted === '') {
            return false;
        }

        return hash_equals($expected, $submitted);
    }

    /**
     * Validate and throw on failure.
     *
     * @throws \RuntimeException
     */
    public static function verify(): void
    {
        if (!self::validate()) {
            http_response_code(403);
            throw new \RuntimeException('CSRF token mismatch. Please refresh the page and try again.');
        }
    }

    /**
     * Rotate the token after a successful form submission.
     */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
}

