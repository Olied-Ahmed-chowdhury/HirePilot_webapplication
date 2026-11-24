<?php
/**
 * HirePilot AI — Draft Detail, Edit, Approval & Send View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="mb-3 d-flex justify-content-between align-items-center">
  <a href="<?= BASE_URL ?>/emails" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to all drafts</a>
  <div class="d-flex align-items-center gap-2">
    <?php if ($draft['status'] === 'sent'): ?>
      <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check2-all me-1"></i> Sent Successfully</span>
    <?php elseif ($draft['status'] === 'approved'): ?>
      <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Approved by You</span>
    <?php else: ?>
      <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-pencil me-1"></i> Under Review / Draft</span>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <!-- Left Column: Draft Editor & Content -->
  <div class="col-lg-8">
    <div class="hp-card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
          <i class="bi <?= $draft['draft_type'] === 'cover_letter' ? 'bi-file-earmark-text text-primary' : 'bi-envelope text-info' ?> me-2"></i>
          <?= $draft['draft_type'] === 'cover_letter' ? 'Cover Letter' : 'Direct Application Email' ?>
        </h5>
        <span class="badge bg-light text-muted border small"><?= htmlspecialchars(explode(' ', $draft['ai_model_used'] ?? 'grok')[0]) ?></span>
      </div>

      <form action="<?= BASE_URL ?>/emails/<?= $draft['id'] ?>/update" method="POST">
        <?= Csrf::field() ?>

        <div class="mb-3">
          <label class="form-label">Subject Line</label>
          <input type="text" name="subject" class="form-control fw-bold" value="<?= htmlspecialchars($draft['subject'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Recipient Email</label>
          <input type="email" name="recipient_email" class="form-control" placeholder="hiring@company.com" value="<?= htmlspecialchars($draft['recipient_email'] ?? '') ?>">
        </div>

        <div class="mb-4">
          <label class="form-label">Application Body (Editable)</label>
          <textarea name="body" class="form-control font-monospace fs-6" rows="14" style="line-height: 1.6;" required><?= htmlspecialchars($draft['body']) ?></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.querySelector('[name=body]').value); Toast.success('Copied to clipboard!');">
            <i class="bi bi-clipboard me-1"></i> Copy Text
          </button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Right Column: Approval & Transmission Actions -->
  <div class="col-lg-4">
    <!-- Approval & Send Card -->
    <div class="hp-card p-4 mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-primary me-2"></i> Approval & Transmission</h6>

      <?php if ($draft['status'] === 'draft'): ?>
        <p class="small text-muted mb-3">
          Review and fine-tune your application text on the left. Once you're satisfied, approve the draft to enable sending.
        </p>
        <form action="<?= BASE_URL ?>/emails/<?= $draft['id'] ?>/approve" method="POST" class="mb-3">
          <?= Csrf::field() ?>
          <button type="submit" class="btn btn-success w-100 py-2">
            <i class="bi bi-check2-circle me-1"></i> Approve Draft
          </button>
        </form>
      <?php else: ?>
        <div class="alert alert-success small mb-3">
          <i class="bi bi-check-circle-fill me-1"></i> This draft has been reviewed and approved.
        </div>
      <?php endif; ?>

      <!-- Send Section -->
      <form action="<?= BASE_URL ?>/emails/<?= $draft['id'] ?>/send" method="POST" onsubmit="return confirm('Send this application now?');">
        <?= Csrf::field() ?>
        <div class="mb-3">
          <label class="form-label small">Target Recipient Email *</label>
          <input type="email" name="recipient_email" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($draft['recipient_email'] ?? '') ?>" placeholder="hr@company.com" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 <?= $draft['status'] === 'draft' ? 'disabled' : '' ?>" <?= $draft['status'] === 'draft' ? 'disabled' : '' ?>>
          <i class="bi bi-send-fill me-1"></i> <?= $draft['status'] === 'sent' ? 'Resend Application' : 'Send Application Now' ?>
        </button>
        <div class="form-text small mt-2">
          Logs safely to <code>storage/logs/mail.log</code> if running in test/log mode.
        </div>
      </form>
    </div>

    <!-- Job & Context Reference Card -->
    <div class="hp-card p-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-briefcase text-secondary me-2"></i> Opportunity Reference</h6>
      <div class="fw-bold"><?= htmlspecialchars($draft['job_title'] ?: 'Custom Opportunity') ?></div>
      <div class="small text-muted mb-3"><?= htmlspecialchars($draft['job_company'] ?: 'N/A') ?></div>

      <?php if (!empty($draft['resume_label'])): ?>
        <div class="small text-muted border-top pt-2">
          <i class="bi bi-file-earmark-person text-primary me-1"></i> Resume: <strong><?= htmlspecialchars($draft['resume_label']) ?></strong>
        </div>
      <?php endif; ?>

      <?php if (!empty($draft['sent_at'])): ?>
        <div class="small text-success border-top pt-2 mt-2">
          <i class="bi bi-clock-history me-1"></i> Sent at: <?= date('M d, Y H:i', strtotime($draft['sent_at'])) ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
