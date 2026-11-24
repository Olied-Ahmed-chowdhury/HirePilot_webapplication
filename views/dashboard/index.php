<?php
/**
 * HirePilot AI — Dashboard View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Auth;

$user = Auth::user();
?>
<div class="row g-4 mb-4">
  <!-- Stat Card: Total Applications -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon indigo">
        <i class="bi bi-send-fill"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">Total Applied</div>
        <div class="stat-value"><?= $totalApps ?></div>
        <div class="stat-sub">Lifetime applications</div>
      </div>
    </div>
  </div>

  <!-- Stat Card: Active in Pipeline -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon cyan">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">In Pipeline</div>
        <div class="stat-value"><?= $activeApps ?></div>
        <div class="stat-sub">Active consideration</div>
      </div>
    </div>
  </div>

  <!-- Stat Card: Interviews -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon emerald">
        <i class="bi bi-calendar2-check-fill"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">Interviews</div>
        <div class="stat-value"><?= $interviews ?></div>
        <div class="stat-sub">Scheduled calls/meetings</div>
      </div>
    </div>
  </div>

  <!-- Stat Card: AI Drafts -->
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon violet">
        <i class="bi bi-stars"></i>
      </div>
      <div class="stat-info">
        <div class="stat-label">AI Drafts</div>
        <div class="stat-value"><?= $aiDraftsCount ?></div>
        <div class="stat-sub">Letters & custom emails</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left column: Top AI Recommended Jobs -->
  <div class="col-lg-8">
    <div class="hp-card mb-4">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title">
          <i class="bi bi-stars text-warning me-2"></i> Top AI Matched Jobs For You
        </h5>
        <a href="<?= BASE_URL ?>/jobs" class="small fw-bold">Explore All Jobs →</a>
      </div>
      <div class="hp-card-body p-3">
        <?php if (empty($topJobs)): ?>
          <div class="empty-state py-4">
            <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
            <div class="empty-title">No matching jobs available</div>
            <div class="empty-desc">Check back soon as new opportunities are posted regularly.</div>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($topJobs as $job): ?>
              <?php
                $matchScore = (int)($job['match_score'] ?? 50);
                $skills = !empty($job['skills_required']) ? (is_string($job['skills_required']) ? json_decode($job['skills_required'], true) : $job['skills_required']) : [];
              ?>
              <div class="job-card p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex gap-3 align-items-center">
                  <div class="job-company-logo">
                    <i class="bi bi-building text-primary"></i>
                  </div>
                  <div>
                    <h6 class="job-title mb-1">
                      <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>"><?= htmlspecialchars($job['title']) ?></a>
                    </h6>
                    <div class="job-company mb-1"><?= htmlspecialchars($job['company']) ?> &bull; <?= htmlspecialchars($job['location'] ?: 'Remote') ?></div>
                    <div class="d-flex flex-wrap gap-1">
                      <?php foreach (array_slice($skills, 0, 4) as $s): ?>
                        <span class="badge bg-light text-secondary border small" style="font-size: .72rem;"><?= htmlspecialchars($s) ?></span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-column align-items-md-end gap-2 text-nowrap">
                  <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold text-muted">AI Match:</span>
                    <span class="badge <?= $matchScore >= 75 ? 'bg-success' : ($matchScore >= 50 ? 'bg-primary' : 'bg-warning text-dark') ?> rounded-pill px-2 py-1">
                      <?= $matchScore ?>%
                    </span>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-magic me-1"></i> AI Draft
                    </a>
                    <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" class="btn btn-primary btn-sm">
                      View
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Applications Pipeline -->
    <div class="hp-card">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title">
          <i class="bi bi-clock-history text-primary me-2"></i> Recent Applications
        </h5>
        <a href="<?= BASE_URL ?>/applications" class="small fw-bold">View Pipeline →</a>
      </div>
      <div class="hp-card-body p-0">
        <?php if (empty($recentApps)): ?>
          <div class="empty-state py-4">
            <div class="empty-icon"><i class="bi bi-journal-x"></i></div>
            <div class="empty-title">No applications yet</div>
            <div class="empty-desc">Find matching jobs and start applying with tailored cover letters!</div>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="hp-table">
              <thead>
                <tr>
                  <th>Job Title & Company</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Applied On</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentApps as $app): ?>
                  <tr>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($app['job_title']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($app['job_company']) ?></div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($app['job_location'] ?: 'Remote') ?></td>
                    <td>
                      <span class="status-pill pill-<?= htmlspecialchars($app['status']) ?>" data-app-status="<?= $app['id'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                      </span>
                    </td>
                    <td class="small text-muted"><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                    <td class="text-end">
                      <a href="<?= BASE_URL ?>/applications" class="btn btn-sm btn-ghost" title="View details">
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

  <!-- Right column: Profile & Quick Links -->
  <div class="col-lg-4">
    <!-- Quick Profile Completeness -->
    <div class="hp-card mb-4 p-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-person-check-fill text-success me-2"></i> Quick Profile Status</h6>
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="sidebar-avatar" style="width: 52px; height: 52px; font-size: 1.4rem;">
          <?php if (!empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="fw-bold"><?= htmlspecialchars($user['name'] ?? '') ?></div>
          <div class="small text-muted"><?= htmlspecialchars($user['headline'] ?? 'Set up your headline') ?></div>
        </div>
      </div>

      <div class="small text-muted mb-3">
        Keep your skills updated so the AI match engine presents the most relevant job postings.
      </div>
      <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-primary btn-sm w-100">
        <i class="bi bi-pencil-square me-1"></i> Edit Profile & Skills
      </a>
    </div>

    <!-- Quick Actions Card -->
    <div class="hp-card p-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Quick Actions</h6>
      <div class="d-flex flex-column gap-2">
        <a href="<?= BASE_URL ?>/jobs" class="btn btn-light text-start p-3 border d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold small">Explore Jobs</div>
            <div class="text-muted" style="font-size: .75rem;">Filter by skills, remote, salary</div>
          </div>
          <i class="bi bi-arrow-right text-primary"></i>
        </a>
        <a href="<?= BASE_URL ?>/resume" class="btn btn-light text-start p-3 border d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold small">Manage Resumes</div>
            <div class="text-muted" style="font-size: .75rem;">Upload PDF/DOCX for AI drafting</div>
          </div>
          <i class="bi bi-arrow-right text-primary"></i>
        </a>
        <a href="<?= BASE_URL ?>/emails" class="btn btn-light text-start p-3 border d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold small">Generate AI Cover Letter</div>
            <div class="text-muted" style="font-size: .75rem;">Powered by Grok AI LLM</div>
          </div>
          <i class="bi bi-arrow-right text-primary"></i>
        </a>
      </div>
    </div>
  </div>
</div>
