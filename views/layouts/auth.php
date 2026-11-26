<?php
/**
 * HirePilot AI — Auth Layout
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Flash;

$pageTitle ??= 'Sign In';
?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>

<div class="auth-wrapper">
  <div class="auth-panel-left">
    <div>
      <div class="d-flex align-items-center gap-2 mb-5">
        <div class="sidebar-brand-icon"><i class="bi bi-compass"></i></div>
        <span class="fs-5 fw-bold text-white">HirePilot AI</span>
      </div>

      <h1 class="h3 fw-bold text-white mb-3" style="line-height: 1.3;">
        Job Search &amp; Application Management
      </h1>
      <p class="text-secondary small mb-4">
        Organize your job search with skill-based matching, AI-assisted application drafting with manual review, and a structured application tracker.
      </p>

      <div class="d-flex flex-column gap-3 mt-4 small text-light">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check2-square text-primary"></i>
          <span>Multi-turn AI profile inquiry &amp; skill matching</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check2-square text-primary"></i>
          <span>Tailored email &amp; cover letter drafts with approval gate</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check2-square text-primary"></i>
          <span>7-stage application pipeline &amp; response tracking</span>
        </div>
      </div>
    </div>

    <div class="pt-3 border-top border-secondary border-opacity-25 text-secondary small d-flex justify-content-between">
      <span>Olied Ahmed Chowdhury</span>
      <span>ID: 242-134-017</span>
    </div>
  </div>

  <div class="auth-panel-right">
    <div class="auth-card">
      <?php if (Flash::has()): ?>
        <div class="mb-3"><?= Flash::render() ?></div>
      <?php endif; ?>

      <?= $content ?? '' ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
