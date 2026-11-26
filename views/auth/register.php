<?php
/**
 * HirePilot AI — Registration View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
  <div class="sidebar-brand-icon"><i class="bi bi-compass"></i></div>
  <span class="fs-5 fw-bold">HirePilot AI</span>
</div>

<h2 class="auth-title">Create account</h2>
<p class="auth-subtitle">Register to start tracking applications and matching jobs</p>

<form action="<?= BASE_URL ?>/auth/register" method="POST" id="register-form">
  <?= Csrf::field() ?>

  <div class="mb-3">
    <label for="name" class="form-label">Full Name</label>
    <div class="input-group-icon">
      <i class="bi bi-person"></i>
      <input type="text" class="form-control" id="name" name="name"
             placeholder="Olied Ahmed Chowdhury" required autofocus>
    </div>
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <div class="input-group-icon">
      <i class="bi bi-envelope"></i>
      <input type="email" class="form-control" id="email" name="email"
             placeholder="name@example.com" required autocomplete="email">
    </div>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <div class="input-group-icon">
      <i class="bi bi-lock"></i>
      <input type="password" class="form-control" id="password" name="password"
             placeholder="Min 8 chars, 1 uppercase, 1 number" required autocomplete="new-password">
    </div>
  </div>

  <div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm Password</label>
    <div class="input-group-icon">
      <i class="bi bi-shield-check"></i>
      <input type="password" class="form-control" id="password_confirm" name="password_confirm"
             placeholder="Re-enter password" required autocomplete="new-password">
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 mt-1 mb-3">
    Create Account
  </button>
</form>

<p class="text-center text-muted small mb-0">
  Already have an account?
  <a href="<?= BASE_URL ?>/auth/login" class="fw-semibold">Sign in</a>
</p>
