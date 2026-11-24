<?php
/**
 * HirePilot AI — Admin Dashboard View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Admin Dashboard</h2>
    <p class="page-subtitle">System statistics, user management, and job postings control center</p>
  </div>
  <a href="<?= BASE_URL ?>/admin/jobs/create" class="btn btn-primary shadow-sm">
    <i class="bi bi-plus-lg me-1"></i> Post New Job
  </a>
</div>

<!-- System Statistics Row -->
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon indigo">
        <i class="bi bi-people-fill"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-sub">Registered accounts</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon emerald">
        <i class="bi bi-briefcase-fill"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">Total Jobs</div>
        <div class="stat-value"><?= $totalJobs ?></div>
        <div class="stat-sub">Platform listings</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon cyan">
        <i class="bi bi-send-check-fill"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">Applications</div>
        <div class="stat-value"><?= $totalApps ?></div>
        <div class="stat-sub">Submitted to jobs</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon violet">
        <i class="bi bi-robot"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">AI Drafts</div>
        <div class="stat-value"><?= $totalDrafts ?></div>
        <div class="stat-sub">Generated letters & emails</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Applications -->
  <div class="col-lg-7">
    <div class="hp-card">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title"><i class="bi bi-send text-primary me-2"></i> Recent Platform Applications</h5>
      </div>
      <div class="hp-card-body p-0">
        <?php if (empty($recentApps)): ?>
          <div class="p-4 text-center text-muted">No applications recorded yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="hp-table">
              <thead>
                <tr>
                  <th>Applicant</th>
                  <th>Job Title</th>
                  <th>Stage</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentApps as $app): ?>
                  <tr>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($app['user_name']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($app['user_email']) ?></div>
                    </td>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($app['job_title']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($app['job_company']) ?></div>
                    </td>
                    <td>
                      <span class="status-pill pill-<?= htmlspecialchars($app['status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                      </span>
                    </td>
                    <td class="small text-muted"><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Users -->
  <div class="col-lg-5">
    <div class="hp-card">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title"><i class="bi bi-person-check text-success me-2"></i> Newest Users</h5>
        <a href="<?= BASE_URL ?>/admin/users" class="small fw-bold">Manage All →</a>
      </div>
      <div class="hp-card-body p-0">
        <?php if (empty($recentUsers)): ?>
          <div class="p-4 text-center text-muted">No users found.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="hp-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Role</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentUsers as $u): ?>
                  <tr>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border small"><?= ucfirst($u['role']) ?></span>
                    </td>
                    <td>
                      <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $u['is_active'] ? 'text-success' : 'text-danger' ?> small">
                        <?= $u['is_active'] ? 'Active' : 'Disabled' ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
