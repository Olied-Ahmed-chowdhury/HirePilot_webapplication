<?php
/**
 * HirePilot AI — Layout: HTML <head> section
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Usage: require VIEWS_PATH . '/layouts/header.php';
 * Variables (set before requiring):
 *   $pageTitle   — Browser tab title
 *   $pageDesc    — Meta description (optional)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;

$pageTitle ??= config('app.name');
$pageDesc  ??= 'AI-Powered Job Search & Application Management System';
$csrfToken   = Csrf::token();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"  content="<?= htmlspecialchars($pageDesc, ENT_QUOTES) ?>">
  <meta name="csrf-token"   content="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
  <meta name="author"       content="Olied Ahmed Chowdhury">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> | HirePilot AI">

  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> | HirePilot AI</title>

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- HirePilot App CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">

  <!-- Page-specific extra head content -->
  <?= $extraHead ?? '' ?>
</head>
<body>

