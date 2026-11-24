<?php
/**
 * HirePilot AI — Login View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="auth-logo d-lg-none">
  <div class="auth-logo-icon">🚀</div>
  <span class="fs-4 fw-800">HirePilot<span class="text-primary"> AI</span></span>
</div>

<h2 class="auth-title">Welcome back</h2>
<p class="auth-subtitle">Sign in to manage your jobs and AI applications</p>

<!-- Google OAuth Sign-In -->
<a href="<?= BASE_URL ?>/auth/google" class="btn-google mb-3">
  <svg width="20" height="20" viewBox="0 0 48 48">
    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
  </svg>
  Continue with Google
</a>

<div class="auth-divider">or sign in with email</div>

<form action="<?= BASE_URL ?>/auth/login" method="POST" id="login-form">
  <?= Csrf::field() ?>

  <div class="mb-3">
    <label for="email" class="form-label">Email Address</label>
    <div class="input-group-icon">
      <i class="bi bi-envelope"></i>
      <input type="email" class="form-control" id="email" name="email"
             placeholder="name@example.com" required autocomplete="email" autofocus>
    </div>
  </div>

  <div class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
      <label for="password" class="form-label">Password</label>
    </div>
    <div class="input-group-icon">
      <i class="bi bi-lock"></i>
      <input type="password" class="form-control" id="password" name="password"
             placeholder="••••••••" required autocomplete="current-password">
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 mt-2 mb-3" id="login-btn">
    Sign In
  </button>
</form>

<!-- Demo Credentials Autofill Helper -->
<div class="p-3 bg-light rounded-3 border mb-3">
  <div class="small fw-700 text-muted mb-2"><i class="bi bi-key-fill text-primary me-1"></i> Quick Demo Logins:</div>
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
  <a href="<?= BASE_URL ?>/auth/register" class="fw-bold">Create account</a>
</p>

<script>
function fillCreds(email, pass) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = pass;
}
</script>
