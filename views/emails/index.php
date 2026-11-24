<?php
/**
 * HirePilot AI — Email Drafts & Cover Letters List View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="page-header">
  <div>
    <h2 class="page-title">AI Application Writing Hub</h2>
    <p class="page-subtitle">Crafted cover letters, tailored application emails, and transmission records</p>
  </div>
  <a href="<?= BASE_URL ?>/emails/create" class="btn btn-primary shadow-sm">
    <i class="bi bi-stars me-1"></i> New AI Generation
  </a>
</div>

<?php if (empty($drafts)): ?>
  <div class="hp-card p-5 text-center">
    <div class="empty-icon"><i class="bi bi-stars text-primary" style="font-size: 3.5rem;"></i></div>
    <h4 class="fw-bold mt-2">No AI drafts created yet</h4>
    <p class="text-muted mx-auto" style="max-width: 480px;">
      Generate custom cover letters and personalized cold emails directly tailored to any job vacancy using HirePilot AI.
    </p>
    <a href="<?= BASE_URL ?>/emails/create" class="btn btn-primary mt-2">
      <i class="bi bi-magic me-1"></i> Generate Your First Draft
    </a>
  </div>
<?php else: ?>
  <div class="hp-card p-0">
    <div class="table-responsive">
      <table class="hp-table">
        <thead>
          <tr>
            <th>Target Opportunity</th>
            <th>Type</th>
            <th>Subject Line</th>
            <th>AI Engine</th>
            <th>Approval Status</th>
            <th>Created</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($drafts as $d): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= htmlspecialchars($d['job_title'] ?: 'General Application') ?></div>
                <div class="small text-muted"><?= htmlspecialchars($d['job_company'] ?: 'Direct Outreach') ?></div>
              </td>
              <td>
                <span class="badge bg-light text-dark border small">
                  <?= $d['draft_type'] === 'cover_letter' ? '📄 Cover Letter' : '✉️ Application Email' ?>
                </span>
              </td>
              <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($d['subject'] ?? '') ?>">
                <?= htmlspecialchars($d['subject'] ?: 'No Subject') ?>
              </td>
              <td>
                <span class="badge bg-info bg-opacity-10 text-info border border-info small" style="font-size: .72rem;">
                  <?= htmlspecialchars(explode(' ', $d['ai_model_used'] ?? 'grok')[0]) ?>
                </span>
              </td>
              <td>
                <?php if ($d['status'] === 'sent'): ?>
                  <span class="status-pill pill-offer_received">Sent ✓</span>
                <?php elseif ($d['status'] === 'approved'): ?>
                  <span class="status-pill pill-active">Approved</span>
                <?php else: ?>
                  <span class="status-pill pill-under_review">Draft</span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
              <td class="text-end text-nowrap">
                <a href="<?= BASE_URL ?>/emails/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-eye me-1"></i> Preview / Edit
                </a>
                <form action="<?= BASE_URL ?>/emails/<?= $d['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this draft?');">
                  <?= Csrf::field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
