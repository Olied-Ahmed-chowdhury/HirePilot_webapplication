<?php
/**
 * HirePilot AI — Login View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
  <div class="sidebar-brand-icon"><i class="bi bi-compass"></i></div>
  <span class="fs-5 fw-bold">HirePilot AI</span>
</div>

<h2 class="auth-title">Sign in</h2>
<p class="auth-subtitle">Enter your credentials to access your workspace</p>

<form action="<?= BASE_URL ?>/auth/login" method="POST" id="login-form">
  <?= Csrf::field() ?>

  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <div class="input-group-icon">
      <i class="bi bi-envelope"></i>
      <input type="email" class="form-control" id="email" name="email"
             placeholder="name@example.com" required autocomplete="email" autofocus>
    </div>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <div class="input-group-icon">
      <i class="bi bi-lock"></i>
      <input type="password" class="form-control" id="password" name="password"
             placeholder="Enter password" required autocomplete="current-password">
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 mt-1 mb-3" id="login-btn">
    Sign In
  </button>
</form>

<div class="p-3 bg-light rounded border mb-3">
  <div class="small fw-semibold text-muted mb-2">Demo Accounts</div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="fillCreds('demo@hirepilot.ai', 'Demo@1234')">
      Candidate Demo
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="fillCreds('admin@hirepilot.ai', 'Admin@1234')">
      Admin Demo
    </button>
  </div>
</div>

<p class="text-center text-muted small mb-0">
  Don't have an account?
  <a href="<?= BASE_URL ?>/auth/register" class="fw-semibold">Create account</a>
</p>

<script>
function fillCreds(email, pass) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = pass;
}
</script>
