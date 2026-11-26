<?php
/**
 * HirePilot AI — Layout: HTML <head>
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$pageTitle ??= config('app.name');
$pageDesc  ??= 'Job Search and Application Management System';
$csrfToken   = Csrf::token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES) ?>">
  <meta name="csrf-token"  content="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> — HirePilot AI</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <?= $extraHead ?? '' ?>
</head>
<body>
