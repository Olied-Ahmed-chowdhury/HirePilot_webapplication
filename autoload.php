<?php
/**
 * HirePilot AI — PSR-4 Class Autoloader
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Maps namespace HirePilot\* → src/*
 */

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix   = 'HirePilot\\';
    $baseDir  = __DIR__ . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

