<?php
/**
 * HirePilot AI — Dashboard View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;

$user = Auth::user();
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Workspace Overview</h2>
    <p class="page-subtitle">Active applications, skill-matched openings, and drafted outreach</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge bg-light text-dark border small py-2 px-3">
      <i class="bi bi-database me-1 text-primary"></i> Storage: <?= htmlspecialchars($dbDriver ?? 'PDO') ?>
    </span>
    <a href="<?= BASE_URL ?>/jobs" class="btn btn-primary">
      <i class="bi bi-search me-1"></i> Find Jobs
    </a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-send"></i></div>
      <div>
        <div class="stat-label">Total Tracked</div>
        <div class="stat-value"><?= $totalApps ?></div>
        <div class="stat-sub">Applications recorded</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-label">Active Pipeline</div>
        <div class="stat-value"><?= $activeApps ?></div>
        <div class="stat-sub">Applied or reviewing</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
      <div>
        <div class="stat-label">Interviews</div>
        <div class="stat-value"><?= $interviews ?></div>
        <div class="stat-sub">Scheduled stages</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-envelope-paper"></i></div>
      <div>
        <div class="stat-label">AI Drafts</div>
        <div class="stat-value"><?= $aiDraftsCount ?></div>
        <div class="stat-sub">Letters &amp; emails</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="hp-card mb-4">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title">Top Skill-Matched Opportunities</h5>
        <a href="<?= BASE_URL ?>/jobs" class="small fw-semibold">View all jobs</a>
      </div>
      <div class="hp-card-body">
        <?php if (empty($topJobs)): ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
            <div class="empty-title">No active job listings</div>
            <div class="empty-desc">New openings will appear here once published.</div>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($topJobs as $job): ?>
              <?php
                $matchScore = (int) ($job['match_score'] ?? 50);
                $skills     = !empty($job['skills_required']) ? (is_string($job['skills_required']) ? (json_decode($job['skills_required'], true) ?: []) : $job['skills_required']) : [];
              ?>
              <div class="job-card d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex gap-3 align-items-center">
                  <div class="job-company-logo"><i class="bi bi-building text-secondary"></i></div>
                  <div>
                    <h6 class="job-title mb-1">
                      <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>"><?= htmlspecialchars($job['title']) ?></a>
                    </h6>
                    <div class="job-company mb-1"><?= htmlspecialchars($job['company']) ?> &middot; <?= htmlspecialchars($job['location'] ?: 'Remote') ?></div>
                    <div class="d-flex flex-wrap gap-1">
                      <?php foreach (array_slice($skills, 0, 4) as $s): ?>
                        <span class="badge bg-light text-dark border fw-normal"><?= htmlspecialchars($s) ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-column align-items-md-end gap-2 text-nowrap">
                  <span class="small fw-semibold text-muted">Match Score: <strong class="text-dark"><?= $matchScore ?>%</strong></span>
                  <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm">Draft Email</a>
                    <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" class="btn btn-primary btn-sm">Inspect</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="hp-card">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title">Recent Applications</h5>
        <a href="<?= BASE_URL ?>/applications" class="small fw-semibold">Full pipeline</a>
      </div>
      <div class="hp-card-body p-0">
        <?php if (empty($recentApps)): ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-kanban"></i></div>
            <div class="empty-title">No applications tracked yet</div>
            <div class="empty-desc">Save or apply to a job listing to track its progress here.</div>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="hp-table">
              <thead>
                <tr>
                  <th>Role &amp; Company</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Updated</th>
                  <th class="text-end">Details</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentApps as $app): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($app['job_title'] ?? 'Position') ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($app['job_company'] ?? '') ?></div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($app['job_location'] ?: 'Remote') ?></td>
                    <td>
                      <span class="status-pill pill-<?= htmlspecialchars($app['status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                      </span>
                    </td>
                    <td class="small text-muted"><?= date('M d, Y', strtotime($app['updated_at'])) ?></td>
                    <td class="text-end">
                      <a href="<?= BASE_URL ?>/applications/<?= $app['id'] ?>" class="btn btn-sm btn-ghost">
                        <i class="bi bi-chevron-right"></i>
                      </a>
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

  <div class="col-lg-4">
    <div class="hp-card p-4 mb-4">
      <h6 class="fw-bold mb-2">Profile Readiness</h6>
      <p class="small text-muted mb-3">
        Keep your skills, experience level, and work preferences accurate to improve match scoring and AI drafts.
      </p>
      <div class="d-flex flex-column gap-2">
        <a href="<?= BASE_URL ?>/onboarding" class="btn btn-primary btn-sm">
          <i class="bi bi-chat-square-text me-1"></i> Run AI Profile Inquiry
        </a>
        <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-sliders me-1"></i> Edit Profile Manually
        </a>
      </div>
    </div>

    <div class="hp-card p-4">
      <h6 class="fw-bold mb-3">Workspace Shortcuts</h6>
      <div class="d-flex flex-column gap-2">
        <a href="<?= BASE_URL ?>/resume" class="btn btn-outline-primary btn-sm text-start d-flex justify-content-between align-items-center">
          <span>Manage Uploaded Resumes</span>
          <i class="bi bi-arrow-right"></i>
        </a>
        <a href="<?= BASE_URL ?>/emails/create" class="btn btn-outline-primary btn-sm text-start d-flex justify-content-between align-items-center">
          <span>Draft Application Email</span>
          <i class="bi bi-arrow-right"></i>
        </a>
        <a href="<?= BASE_URL ?>/applications" class="btn btn-outline-primary btn-sm text-start d-flex justify-content-between align-items-center">
          <span>Update Pipeline Statuses</span>
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>
