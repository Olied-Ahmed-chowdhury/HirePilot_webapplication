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
      <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="stat-icon violet" style="width: 52px; height: 52px; font-size: 1.5rem;">
          <i class="bi bi-stars"></i>
        </div>
        <div>
          <h4 class="fw-800 mb-0">Generate Tailored Application with AI</h4>
          <p class="text-muted small mb-0">Powered by Grok AI LLM & Candidate Resume Matching Engine</p>
        </div>
      </div>

      <form action="<?= BASE_URL ?>/emails/generate" method="POST" id="ai-draft-form">
        <?= Csrf::field() ?>

        <!-- Target Job Selection -->
        <div class="mb-3">
          <label class="form-label">Select Target Job Opportunity *</label>
          <select name="job_id" id="job_id" class="form-select" required>
            <option value="">-- Choose Job Listing --</option>
            <?php foreach ($jobs as $j): ?>
              <option value="<?= $j['id'] ?>" <?= ($selectedJob && $selectedJob['id'] === $j['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($j['title']) ?> &bull; <?= htmlspecialchars($j['company']) ?> (<?= htmlspecialchars($j['location'] ?: 'Remote') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Candidate Resume Selection -->
        <div class="mb-3">
          <label class="form-label">Attach Resume Context</label>
          <?php if (empty($resumes)): ?>
            <div class="alert alert-light border small text-muted">
              <i class="bi bi-info-circle text-primary me-1"></i> No resume uploaded. Your account profile & skills will be used as background context.
              <a href="<?= BASE_URL ?>/resume" class="fw-bold ms-1">Upload a resume</a> for even richer AI personalization.
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
          <!-- Document Type -->
          <div class="col-md-6">
            <label class="form-label">Application Format *</label>
            <select name="draft_type" class="form-select" required>
              <option value="cover_letter" selected>📄 Comprehensive Cover Letter</option>
              <option value="application_email">✉️ Tailored Direct Outreach Email</option>
            </select>
          </div>

          <!-- Tone of Voice -->
          <div class="col-md-6">
            <label class="form-label">Tone of Voice</label>
            <select name="tone" class="form-select">
              <option value="professional" selected>👔 Professional & Confident (Standard)</option>
              <option value="enthusiastic">🔥 Enthusiastic & Dynamic</option>
              <option value="concise">⚡ Concise & High-Impact (For Busy Execs)</option>
              <option value="technical">💻 Deep Technical & Architecture Focused</option>
            </select>
          </div>
        </div>

        <div class="p-3 bg-light rounded-3 border mb-4 small text-muted">
          <i class="bi bi-lightbulb-fill text-warning me-1"></i>
          <strong>How it works:</strong> The AI extracts your skills, headline, experience, and resume excerpts, comparing them against the target role's requirements to construct an authentic, bespoke application that stands out to hiring managers.
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="<?= BASE_URL ?>/emails" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary px-4 py-2" id="generate-submit-btn">
            <i class="bi bi-magic me-1"></i> Generate AI Draft
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('ai-draft-form').addEventListener('submit', function() {
  const btn = document.getElementById('generate-submit-btn');
  btnLoading(btn, true);
  btn.innerHTML = '<span class="hp-spinner me-2"></span> Generating with AI...';
});
</script>
