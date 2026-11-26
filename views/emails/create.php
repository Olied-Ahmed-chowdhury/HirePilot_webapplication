<?php
/**
 * HirePilot AI — Generate AI Draft View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="mb-3">
  <a href="<?= BASE_URL ?>/emails" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to drafts</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="hp-card p-4">
      <div class="mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1">Generate Application Draft</h4>
        <p class="text-muted small mb-0">Creates a tailored cover letter or email from your profile, resume, and job description</p>
      </div>

      <form action="<?= BASE_URL ?>/emails/generate" method="POST" id="ai-draft-form">
        <?= Csrf::field() ?>

        <div class="mb-3">
          <label class="form-label">Target Job *</label>
          <select name="job_id" id="job_id" class="form-select" required>
            <option value="">-- Select Job Listing --</option>
            <?php foreach ($jobs as $j): ?>
              <option value="<?= $j['id'] ?>" <?= ($selectedJob && $selectedJob['id'] === $j['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($j['title']) ?> &mdash; <?= htmlspecialchars($j['company']) ?> (<?= htmlspecialchars($j['location'] ?: 'Remote') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Resume Context</label>
          <?php if (empty($resumes)): ?>
            <div class="alert alert-light border small text-muted">
              No resume uploaded. Your profile skills and career summary will be used.
              <a href="<?= BASE_URL ?>/resume" class="fw-semibold ms-1">Upload a resume</a>
            </div>
          <?php else: ?>
            <select name="resume_id" id="resume_id" class="form-select">
              <?php foreach ($resumes as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $r['is_primary'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r['label']) ?> (<?= htmlspecialchars($r['original_name']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Draft Type *</label>
            <select name="draft_type" class="form-select" required>
              <option value="cover_letter" selected>Cover Letter</option>
              <option value="application_email">Direct Application Email</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Tone</label>
            <select name="tone" class="form-select">
              <option value="professional" selected>Professional &amp; Direct</option>
              <option value="concise">Concise</option>
              <option value="technical">Technical</option>
              <option value="enthusiastic">Enthusiastic</option>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="<?= BASE_URL ?>/emails" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" id="generate-submit-btn">
            <i class="bi bi-file-earmark-plus me-1"></i> Generate Draft
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('ai-draft-form').addEventListener('submit', function() {
  const btn = document.getElementById('generate-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="hp-spinner me-2"></span> Generating...';
});
</script>
