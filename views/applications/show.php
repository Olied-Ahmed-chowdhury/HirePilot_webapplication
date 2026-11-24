<?php
/**
 * HirePilot AI — Application Detail & Tracking View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="mb-3 d-flex justify-content-between align-items-center">
  <a href="<?= BASE_URL ?>/applications" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to all applications</a>
  <span class="status-pill pill-<?= htmlspecialchars($app['status']) ?> fs-6 py-2 px-3">
    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
  </span>
</div>

<div class="row g-4">
  <!-- Left Column: Details & Editing Notes -->
  <div class="col-lg-8">
    <!-- Application Management Form -->
    <div class="hp-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h4 class="fw-800 mb-1"><?= htmlspecialchars($app['job_title']) ?></h4>
          <div class="text-muted fs-6">
            <span class="fw-bold text-dark"><?= htmlspecialchars($app['job_company']) ?></span> &bull;
            <span><?= htmlspecialchars($app['job_location'] ?: 'Remote') ?></span>
          </div>
        </div>
      </div>

      <form action="<?= BASE_URL ?>/applications/<?= $app['id'] ?>/details" method="POST">
        <?= Csrf::field() ?>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Application Pipeline Stage</label>
            <select name="status" class="form-select">
              <option value="saved" <?= $app['status'] === 'saved' ? 'selected' : '' ?>>Saved</option>
              <option value="applied" <?= $app['status'] === 'applied' ? 'selected' : '' ?>>Applied</option>
              <option value="under_review" <?= $app['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
              <option value="interview_scheduled" <?= $app['status'] === 'interview_scheduled' ? 'selected' : '' ?>>Interview Scheduled</option>
              <option value="offer_received" <?= $app['status'] === 'offer_received' ? 'selected' : '' ?>>Offer Received</option>
              <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
              <option value="withdrawn" <?= $app['status'] === 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Interview Date / Time</label>
            <input type="datetime-local" name="interview_date" class="form-control"
                   value="<?= !empty($app['interview_date']) ? date('Y-m-d\TH:i', strtotime($app['interview_date'])) : '' ?>">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Linked Resume</label>
            <select name="resume_id" class="form-select">
              <option value="">-- None Linked --</option>
              <?php foreach ($resumes as $r): ?>
                <option value="<?= $r['id'] ?>" <?= ($app['resume_id'] == $r['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r['label']) ?> (<?= htmlspecialchars($r['original_name']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Offer Amount (Optional)</label>
            <input type="number" step="0.01" name="offer_amount" class="form-control" placeholder="e.g. 80000"
                   value="<?= !empty($app['offer_amount']) ? htmlspecialchars((string)$app['offer_amount']) : '' ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Private Applicant Notes</label>
          <textarea name="custom_notes" class="form-control" rows="3" placeholder="Key contact names, salary discussion, referral information, etc..."><?= htmlspecialchars($app['custom_notes'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label">Company Feedback & Response Log</label>
          <textarea name="company_response" class="form-control" rows="3" placeholder="Paste HR responses, take-home challenge details, feedback received..."><?= htmlspecialchars($app['company_response'] ?? '') ?></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Save Tracking Updates
          </button>
        </div>
      </form>
    </div>

    <!-- Submitted Cover Letter Preview -->
    <?php if (!empty($app['cover_letter'])): ?>
      <div class="hp-card p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i> Submitted Cover Letter</h6>
        <div class="p-3 bg-light rounded-3 text-secondary lh-lg font-monospace fs-6" style="white-space: pre-line;">
          <?= htmlspecialchars($app['cover_letter']) ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right Column: Meta Info & Actions -->
  <div class="col-lg-4">
    <!-- Meta Info Card -->
    <div class="hp-card p-4 mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Timeline & Activity</h6>
      <div class="small text-muted mb-2">
        <strong>First Recorded:</strong> <?= date('M d, Y H:i', strtotime($app['created_at'])) ?>
      </div>
      <div class="small text-muted mb-2">
        <strong>Last Updated:</strong> <?= date('M d, Y H:i', strtotime($app['updated_at'])) ?>
      </div>
      <?php if (!empty($app['applied_at'])): ?>
        <div class="small text-success mb-2">
          <strong>Applied Date:</strong> <?= date('M d, Y', strtotime($app['applied_at'])) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($app['resume_label'])): ?>
        <div class="small text-muted mb-2">
          <strong>Attached Resume:</strong> <?= htmlspecialchars($app['resume_label']) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick AI Draft Link -->
    <div class="hp-card p-4 mb-4">
      <h6 class="fw-bold mb-2">Need a follow-up email?</h6>
      <p class="small text-muted mb-3">Generate a tailored follow-up or interview thank-you email using Grok AI.</p>
      <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $app['job_id'] ?>" class="btn btn-outline-primary w-100 btn-sm">
        <i class="bi bi-stars me-1"></i> Generate Follow-up Draft
      </a>
    </div>

    <!-- Danger Zone -->
    <div class="hp-card p-4 border-danger border-opacity-25">
      <h6 class="fw-bold text-danger mb-2">Danger Zone</h6>
      <p class="small text-muted mb-3">Permanently delete this application from your tracking board.</p>
      <form action="<?= BASE_URL ?>/applications/<?= $app['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this application record?');">
        <?= Csrf::field() ?>
        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
          <i class="bi bi-trash me-1"></i> Delete Application
        </button>
      </form>
    </div>
  </div>
</div>
