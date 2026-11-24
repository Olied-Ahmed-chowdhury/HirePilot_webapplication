<?php
/**
 * HirePilot AI — Profile Settings View
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$skillsArray = [];
if (!empty($user['skills'])) {
    $skillsArray = is_string($user['skills']) ? (json_decode($user['skills'], true) ?: []) : $user['skills'];
}
?>
<div class="row g-4">
  <div class="col-lg-4">
    <!-- User Quick Summary Card -->
    <div class="hp-card mb-4 text-center p-4">
      <div class="mx-auto mb-3 sidebar-avatar" style="width: 80px; height: 80px; font-size: 2rem;">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        <?php endif; ?>
      </div>
      <h5 class="fw-800 mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
      <p class="text-muted small mb-3"><?= htmlspecialchars($user['headline'] ?? 'Job Seeker') ?></p>

      <div class="d-flex justify-content-center gap-2 mb-3">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
          <?= ucfirst($user['role'] ?? 'user') ?>
        </span>
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
          <?= ucfirst($user['remote_pref'] ?? 'any') ?>
        </span>
      </div>

      <div class="text-start border-top pt-3 small text-muted">
        <div class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> <?= htmlspecialchars($user['email'] ?? '') ?></div>
        <?php if (!empty($user['location'])): ?>
          <div class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> <?= htmlspecialchars($user['location']) ?></div>
        <?php endif; ?>
        <?php if (!empty($user['phone'])): ?>
          <div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i> <?= htmlspecialchars($user['phone']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="hp-card">
      <div class="hp-card-header">
        <h5 class="hp-card-title"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Edit Profile & Job Match Preferences</h5>
      </div>
      <div class="hp-card-body">
        <form action="<?= BASE_URL ?>/profile" method="POST">
          <?= Csrf::field() ?>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Professional Headline</label>
              <input type="text" name="headline" class="form-control" placeholder="e.g. Full-Stack Developer | PHP, JS, Vue" value="<?= htmlspecialchars($user['headline'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Target Role</label>
              <input type="text" name="target_role" class="form-control" placeholder="e.g. Software Engineer, Backend Lead" value="<?= htmlspecialchars($user['target_role'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Years of Experience</label>
              <input type="number" name="experience_yrs" min="0" max="40" class="form-control" value="<?= (int)($user['experience_yrs'] ?? 0) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Work Preference</label>
              <select name="remote_pref" class="form-select">
                <option value="any" <?= ($user['remote_pref'] ?? '') === 'any' ? 'selected' : '' ?>>Any (Flexible)</option>
                <option value="remote" <?= ($user['remote_pref'] ?? '') === 'remote' ? 'selected' : '' ?>>100% Remote</option>
                <option value="hybrid" <?= ($user['remote_pref'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                <option value="onsite" <?= ($user['remote_pref'] ?? '') === 'onsite' ? 'selected' : '' ?>>On-site</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Location / City</label>
              <input type="text" name="location" class="form-control" placeholder="e.g. Dhaka, Bangladesh" value="<?= htmlspecialchars($user['location'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control" placeholder="e.g. +880 1712-345678" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
          </div>

          <!-- Skills Tag Input -->
          <div class="mb-3">
            <label class="form-label">Skills (Used for AI Job Matching & Application Drafting)</label>
            <div class="skills-tag-container" id="profile-skills-box">
              <input type="text" class="skill-tag-input" placeholder="Type skill (e.g. PHP, MySQL, React) & press Enter...">
            </div>
            <input type="hidden" name="skills" id="profile-skills-hidden" value="<?= htmlspecialchars(json_encode($skillsArray)) ?>">
            <div class="form-text">Type skill and press <kbd>Enter</kbd> or comma. Skills power your automatic AI matching score.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Professional Bio / Summary</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Brief summary of your background, achievements, and career focus..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">LinkedIn URL</label>
              <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/..." value="<?= htmlspecialchars($user['linkedin_url'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">GitHub URL</label>
              <input type="url" name="github_url" class="form-control" placeholder="https://github.com/..." value="<?= htmlspecialchars($user['github_url'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Portfolio URL</label>
              <input type="url" name="portfolio_url" class="form-control" placeholder="https://myportfolio.dev" value="<?= htmlspecialchars($user['portfolio_url'] ?? '') ?>">
            </div>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-primary px-4 py-2">
              <i class="bi bi-check2-circle me-1"></i> Save Profile Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initSkillsInput('profile-skills-box', 'profile-skills-hidden');
});
</script>
