<?php
/**
 * HirePilot AI — Application Pipeline & Tracker View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$currentStatus = $_GET['status'] ?? '';
$statuses = [
    'saved'               => ['label' => 'Saved', 'icon' => 'bi-bookmark', 'badge' => 'secondary'],
    'applied'             => ['label' => 'Applied', 'icon' => 'bi-send', 'badge' => 'primary'],
    'under_review'        => ['label' => 'Under Review', 'icon' => 'bi-hourglass-split', 'badge' => 'warning'],
    'interview_scheduled' => ['label' => 'Interviewing', 'icon' => 'bi-calendar-check', 'badge' => 'info'],
    'offer_received'      => ['label' => 'Offer Received', 'icon' => 'bi-trophy', 'badge' => 'success'],
    'rejected'            => ['label' => 'Rejected', 'icon' => 'bi-x-circle', 'badge' => 'danger'],
    'withdrawn'           => ['label' => 'Withdrawn', 'icon' => 'bi-arrow-return-left', 'badge' => 'dark'],
];
?>
<div class="page-header">
  <div>
    <h2 class="page-title">Application Pipeline</h2>
    <p class="page-subtitle">Track, update and manage every active job application in real time</p>
  </div>
  <a href="<?= BASE_URL ?>/jobs" class="btn btn-primary shadow-sm">
    <i class="bi bi-search me-1"></i> Discover More Jobs
  </a>
</div>

<!-- Interactive Status Pipeline Filter Pills -->
<div class="filter-pills mb-4">
  <a href="<?= BASE_URL ?>/applications" class="filter-pill <?= empty($currentStatus) ? 'active' : '' ?>">
    <i class="bi bi-grid-fill"></i> All Applications (<?= array_sum($statusCounts) ?>)
  </a>
  <?php foreach ($statuses as $k => $meta): ?>
    <a href="<?= BASE_URL ?>/applications?status=<?= $k ?>" class="filter-pill <?= $currentStatus === $k ? 'active' : '' ?>">
      <i class="bi <?= $meta['icon'] ?>"></i> <?= $meta['label'] ?>
      <span class="badge bg-light text-dark border ms-1" style="font-size: .7rem;"><?= $statusCounts[$k] ?? 0 ?></span>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($applications)): ?>
  <div class="hp-card p-5 text-center">
    <div class="empty-icon"><i class="bi bi-kanban text-muted" style="font-size: 3.5rem;"></i></div>
    <h4 class="fw-bold mt-2">No applications found in this stage</h4>
    <p class="text-muted mx-auto" style="max-width: 480px;">
      Explore curated job opportunities and use HirePilot AI to generate tailored cover letters and track company responses.
    </p>
    <a href="<?= BASE_URL ?>/jobs" class="btn btn-primary mt-2">Browse Job Listings</a>
  </div>
<?php else: ?>
  <div class="hp-card p-0">
    <div class="table-responsive">
      <table class="hp-table">
        <thead>
          <tr>
            <th>Company & Opportunity</th>
            <th>Location</th>
            <th>Type</th>
            <th>Current Pipeline Stage</th>
            <th>Applied Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $app): ?>
            <tr>
              <td>
                <div class="fw-bold">
                  <a href="<?= BASE_URL ?>/applications/<?= $app['id'] ?>" class="text-dark">
                    <?= htmlspecialchars($app['job_title']) ?>
                  </a>
                </div>
                <div class="small text-muted"><?= htmlspecialchars($app['job_company']) ?></div>
              </td>
              <td class="small text-muted"><?= htmlspecialchars($app['job_location'] ?: 'Remote') ?></td>
              <td>
                <span class="badge bg-light text-dark border small">
                  <?= ucfirst($app['job_type']) ?>
                </span>
                <?php if ($app['is_remote']): ?>
                  <span class="badge bg-info bg-opacity-10 text-info border border-info small" style="font-size: .7rem;">Remote</span>
                <?php endif; ?>
              </td>
              <td>
                <!-- Interactive AJAX Status Selector -->
                <div class="dropdown d-inline-block">
                  <button class="status-pill pill-<?= htmlspecialchars($app['status']) ?> dropdown-toggle border-0"
                          type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-status-<?= $app['id'] ?>"
                          data-app-status="<?= $app['id'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                  </button>
                  <ul class="dropdown-menu shadow border" style="border-radius: 12px; font-size: .85rem;">
                    <li><h6 class="dropdown-header">Update Stage</h6></li>
                    <?php foreach ($statuses as $stKey => $stMeta): ?>
                      <li>
                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 <?= $app['status'] === $stKey ? 'active' : '' ?>"
                                onclick="changeAppStatus(<?= $app['id'] ?>, '<?= $stKey ?>', this)">
                          <i class="bi <?= $stMeta['icon'] ?>"></i> <?= $stMeta['label'] ?>
                        </button>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </td>
              <td class="small text-muted">
                <?= $app['applied_at'] ? date('M d, Y', strtotime($app['applied_at'])) : '<span class="text-secondary opacity-50">Not sent</span>' ?>
              </td>
              <td class="text-end text-nowrap">
                <a href="<?= BASE_URL ?>/applications/<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="View details and notes">
                  <i class="bi bi-pencil-square me-1"></i> Details
                </a>
                <form action="<?= BASE_URL ?>/applications/<?= $app['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Remove application from tracker?');">
                  <?= Csrf::field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
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

<script>
async function changeAppStatus(appId, newStatus, itemEl) {
  const btn = document.getElementById(`btn-status-${appId}`);
  btnLoading(btn, true);

  try {
    const res = await Http.post(`<?= BASE_URL ?>/applications/${appId}/status`, {
      status: newStatus,
      _csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
    });

    if (res.success) {
      btn.className = `status-pill pill-${newStatus} dropdown-toggle border-0`;
      btn.textContent = res.readable;
      Toast.success(`Status updated to "${res.readable}"`);

      // Update active in dropdown
      const menu = itemEl.closest('.dropdown-menu');
      menu.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
      itemEl.classList.add('active');
    }
  } catch (err) {
    Toast.error(err.message || 'Failed to update status');
  } finally {
    btnLoading(btn, false);
  }
}
</script>
