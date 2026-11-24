<?php
/**
 * HirePilot AI — Admin Job Management View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Manage Job Postings</h2>
    <p class="page-subtitle">Create, update, toggle visibility and monitor job application counts</p>
  </div>
  <a href="<?= BASE_URL ?>/admin/jobs/create" class="btn btn-primary shadow-sm">
    <i class="bi bi-plus-lg me-1"></i> Post New Job
  </a>
</div>

<div class="hp-card p-0">
  <div class="table-responsive">
    <table class="hp-table">
      <thead>
        <tr>
          <th>Job Title & Company</th>
          <th>Location & Type</th>
          <th>Views / Apps</th>
          <th>Status</th>
          <th>Posted On</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $j): ?>
          <tr>
            <td>
              <div class="fw-bold">
                <a href="<?= BASE_URL ?>/jobs/<?= $j['id'] ?>" target="_blank" class="text-dark">
                  <?= htmlspecialchars($j['title']) ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                </a>
              </div>
              <div class="small text-muted"><?= htmlspecialchars($j['company']) ?></div>
            </td>
            <td>
              <div class="small"><?= htmlspecialchars($j['location'] ?: 'Remote') ?></div>
              <span class="badge bg-light text-secondary border small" style="font-size: .72rem;"><?= ucfirst($j['job_type']) ?></span>
              <?php if ($j['is_remote']): ?>
                <span class="badge bg-info bg-opacity-10 text-info border border-info small" style="font-size: .72rem;">Remote</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="small"><i class="bi bi-eye text-muted me-1"></i> <?= (int)$j['view_count'] ?> views</div>
              <div class="small text-primary"><i class="bi bi-send text-muted me-1"></i> <?= (int)$j['apply_count'] ?> applications</div>
            </td>
            <td>
              <form action="<?= BASE_URL ?>/admin/jobs/<?= $j['id'] ?>/toggle" method="POST" class="d-inline">
                <?= Csrf::field() ?>
                <button type="submit" class="status-pill pill-<?= htmlspecialchars($j['status']) ?> border-0 cursor-pointer" title="Click to toggle active/paused">
                  <?= ucfirst($j['status']) ?>
                </button>
              </form>
            </td>
            <td class="small text-muted"><?= date('M d, Y', strtotime($j['created_at'])) ?></td>
            <td class="text-end text-nowrap">
              <a href="<?= BASE_URL ?>/admin/jobs/<?= $j['id'] ?>/edit" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="<?= BASE_URL ?>/admin/jobs/<?= $j['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this job permanently?');">
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
