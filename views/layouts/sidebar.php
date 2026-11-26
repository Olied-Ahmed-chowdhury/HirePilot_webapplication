<?php
/**
 * HirePilot AI — Layout: Sidebar Navigation
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;
use HirePilot\Core\Database;

$user    = Auth::user();
$isAdmin = Auth::isAdmin();
$initial = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$appCount = 0;
try {
    $db  = Database::getInstance();
    $cnt = $db->fetchOne(
        "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status NOT IN ('withdrawn','rejected')",
        [':uid' => Auth::id()]
    );
    $appCount = (int) ($cnt['c'] ?? 0);
} catch (\Throwable) {}
?>
<aside class="app-sidebar" id="app-sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand-icon"><i class="bi bi-compass"></i></div>
    <span class="sidebar-brand-name">HirePilot AI</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Workspace</div>

    <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= str_contains($currentPath, '/dashboard') ? 'active' : '' ?>">
      <i class="bi bi-grid"></i> Overview
    </a>

    <a href="<?= BASE_URL ?>/jobs" class="sidebar-link <?= str_contains($currentPath, '/jobs') ? 'active' : '' ?>">
      <i class="bi bi-briefcase"></i> Job Discovery
    </a>

    <a href="<?= BASE_URL ?>/applications" class="sidebar-link <?= str_contains($currentPath, '/applications') ? 'active' : '' ?>">
      <i class="bi bi-kanban"></i> Applications
      <?php if ($appCount > 0): ?>
        <span class="nav-badge"><?= $appCount ?></span>
      <?php endif; ?>
    </a>

    <div class="nav-label">AI Assistant</div>

    <a href="<?= BASE_URL ?>/emails" class="sidebar-link <?= str_contains($currentPath, '/emails') ? 'active' : '' ?>">
      <i class="bi bi-envelope-paper"></i> Application Drafts
    </a>

    <a href="<?= BASE_URL ?>/onboarding" class="sidebar-link <?= str_contains($currentPath, '/onboarding') ? 'active' : '' ?>">
      <i class="bi bi-chat-square-text"></i> AI Profile Inquiry
    </a>

    <div class="nav-label">Account</div>

    <a href="<?= BASE_URL ?>/resume" class="sidebar-link <?= str_contains($currentPath, '/resume') ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-text"></i> Resumes
    </a>

    <a href="<?= BASE_URL ?>/profile" class="sidebar-link <?= $currentPath === '/HirePilot_webapplication/public/profile' ? 'active' : '' ?>">
      <i class="bi bi-person"></i> Profile & Skills
    </a>

    <a href="<?= BASE_URL ?>/profile/change-password" class="sidebar-link <?= str_contains($currentPath, '/change-password') ? 'active' : '' ?>">
      <i class="bi bi-shield-lock"></i> Security
    </a>

    <?php if ($isAdmin): ?>
      <div class="nav-label">Administration</div>

      <a href="<?= BASE_URL ?>/admin" class="sidebar-link <?= preg_match('#/admin$#', $currentPath) ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> System Stats
      </a>

      <a href="<?= BASE_URL ?>/admin/jobs" class="sidebar-link <?= str_contains($currentPath, '/admin/jobs') ? 'active' : '' ?>">
        <i class="bi bi-list-task"></i> Manage Jobs
      </a>

      <a href="<?= BASE_URL ?>/admin/users" class="sidebar-link <?= str_contains($currentPath, '/admin/users') ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Manage Users
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= $initial ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
        <div class="sidebar-user-role"><?= ucfirst($user['role'] ?? 'user') ?></div>
      </div>
      <a href="<?= BASE_URL ?>/auth/logout" class="btn-ghost btn-icon ms-auto" title="Sign out">
        <i class="bi bi-box-arrow-right" style="color:#94a3b8;"></i>
      </a>
    </div>
  </div>
</aside>

<div id="sidebar-overlay"></div>
