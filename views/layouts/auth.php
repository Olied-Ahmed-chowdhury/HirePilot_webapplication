<?php
/**
 * HirePilot AI — Auth Pages Layout
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Flash;

$pageTitle ??= 'Authentication';
?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>

<div class="auth-wrapper">
  <!-- Left branding panel -->
  <div class="auth-panel-left">
    <div>
      <div class="d-flex align-items-center gap-3 mb-5">
        <div class="sidebar-brand-icon" style="width: 44px; height: 44px; font-size: 1.3rem;">🚀</div>
        <span class="fs-4 fw-800 text-white">HirePilot<span style="color: var(--hp-primary-light);"> AI</span></span>
      </div>

      <h1 class="display-6 fw-800 text-white mb-3" style="line-height: 1.2;">
        Accelerate Your Career with <span class="text-gradient">Intelligent AI</span>.
      </h1>
      <p class="text-light opacity-75 fs-6 mb-4">
        Smart job discovery with tailored AI relevance scoring, automated email & cover letter drafting, and full application lifecycle tracking.
      </p>

      <div class="d-flex flex-column gap-3 mt-4">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
            <i class="bi bi-stars text-warning"></i>
          </div>
          <span class="text-light small">Grok AI & LLM Tailored Cover Letters & Outreach</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
            <i class="bi bi-bullseye text-info"></i>
          </div>
          <span class="text-light small">Automated Skill Matching & Relevance Scoring</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
            <i class="bi bi-kanban text-success"></i>
          </div>
          <span class="text-light small">Real-time Application Pipeline & Status Alerts</span>
        </div>
      </div>
    </div>

    <div class="pt-4 border-top border-white border-opacity-10 text-white-50 small d-flex justify-content-between align-items-center">
      <span>Author: Olied Ahmed Chowdhury</span>
      <span>ID: 242-134-017</span>
    </div>
  </div>

  <!-- Right form container -->
  <div class="auth-panel-right">
    <div class="auth-card">
      <?php if (Flash::has()): ?>
        <div class="mb-4">
          <?= Flash::render() ?>
        </div>
      <?php endif; ?>

      <?= $content ?? '' ?>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
