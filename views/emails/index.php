<?php
/**
 * HirePilot AI — Email Drafts List View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Application Drafts</h2>
    <p class="page-subtitle">Review, edit, approve, and send generated cover letters and emails</p>
  </div>
  <a href="<?= BASE_URL ?>/emails/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> New Draft
  </a>
</div>

<?php if (empty($drafts)): ?>
  <div class="hp-card p-5 text-center">
    <div class="empty-icon"><i class="bi bi-envelope-paper"></i></div>
    <div class="empty-title">No drafts created yet</div>
    <div class="empty-desc mb-3">Generate a tailored cover letter or application email for any job opening.</div>
    <a href="<?= BASE_URL ?>/emails/create" class="btn btn-primary btn-sm">Create First Draft</a>
  </div>
<?php else: ?>
  <div class="hp-card p-0">
    <div class="table-responsive">
      <table class="hp-table">
        <thead>
          <tr>
            <th>Target Role</th>
            <th>Format</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Created</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($drafts as $d): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($d['job_title'] ?: 'General Application') ?></div>
                <div class="small text-muted"><?= htmlspecialchars($d['job_company'] ?: 'Direct Outreach') ?></div>
              </td>
              <td>
                <span class="badge bg-light text-dark border fw-normal">
                  <?= $d['draft_type'] === 'cover_letter' ? 'Cover Letter' : 'Application Email' ?>
                </span>
              </td>
              <td class="text-truncate" style="max-width: 260px;">
                <?= htmlspecialchars($d['subject'] ?: 'Untitled') ?>
              </td>
              <td>
                <?php if ($d['status'] === 'sent'): ?>
                  <span class="status-pill pill-offer_received">Sent</span>
                <?php elseif ($d['status'] === 'approved'): ?>
                  <span class="status-pill pill-active">Approved</span>
                <?php else: ?>
                  <span class="status-pill pill-under_review">Draft</span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
              <td class="text-end text-nowrap">
                <a href="<?= BASE_URL ?>/emails/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                  Review / Edit
                </a>
                <form action="<?= BASE_URL ?>/emails/<?= $d['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this draft?');">
                  <?= Csrf::field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
