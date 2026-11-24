<?php
/**
 * HirePilot AI — Resume Management View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Resume Management</h2>
    <p class="page-subtitle">Upload and maintain your resumes for AI-powered job application drafting</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload New Resume
  </button>
</div>

<?php if (empty($resumes)): ?>
  <div class="hp-card p-5 text-center">
    <div class="empty-icon"><i class="bi bi-file-earmark-person text-muted" style="font-size: 3.5rem;"></i></div>
    <h4 class="fw-bold mt-2">No resumes uploaded yet</h4>
    <p class="text-muted mx-auto" style="max-width: 480px;">
      Upload your resume in PDF or DOCX format. HirePilot AI will parse your skills and experience to craft high-converting cover letters and tailored emails.
    </p>
    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
      Upload Your First Resume
    </button>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($resumes as $res): ?>
      <div class="col-md-6 col-lg-4">
        <div class="hp-card h-100 d-flex flex-column justify-content-between p-4 <?= $res['is_primary'] ? 'border-primary' : '' ?>">
          <div>
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="rounded-3 p-3 bg-light text-primary fs-3">
                <i class="bi <?= str_contains($res['mime_type'], 'pdf') ? 'bi-filetype-pdf text-danger' : 'bi-file-earmark-word text-primary' ?>"></i>
              </div>
              <?php if ($res['is_primary']): ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 rounded-pill">
                  <i class="bi bi-star-fill me-1 text-warning"></i> Default Primary
                </span>
              <?php endif; ?>
            </div>

            <h5 class="fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($res['label']) ?>">
              <?= htmlspecialchars($res['label']) ?>
            </h5>
            <div class="small text-muted mb-2 text-truncate" title="<?= htmlspecialchars($res['original_name']) ?>">
              <i class="bi bi-paperclip"></i> <?= htmlspecialchars($res['original_name']) ?>
            </div>

            <div class="small text-muted mb-3">
              <span><?= number_format($res['file_size'] / 1024, 1) ?> KB</span> &bull;
              <span>Uploaded <?= date('M d, Y', strtotime($res['created_at'])) ?></span>
            </div>

            <?php if (!empty($res['parsed_text'])): ?>
              <div class="p-2 rounded bg-light border text-muted small mb-3" style="max-height: 80px; overflow-y: hidden; text-overflow: ellipsis;">
                <i class="bi bi-robot text-primary me-1"></i> <strong>Extracted text preview:</strong><br>
                <?= htmlspecialchars(mb_substr($res['parsed_text'], 0, 140)) ?>...
              </div>
            <?php endif; ?>
          </div>

          <div class="pt-3 border-top d-flex gap-2 justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/resume/<?= $res['id'] ?>/download" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-download me-1"></i> Download
            </a>

            <div class="d-flex gap-2">
              <?php if (!$res['is_primary']): ?>
                <form action="<?= BASE_URL ?>/resume/<?= $res['id'] ?>/primary" method="POST" class="d-inline">
                  <?= Csrf::field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-primary" title="Set as primary resume">
                    Make Primary
                  </button>
                </form>
              <?php endif; ?>

              <form action="<?= BASE_URL ?>/resume/<?= $res['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this resume?');">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete resume">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Upload Resume Modal -->
<div class="modal fade" id="uploadResumeModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/resume/upload" method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>

        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel"><i class="bi bi-cloud-arrow-up text-primary me-2"></i> Upload Resume</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label">Resume Title / Label</label>
            <input type="text" name="label" class="form-control" placeholder="e.g. Senior Backend Resume 2026">
            <div class="form-text">Optional custom label to easily identify this version.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Select File (PDF, DOCX, DOC - Max 5MB) *</label>
            <input type="file" name="resume_file" id="resume_file" class="form-control" accept=".pdf,.docx,.doc" required>
            <div id="file-preview-area"></div>
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary_check" checked>
            <label class="form-check-label" for="is_primary_check">
              Set as primary resume for job applications & AI drafting
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-upload me-1"></i> Upload & Parse
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initFileUpload('resume_file', 'file-preview-area');
});
</script>
