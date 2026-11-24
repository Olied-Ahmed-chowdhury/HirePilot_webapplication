<?php
/**
 * HirePilot AI — Security Settings View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="hp-card">
      <div class="hp-card-header">
        <h5 class="hp-card-title"><i class="bi bi-shield-lock-fill me-2 text-primary"></i> Change Account Password</h5>
      </div>
      <div class="hp-card-body p-4">
        <form action="<?= BASE_URL ?>/profile/change-password" method="POST">
          <?= Csrf::field() ?>

          <?php if (!empty($user['password_hash'])): ?>
          <div class="mb-3">
            <label class="form-label">Current Password *</label>
            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
          </div>
          <?php else: ?>
          <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-google me-1"></i> You signed in via Google OAuth. You can set a password here to enable email/password sign-in as well.
          </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">New Password *</label>
            <input type="password" name="new_password" class="form-control" placeholder="Min. 8 characters with 1 capital and 1 number" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-secondary">Back to Profile</a>
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-key me-1"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
