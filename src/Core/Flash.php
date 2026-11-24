<?php
/**
 * HirePilot AI — Session Flash Helper
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Provides one-shot flash messages (success, error, warning, info)
 * that survive one redirect and are auto-consumed on next read.
 */

declare(strict_types=1);

namespace HirePilot\Core;

class Flash
{
    private const KEY = '_flash';

    public static function set(string $type, string $message): void
    {
        $_SESSION[self::KEY][$type][] = $message;
    }

    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error',   $msg); }
    public static function warning(string $msg): void { self::set('warning', $msg); }
    public static function info(string $msg): void    { self::set('info',    $msg); }

    /**
     * Get and clear all flash messages (or for a specific type).
     */
    public static function get(?string $type = null): array
    {
        if ($type !== null) {
            $msgs = $_SESSION[self::KEY][$type] ?? [];
            unset($_SESSION[self::KEY][$type]);
            return $msgs;
        }

        $all = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $all;
    }

    /**
     * Check if any flash messages exist.
     */
    public static function has(?string $type = null): bool
    {
        if ($type !== null) {
            return !empty($_SESSION[self::KEY][$type]);
        }
        return !empty($_SESSION[self::KEY]);
    }

    /**
     * Render Bootstrap 5 alert HTML for all flash messages.
     */
    public static function render(): string
    {
        if (!self::has()) {
            return '';
        }

        $map = [
            'success' => ['alert-success', '✓'],
            'error'   => ['alert-danger',  '✕'],
            'warning' => ['alert-warning', '⚠'],
            'info'    => ['alert-info',    'ℹ'],
        ];

        $html = '<div id="flash-container" class="flash-container">';
        foreach (self::get() as $type => $messages) {
            [$cls, $icon] = $map[$type] ?? ['alert-secondary', '•'];
            foreach ($messages as $msg) {
                $html .= sprintf(
                    '<div class="alert %s alert-dismissible fade show shadow-sm" role="alert">'
                    . '<span class="me-2">%s</span>%s'
                    . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
                    . '</div>',
                    $cls,
                    $icon,
                    htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')
                );
            }
        }
        $html .= '</div>';
        return $html;
    }
}

