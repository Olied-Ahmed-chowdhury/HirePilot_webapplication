<?php
/**
 * HirePilot AI — Layout: Top Navbar
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;

$user    = Auth::user();
$initial = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
$navbarTitle ??= 'Dashboard';

// Unread notification count
$unreadCount = 0;
try {
  $db = \HirePilot\Core\Database::getInstance();
  $row = $db->fetchOne(
    "SELECT COUNT(*) AS c FROM notifications WHERE user_id = :uid AND is_read = 0",
    [':uid' => Auth::id()]
  );
  $unreadCount = (int)($row['c'] ?? 0);
} catch (\Exception $e) {}
?>
<!-- Top Navbar -->
<header class="app-navbar">

  <!-- Mobile sidebar toggle -->
  <button class="navbar-toggle" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <!-- Page title -->
  <div class="navbar-title"><?= htmlspecialchars($navbarTitle) ?></div>

  <!-- Search bar -->
  <div class="navbar-search d-none d-lg-block">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search jobs, applications…"
           id="global-search" autocomplete="off">
  </div>

  <!-- Action buttons -->
  <div class="navbar-actions">

    <!-- Notifications dropdown -->
    <div class="dropdown">
      <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" id="notif-btn">
        <i class="bi bi-bell"></i>
        <?php if ($unreadCount > 0): ?>
          <span class="notif-badge" id="notif-badge"><?= $unreadCount ?></span>
        <?php else: ?>
          <span class="notif-badge" id="notif-badge" style="display:none"></span>
        <?php endif; ?>
      </button>

      <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0" id="notif-dropdown">
        <div class="notif-header">
          <span>Notifications</span>
          <?php if ($unreadCount > 0): ?>
            <a href="<?= BASE_URL ?>/notifications/read-all" class="text-primary" style="font-size:.8rem;font-weight:600"
               id="mark-all-read">Mark all read</a>
          <?php endif; ?>
        </div>
        <div id="notif-list" style="max-height:340px;overflow-y:auto">
          <div class="text-center text-muted py-3" style="font-size:.84rem">Loading…</div>
        </div>
        <div class="p-2 text-center border-top">
          <a href="<?= BASE_URL ?>/notifications" class="text-primary" style="font-size:.8rem;font-weight:600">View all notifications</a>
        </div>
      </div>
    </div>

    <!-- User avatar dropdown -->
    <div class="dropdown">
      <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" style="width:auto;gap:.5rem;padding:.3rem .6rem">
        <div class="sidebar-avatar" style="width:32px;height:32px;font-size:.75rem">
          <?php if (!empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= $initial ?>
          <?php endif; ?>
        </div>
        <i class="bi bi-chevron-down" style="font-size:.7rem;color:var(--hp-text-muted)"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="border-radius:12px;border:1px solid var(--hp-border);box-shadow:0 8px 30px rgba(0,0,0,.1);min-width:200px;padding:.5rem">
        <li>
          <div class="px-3 py-2">
            <div class="fw-700" style="font-size:.875rem"><?= htmlspecialchars($user['name'] ?? '') ?></div>
            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($user['email'] ?? '') ?></div>
          </div>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item rounded-2" href="<?= BASE_URL ?>/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><a class="dropdown-item rounded-2" href="<?= BASE_URL ?>/profile/change-password"><i class="bi bi-shield-lock me-2"></i>Security</a></li>
        <?php if (Auth::isAdmin()): ?>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item rounded-2" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
        <?php endif; ?>
        <li><hr class="dropdown-divider my-1"></li>
        <li>
          <a class="dropdown-item rounded-2 text-danger" href="<?= BASE_URL ?>/auth/logout">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</header>

