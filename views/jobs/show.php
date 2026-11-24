<?php
/**
 * HirePilot AI — Job Detail & Apply View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$skills = !empty($job['skills_required']) ? (is_string($job['skills_required']) ? json_decode($job['skills_required'], true) : $job['skills_required']) : [];
?>
<div class="mb-3">
  <a href="<?= BASE_URL ?>/jobs" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to all jobs</a>
</div>

<div class="row g-4">
  <!-- Main Job Details Column -->
  <div class="col-lg-8">
    <div class="hp-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div class="d-flex gap-3 align-items-center">
          <div class="job-company-logo" style="width: 58px; height: 58px; font-size: 1.6rem;">
            <i class="bi bi-building text-primary"></i>
          </div>
          <div>
            <h3 class="fw-800 mb-1"><?= htmlspecialchars($job['title']) ?></h3>
            <div class="text-muted fs-6">
              <span class="fw-bold text-dark"><?= htmlspecialchars($job['company']) ?></span> &bull;
              <span><?= htmlspecialchars($job['location'] ?: 'Remote') ?></span>
            </div>
          </div>
        </div>

        <span class="badge <?= $matchScore >= 75 ? 'bg-success' : ($matchScore >= 50 ? 'bg-primary' : 'bg-warning text-dark') ?> rounded-pill px-3 py-2 fs-6">
          <i class="bi bi-stars me-1"></i> <?= $matchScore ?>% AI Match
        </span>
      </div>

      <div class="d-flex flex-wrap gap-2 py-3 border-top border-bottom my-3">
        <span class="badge bg-light text-dark border px-3 py-2">
          <i class="bi bi-briefcase me-1 text-primary"></i> <?= ucfirst($job['job_type']) ?>
        </span>
        <span class="badge bg-light text-dark border px-3 py-2">
          <i class="bi bi-speedometer2 me-1 text-info"></i> <?= ucfirst($job['experience_level']) ?> Level
        </span>
        <?php if ($job['is_remote']): ?>
          <span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2">
            <i class="bi bi-wifi me-1"></i> 100% Remote
          </span>
        <?php endif; ?>
        <?php if (!empty($job['salary_min'])): ?>
          <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
            <i class="bi bi-cash-stack me-1"></i> <?= number_format((float)$job['salary_min']) ?> - <?= number_format((float)$job['salary_max']) ?> <?= htmlspecialchars($job['salary_currency']) ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div class="mb-4">
        <h5 class="fw-bold mb-3">Job Description</h5>
        <div class="text-secondary lh-lg" style="white-space: pre-line;">
          <?= htmlspecialchars($job['description']) ?>
        </div>
      </div>

      <!-- Requirements -->
      <?php if (!empty($job['requirements'])): ?>
        <div class="mb-4">
          <h5 class="fw-bold mb-3">Requirements & Qualifications</h5>
          <div class="text-secondary lh-lg" style="white-space: pre-line;">
            <?= htmlspecialchars($job['requirements']) ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Benefits -->
      <?php if (!empty($job['benefits'])): ?>
        <div class="mb-4">
          <h5 class="fw-bold mb-3">Perks & Benefits</h5>
          <div class="text-secondary lh-lg" style="white-space: pre-line;">
            <?= htmlspecialchars($job['benefits']) ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Skills Required -->
      <?php if (!empty($skills)): ?>
        <div class="mb-2">
          <h6 class="fw-bold mb-2">Required Skills & Technologies</h6>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($skills as $s): ?>
              <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                <?= htmlspecialchars($s) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Action Sidebar -->
  <div class="col-lg-4">
    <!-- Application Action Card -->
    <div class="hp-card p-4 mb-4">
      <h5 class="fw-bold mb-3">Ready to Apply?</h5>

      <?php if (!empty($existingApp)): ?>
        <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-check-circle-fill fs-5"></i>
          <div>
            <div class="fw-bold small">Already Applied!</div>
            <div style="font-size: .75rem;">Status: <span class="status-pill pill-<?= $existingApp['status'] ?>"><?= ucfirst(str_replace('_', ' ', $existingApp['status'])) ?></span></div>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/applications" class="btn btn-outline-primary w-100 mb-2">
          Track in Application Pipeline
        </a>
      <?php else: ?>
        <p class="small text-muted mb-3">
          Use HirePilot AI to generate a tailored cover letter and outreach email directly aligned with this role's requirements.
        </p>

        <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $job['id'] ?>" class="btn btn-primary w-100 py-2 mb-2 d-flex align-items-center justify-content-center gap-2 shadow">
          <i class="bi bi-stars"></i> Generate AI Cover Letter
        </a>

        <button type="button" class="btn btn-outline-secondary w-100 py-2" data-bs-toggle="modal" data-bs-target="#quickApplyModal">
          <i class="bi bi-send me-1"></i> Quick Apply / Save
        </button>
      <?php endif; ?>
    </div>

    <!-- Company Information Card -->
    <div class="hp-card p-4">
      <h6 class="fw-bold mb-3">About the Company</h6>
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-buildings text-primary"></i>
        <span class="fw-bold"><?= htmlspecialchars($job['company']) ?></span>
      </div>
      <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
        <i class="bi bi-geo-alt text-primary"></i>
        <span><?= htmlspecialchars($job['location'] ?: 'Remote') ?></span>
      </div>
      <?php if (!empty($job['deadline'])): ?>
        <div class="d-flex align-items-center gap-2 text-muted small mb-2">
          <i class="bi bi-calendar-event text-danger"></i>
          <span>Apply before: <?= date('M d, Y', strtotime($job['deadline'])) ?></span>
        </div>
      <?php endif; ?>
      <div class="d-flex align-items-center gap-2 text-muted small">
        <i class="bi bi-eye text-info"></i>
        <span><?= (int)$job['view_count'] ?> views &bull; <?= (int)$job['apply_count'] ?> applications</span>
      </div>
    </div>
  </div>
</div>

<!-- Quick Apply Modal -->
<div class="modal fade" id="quickApplyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/applications" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

        <div class="modal-header">
          <h5 class="modal-title">Apply for <?= htmlspecialchars($job['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label">Attach Resume *</label>
            <?php if (empty($resumes)): ?>
              <div class="alert alert-warning small mb-0">
                You haven't uploaded a resume yet. <a href="<?= BASE_URL ?>/resume" class="fw-bold">Upload one now</a>.
              </div>
            <?php else: ?>
              <select name="resume_id" class="form-select" required>
                <?php foreach ($resumes as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= $r['is_primary'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['label']) ?> (<?= htmlspecialchars($r['original_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Cover Letter / Notes (Optional)</label>
            <textarea name="cover_letter" class="form-control" rows="4" placeholder="Brief note to the hiring manager or private application notes..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Initial Status</label>
            <select name="status" class="form-select">
              <option value="applied" selected>Mark as Applied</option>
              <option value="saved">Save for Later</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Submit Application</button>
        </div>
      </form>
    </div>
  </div>
</div>
