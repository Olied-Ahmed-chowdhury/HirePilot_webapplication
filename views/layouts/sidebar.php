<?php
/**
 * HirePilot AI — Layout: Sidebar Navigation
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;

$user    = Auth::user();
$isAdmin = Auth::isAdmin();
$initial = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

function navActive(string $path): string {
    global $currentPath;
    return str_starts_with($currentPath, $path) ? 'active' : '';
}
?>
<!-- Sidebar -->
<aside class="app-sidebar" id="app-sidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="sidebar-brand-icon">🚀</div>
    <span class="sidebar-brand-name">HirePilot<span> AI</span></span>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">

    <!-- Main -->
    <div class="nav-label">Main</div>

    <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= navActive('/dashboard') ?>">
      <i class="bi bi-grid-1x2"></i>
      Dashboard
    </a>

    <a href="<?= BASE_URL ?>/jobs" class="sidebar-link <?= navActive('/jobs') ?>">
      <i class="bi bi-briefcase"></i>
      Browse Jobs
    </a>

    <a href="<?= BASE_URL ?>/applications" class="sidebar-link <?= navActive('/applications') ?>">
      <i class="bi bi-kanban"></i>
      My Applications
      <?php
        // Show count badge if there are active applications
        try {
          $db = \HirePilot\Core\Database::getInstance();
          $cnt = $db->fetchOne(
            "SELECT COUNT(*) AS c FROM applications WHERE user_id = :uid AND status NOT IN ('withdrawn','rejected')",
            [':uid' => Auth::id()]
          );
          if (($cnt['c'] ?? 0) > 0): ?>
            <span class="nav-badge"><?= (int)$cnt['c'] ?></span>
          <?php endif;
        } catch (\Exception $e) {}
      ?>
    </a>

    <!-- AI Tools -->
    <div class="nav-label">AI Tools</div>

    <a href="<?= BASE_URL ?>/emails" class="sidebar-link <?= navActive('/emails') ?>">
      <i class="bi bi-stars"></i>
      AI Cover Letters
    </a>

    <!-- My Account -->
    <div class="nav-label">Account</div>

    <a href="<?= BASE_URL ?>/resume" class="sidebar-link <?= navActive('/resume') ?>">
      <i class="bi bi-file-earmark-person"></i>
      My Resumes
    </a>

    <a href="<?= BASE_URL ?>/profile" class="sidebar-link <?= navActive('/profile') ?>">
      <i class="bi bi-person-circle"></i>
      Profile
    </a>

    <a href="<?= BASE_URL ?>/profile/change-password" class="sidebar-link <?= navActive('/profile/change-password') ?>">
      <i class="bi bi-shield-lock"></i>
      Security
    </a>

    <!-- Admin -->
    <?php if ($isAdmin): ?>
    <div class="nav-label">Admin</div>

    <a href="<?= BASE_URL ?>/admin" class="sidebar-link <?= navActive('/admin') ?>">
      <i class="bi bi-speedometer2"></i>
      Admin Dashboard
    </a>

    <a href="<?= BASE_URL ?>/admin/jobs" class="sidebar-link <?= navActive('/admin/jobs') ?>">
      <i class="bi bi-database-gear"></i>
      Manage Jobs
    </a>

    <a href="<?= BASE_URL ?>/admin/users" class="sidebar-link <?= navActive('/admin/users') ?>">
      <i class="bi bi-people"></i>
      Manage Users
    </a>
    <?php endif; ?>

  </nav>

  <!-- User footer -->
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <?= $initial ?>
        <?php endif; ?>
      </div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
        <div class="sidebar-user-role"><?= ucfirst($user['role'] ?? 'user') ?></div>
      </div>
      <a href="<?= BASE_URL ?>/auth/logout" class="btn-ghost btn-icon ms-auto" title="Logout">
        <i class="bi bi-box-arrow-right" style="color:#94a3b8;font-size:1.1rem"></i>
      </a>
    </div>
  </div>

</aside>

<!-- Mobile overlay -->
<div id="sidebar-overlay"></div>

