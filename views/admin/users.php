<?php
/**
 * HirePilot AI — Admin User Management View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Manage Platform Users</h2>
    <p class="page-subtitle">Review registered job seekers, administrators, and account active states</p>
  </div>
</div>

<div class="hp-card p-0">
  <div class="table-responsive">
    <table class="hp-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Headline / Target Role</th>
          <th>Location</th>
          <th>System Role</th>
          <th>Status</th>
          <th>Joined</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="sidebar-avatar" style="width: 32px; height: 32px; font-size: .75rem;">
                  <?php if (!empty($u['avatar'])): ?>
                    <img src="<?= htmlspecialchars($u['avatar']) ?>" alt="Avatar">
                  <?php else: ?>
                    <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <div class="small fw-semibold"><?= htmlspecialchars($u['target_role'] ?: ($u['headline'] ?: 'Job Seeker')) ?></div>
              <div class="small text-muted"><?= (int)($u['experience_yrs'] ?? 0) ?> yrs exp</div>
            </td>
            <td class="small text-muted"><?= htmlspecialchars($u['location'] ?: 'Unspecified') ?></td>
            <td>
              <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?> bg-opacity-10 <?= $u['role'] === 'admin' ? 'text-danger' : 'text-primary' ?> border small">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td>
              <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-secondary' ?> bg-opacity-10 <?= $u['is_active'] ? 'text-success' : 'text-secondary' ?> small">
                <?= $u['is_active'] ? 'Active' : 'Deactivated' ?>
              </span>
            </td>
            <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
            <td class="text-end">
              <?php if ($u['id'] !== \HirePilot\Core\Auth::id()): ?>
                <form action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/toggle" method="POST" class="d-inline" onsubmit="return confirm('Toggle active status for this user?');">
                  <?= Csrf::field() ?>
                  <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="Toggle status">
                    <i class="bi <?= $u['is_active'] ? 'bi-person-slash' : 'bi-person-check' ?> me-1"></i>
                    <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                  </button>
                </form>
              <?php else: ?>
                <span class="small text-muted fst-italic">You (Current Admin)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
