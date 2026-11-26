<?php
/**
 * HirePilot AI — Job Detail & Apply View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$skills = !empty($job['skills_required']) ? (is_string($job['skills_required']) ? (json_decode($job['skills_required'], true) ?: []) : $job['skills_required']) : [];
?>
<div class="mb-3">
  <a href="<?= BASE_URL ?>/jobs" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to job listings</a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="hp-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div class="d-flex gap-3 align-items-center">
          <div class="job-company-logo" style="width: 50px; height: 50px;">
            <i class="bi bi-building text-secondary fs-4"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-1 h4"><?= htmlspecialchars($job['title']) ?></h3>
            <div class="text-muted small">
              <span class="fw-semibold text-dark"><?= htmlspecialchars($job['company']) ?></span> &middot;
              <span><?= htmlspecialchars($job['location'] ?: 'Remote') ?></span>
            </div>
          </div>
        </div>

        <span class="badge bg-light text-dark border px-3 py-2">
          Relevance: <?= (int) $matchScore ?>%
        </span>
      </div>

      <div class="d-flex flex-wrap gap-2 py-3 border-top border-bottom my-3">
        <span class="badge bg-light text-dark border fw-normal px-2 py-1">
          <i class="bi bi-briefcase me-1"></i> <?= ucfirst($job['job_type']) ?>
        </span>
        <span class="badge bg-light text-dark border fw-normal px-2 py-1">
          <i class="bi bi-bar-chart me-1"></i> <?= ucfirst($job['experience_level']) ?> Level
        </span>
        <?php if (!empty($job['is_remote'])): ?>
          <span class="badge bg-light text-dark border fw-normal px-2 py-1">
            <i class="bi bi-laptop me-1"></i> Remote
          </span>
        <?php endif; ?>
        <?php if (!empty($job['salary_min'])): ?>
          <span class="badge bg-light text-dark border fw-normal px-2 py-1">
            <?= number_format((float) $job['salary_min']) ?> - <?= number_format((float) $job['salary_max']) ?> <?= htmlspecialchars($job['salary_currency']) ?>
          </span>
        <?php endif; ?>
      </div>

      <div class="mb-4">
        <h6 class="fw-bold mb-2">Role Description</h6>
        <div class="text-secondary small lh-lg" style="white-space: pre-line;">
          <?= htmlspecialchars($job['description']) ?>
        </div>
      </div>

      <?php if (!empty($job['requirements'])): ?>
        <div class="mb-4">
          <h6 class="fw-bold mb-2">Requirements</h6>
          <div class="text-secondary small lh-lg" style="white-space: pre-line;">
            <?= htmlspecialchars($job['requirements']) ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($skills)): ?>
        <div>
          <h6 class="fw-bold mb-2">Required Skills</h6>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($skills as $s): ?>
              <span class="badge bg-light text-dark border fw-normal px-2 py-1"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="hp-card p-4 mb-4">
      <h6 class="fw-bold mb-3">Application Actions</h6>

      <?php if (!empty($existingApp)): ?>
        <div class="alert alert-light border small mb-3">
          <div class="fw-semibold mb-1">Application Recorded</div>
          <div>Current stage: <span class="status-pill pill-<?= htmlspecialchars($existingApp['status']) ?>"><?= ucfirst(str_replace('_', ' ', $existingApp['status'])) ?></span></div>
        </div>
        <a href="<?= BASE_URL ?>/applications/<?= $existingApp['id'] ?>" class="btn btn-outline-primary w-100 mb-2">
          Manage in Pipeline
        </a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $job['id'] ?>" class="btn btn-primary w-100 mb-2">
          <i class="bi bi-envelope-paper me-1"></i> Draft AI Application
        </a>

        <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#quickApplyModal">
          <i class="bi bi-bookmark-check me-1"></i> Track / Quick Apply
        </button>
      <?php endif; ?>

      <?php if (!empty($job['external_url'])): ?>
        <a href="<?= htmlspecialchars($job['external_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary w-100 btn-sm mt-1">
          <i class="bi bi-box-arrow-up-right me-1"></i> Open Original Listing
        </a>
      <?php endif; ?>
    </div>

    <div class="hp-card p-4">
      <h6 class="fw-bold mb-3">Listing Metadata</h6>
      <div class="small text-muted d-flex flex-column gap-2">
        <div><strong>Company:</strong> <?= htmlspecialchars($job['company']) ?></div>
        <div><strong>Location:</strong> <?= htmlspecialchars($job['location'] ?: 'Remote') ?></div>
        <?php if (!empty($job['deadline'])): ?>
          <div><strong>Deadline:</strong> <?= date('M d, Y', strtotime($job['deadline'])) ?></div>
        <?php endif; ?>
        <div><strong>Activity:</strong> <?= (int) $job['view_count'] ?> views &middot; <?= (int) $job['apply_count'] ?> tracked</div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="quickApplyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/applications" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

        <div class="modal-header">
          <h5 class="modal-title">Record Application</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label">Attach Resume</label>
            <?php if (empty($resumes)): ?>
              <div class="alert alert-light border small mb-0">
                No resume uploaded yet. <a href="<?= BASE_URL ?>/resume" class="fw-semibold">Upload a resume</a> or continue without attachment.
              </div>
            <?php else: ?>
              <select name="resume_id" class="form-select">
                <?php foreach ($resumes as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= $r['is_primary'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['label']) ?> (<?= htmlspecialchars($r['original_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Cover Letter / Notes</label>
            <textarea name="cover_letter" class="form-control" rows="4" placeholder="Paste cover letter or private notes..."></textarea>
          </div>

          <div class="mb-2">
            <label class="form-label">Initial Status</label>
            <select name="status" class="form-select">
              <option value="applied" selected>Applied</option>
              <option value="saved">Saved for Later</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Application</button>
        </div>
      </form>
    </div>
  </div>
</div>
