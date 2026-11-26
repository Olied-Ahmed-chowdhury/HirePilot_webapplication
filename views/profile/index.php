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
<div class="page-header">
  <div>
    <h2 class="page-title">Profile &amp; Preferences</h2>
    <p class="page-subtitle">Maintain your career preferences and technical skills used for job matching</p>
  </div>
  <a href="<?= BASE_URL ?>/onboarding" class="btn btn-outline-primary">
    <i class="bi bi-chat-square-text me-1"></i> Open AI Profile Inquiry
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="hp-card p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="sidebar-avatar" style="width: 52px; height: 52px; font-size: 1.25rem;">
          <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div>
          <h5 class="fw-bold mb-0"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
          <div class="text-muted small"><?= htmlspecialchars($user['headline'] ?: 'Candidate') ?></div>
        </div>
      </div>

      <div class="d-flex gap-2 mb-3">
        <span class="status-pill pill-applied"><?= ucfirst($user['role'] ?? 'user') ?></span>
        <span class="status-pill pill-saved"><?= ucfirst($user['work_preference'] ?? 'any') ?></span>
      </div>

      <div class="border-top pt-3 small text-muted d-flex flex-column gap-2">
        <div><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email'] ?? '') ?></div>
        <?php if (!empty($user['preferred_location'])): ?>
          <div><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($user['preferred_location']) ?></div>
        <?php endif; ?>
        <?php if (!empty($user['phone'])): ?>
          <div><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($user['phone']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="hp-card">
      <div class="hp-card-header">
        <h5 class="hp-card-title">Edit Career Profile</h5>
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
              <input type="text" name="headline" class="form-control" placeholder="e.g. Full-Stack PHP Developer" value="<?= htmlspecialchars($user['headline'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Target Role</label>
              <input type="text" name="target_role" class="form-control" placeholder="e.g. Backend Engineer" value="<?= htmlspecialchars($user['target_role'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Experience Level</label>
              <select name="experience_level" class="form-select">
                <option value="entry" <?= ($user['experience_level'] ?? '') === 'entry' ? 'selected' : '' ?>>Entry</option>
                <option value="mid" <?= ($user['experience_level'] ?? 'mid') === 'mid' ? 'selected' : '' ?>>Mid</option>
                <option value="senior" <?= ($user['experience_level'] ?? '') === 'senior' ? 'selected' : '' ?>>Senior</option>
                <option value="lead" <?= ($user['experience_level'] ?? '') === 'lead' ? 'selected' : '' ?>>Lead</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Years of Experience</label>
              <input type="number" name="experience_yrs" min="0" max="40" class="form-control" value="<?= (int) ($user['experience_yrs'] ?? 0) ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Work Preference</label>
              <select name="work_preference" class="form-select">
                <option value="any" <?= ($user['work_preference'] ?? '') === 'any' ? 'selected' : '' ?>>Any</option>
                <option value="remote" <?= ($user['work_preference'] ?? '') === 'remote' ? 'selected' : '' ?>>Remote</option>
                <option value="hybrid" <?= ($user['work_preference'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                <option value="onsite" <?= ($user['work_preference'] ?? '') === 'onsite' ? 'selected' : '' ?>>On-site</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Preferred Location</label>
              <input type="text" name="preferred_location" class="form-control" placeholder="e.g. Dhaka, Bangladesh" value="<?= htmlspecialchars($user['preferred_location'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control" placeholder="+880 1700-000000" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Technical Skills</label>
            <div class="skills-tag-container" id="profile-skills-box">
              <input type="text" class="skill-tag-input" placeholder="Type skill and press Enter...">
            </div>
            <input type="hidden" name="skills" id="profile-skills-hidden" value="<?= htmlspecialchars(json_encode($skillsArray)) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Career Goals</label>
            <textarea name="career_goals" class="form-control" rows="2" placeholder="Describe the roles or engineering teams you are targeting..."><?= htmlspecialchars($user['career_goals'] ?? '') ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Professional Summary</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Concise overview of your background..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2 me-1"></i> Save Changes
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
