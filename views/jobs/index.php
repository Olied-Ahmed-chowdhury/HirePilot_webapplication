<?php
/**
 * HirePilot AI — Job Discovery View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

$q = htmlspecialchars($_GET['q'] ?? '');
$currentType = $_GET['job_type'] ?? '';
$isRemote = $_GET['is_remote'] ?? '';
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Explore Opportunities</h2>
    <p class="page-subtitle">Personalized job recommendations ranked by AI skill matching</p>
  </div>
</div>

<!-- Search & Interactive Filters Bar -->
<div class="hp-card p-3 mb-4">
  <form action="<?= BASE_URL ?>/jobs" method="GET" class="row g-2 align-items-center">
    <div class="col-md-5">
      <div class="input-group-icon">
        <i class="bi bi-search"></i>
        <input type="text" name="q" class="form-control" placeholder="Search by job title, company, or tech stack..." value="<?= $q ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="job_type" class="form-select">
        <option value="">All Job Types</option>
        <option value="full-time" <?= $currentType === 'full-time' ? 'selected' : '' ?>>Full-Time</option>
        <option value="part-time" <?= $currentType === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
        <option value="contract" <?= $currentType === 'contract' ? 'selected' : '' ?>>Contract</option>
        <option value="internship" <?= $currentType === 'internship' ? 'selected' : '' ?>>Internship</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="is_remote" class="form-select">
        <option value="">Any Location</option>
        <option value="1" <?= $isRemote === '1' ? 'selected' : '' ?>>Remote Only</option>
        <option value="0" <?= $isRemote === '0' ? 'selected' : '' ?>>On-site / Hybrid</option>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-filter"></i> Filter
      </button>
      <?php if (!empty($q) || !empty($currentType) || $isRemote !== ''): ?>
        <a href="<?= BASE_URL ?>/jobs" class="btn btn-outline-secondary" title="Clear filters">
          <i class="bi bi-x-lg"></i>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Filter Pills row -->
<div class="filter-pills mb-4">
  <a href="<?= BASE_URL ?>/jobs" class="filter-pill <?= empty($currentType) && $isRemote === '' ? 'active' : '' ?>">
    <i class="bi bi-grid-fill"></i> All Jobs
  </a>
  <a href="<?= BASE_URL ?>/jobs?is_remote=1" class="filter-pill <?= $isRemote === '1' ? 'active' : '' ?>">
    <i class="bi bi-laptop"></i> Remote Only
  </a>
  <a href="<?= BASE_URL ?>/jobs?job_type=full-time" class="filter-pill <?= $currentType === 'full-time' ? 'active' : '' ?>">
    <i class="bi bi-briefcase-fill"></i> Full-Time
  </a>
  <a href="<?= BASE_URL ?>/jobs?job_type=contract" class="filter-pill <?= $currentType === 'contract' ? 'active' : '' ?>">
    <i class="bi bi-file-earmark-code"></i> Contract
  </a>
  <a href="<?= BASE_URL ?>/jobs?job_type=internship" class="filter-pill <?= $currentType === 'internship' ? 'active' : '' ?>">
    <i class="bi bi-mortarboard-fill"></i> Internship
  </a>
</div>

<!-- Job Cards Grid -->
<?php if (empty($result['jobs'])): ?>
  <div class="hp-card p-5 text-center">
    <div class="empty-icon"><i class="bi bi-search text-muted" style="font-size: 3rem;"></i></div>
    <h4 class="fw-bold mt-2">No jobs found matching your criteria</h4>
    <p class="text-muted">Try clearing some filters or searching with different keywords.</p>
    <a href="<?= BASE_URL ?>/jobs" class="btn btn-outline-primary">Reset All Filters</a>
  </div>
<?php else: ?>
  <div class="row g-4 mb-4">
    <?php foreach ($result['jobs'] as $job): ?>
      <?php
        $matchScore = (int)($job['match_score'] ?? 50);
        $skills = !empty($job['skills_required']) ? (is_string($job['skills_required']) ? json_decode($job['skills_required'], true) : $job['skills_required']) : [];
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="job-card h-100 d-flex flex-column justify-content-between">
          <div>
            <!-- Top Card Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="job-company-logo">
                <i class="bi bi-building text-primary"></i>
              </div>
              <!-- AI Match Pill -->
              <span class="badge <?= $matchScore >= 75 ? 'bg-success' : ($matchScore >= 50 ? 'bg-primary' : 'bg-warning text-dark') ?> rounded-pill px-3 py-1">
                <i class="bi bi-stars me-1"></i> <?= $matchScore ?>% Match
              </span>
            </div>

            <h5 class="job-title mb-1">
              <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>"><?= htmlspecialchars($job['title']) ?></a>
            </h5>
            <div class="job-company mb-2">
              <span class="fw-bold"><?= htmlspecialchars($job['company']) ?></span> &bull;
              <span><?= htmlspecialchars($job['location'] ?: 'Remote') ?></span>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="badge bg-light text-secondary border small">
                <i class="bi bi-briefcase me-1"></i> <?= ucfirst($job['job_type']) ?>
              </span>
              <?php if ($job['is_remote']): ?>
                <span class="badge bg-info bg-opacity-10 text-info border border-info small">
                  <i class="bi bi-wifi"></i> Remote
                </span>
              <?php endif; ?>
              <?php if (!empty($job['salary_min'])): ?>
                <span class="badge bg-light text-success border small">
                  <?= number_format((float)$job['salary_min']) ?> <?= htmlspecialchars($job['salary_currency']) ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Required Skills Tag Snippet -->
            <div class="d-flex flex-wrap gap-1 mb-3">
              <?php foreach (array_slice($skills, 0, 4) as $skill): ?>
                <span class="badge bg-light text-dark border small" style="font-size: .72rem;"><?= htmlspecialchars($skill) ?></span>
              <?php endforeach; ?>
              <?php if (count($skills) > 4): ?>
                <span class="badge bg-light text-muted small" style="font-size: .72rem;">+<?= count($skills) - 4 ?> more</span>
              <?php endif; ?>
            </div>

            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?= htmlspecialchars(strip_tags($job['description'])) ?>
            </p>
          </div>

          <!-- Bottom Card Action Footer -->
          <div class="pt-3 border-top d-flex gap-2 justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/emails/create?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary" title="AI Generate Cover Letter">
              <i class="bi bi-stars me-1"></i> AI Apply
            </a>
            <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" class="btn btn-sm btn-primary px-3">
              View Details
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($result['total_pages'] > 1): ?>
    <nav class="d-flex justify-content-center mt-4">
      <ul class="pagination">
        <?php for ($p = 1; $p <= $result['total_pages']; $p++): ?>
          <li class="page-item <?= $p === $result['page'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/jobs?page=<?= $p ?>&q=<?= urlencode($q) ?>&job_type=<?= urlencode($currentType) ?>&is_remote=<?= urlencode($isRemote) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php endif; ?>
