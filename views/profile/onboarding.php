<?php
/**
 * HirePilot AI — Interactive AI Profile Inquiry & Onboarding (SRS 2.3)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$skillsArray = !empty($user['skills'])
    ? (is_string($user['skills']) ? (json_decode($user['skills'], true) ?: []) : $user['skills'])
    : [];
?>
<div class="page-header">
  <div>
    <h2 class="page-title">AI Profile Inquiry</h2>
    <p class="page-subtitle">Answer a few quick questions so HirePilot AI can structure your skills and job preferences</p>
  </div>
  <form action="<?= BASE_URL ?>/onboarding/skip" method="POST">
    <?= Csrf::field() ?>
    <button type="submit" class="btn btn-outline-secondary btn-sm">
      Skip to Dashboard
    </button>
  </form>
</div>

<div class="row g-4">
  <!-- Left: Multi-turn AI Chat -->
  <div class="col-lg-7">
    <div class="hp-card">
      <div class="hp-card-header d-flex justify-content-between align-items-center">
        <h5 class="hp-card-title"><i class="bi bi-chat-square-text me-2 text-primary"></i> Guided Profile Interview</h5>
        <span class="badge bg-light text-dark border" id="inquiry-status-badge">
          <?= !empty($user['onboarding_completed']) ? 'Completed' : 'In Progress' ?>
        </span>
      </div>
      <div class="hp-card-body">
        <div class="chat-stream mb-3" id="inquiry-chat-stream">
          <?php foreach ($history as $turn): ?>
            <div class="chat-msg <?= $turn['role'] === 'assistant' ? 'assistant' : 'user' ?>">
              <?= nl2br(htmlspecialchars($turn['content'])) ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="inquiry-error-box" class="alert alert-danger small d-none mb-3"></div>

        <form id="inquiry-form">
          <div class="mb-2">
            <textarea id="inquiry-input" class="form-control" rows="2"
                      placeholder="Example: I am targeting Junior PHP Backend Developer roles in Dhaka or hybrid. I have 1 year experience with PHP, MySQL, Bootstrap, and Git."
                      required></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Extracted fields update your profile automatically.</span>
            <button type="submit" class="btn btn-primary" id="inquiry-send-btn">
              <i class="bi bi-send me-1"></i> Send Response
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right: Live Extracted Profile State (Editable Manual Fallback) -->
  <div class="col-lg-5">
    <div class="hp-card">
      <div class="hp-card-header">
        <h5 class="hp-card-title"><i class="bi bi-sliders me-2 text-primary"></i> Extracted Profile (Manual Fallback)</h5>
      </div>
      <div class="hp-card-body">
        <form action="<?= BASE_URL ?>/profile" method="POST">
          <?= Csrf::field() ?>
          <input type="hidden" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">

          <div class="mb-3">
            <label class="form-label">Target Role</label>
            <input type="text" name="target_role" id="field-target-role" class="form-control"
                   value="<?= htmlspecialchars($user['target_role'] ?? '') ?>" placeholder="e.g. Full-Stack Developer">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Experience Level</label>
              <select name="experience_level" id="field-exp-level" class="form-select">
                <option value="entry" <?= ($user['experience_level'] ?? '') === 'entry' ? 'selected' : '' ?>>Entry</option>
                <option value="mid" <?= ($user['experience_level'] ?? 'mid') === 'mid' ? 'selected' : '' ?>>Mid</option>
                <option value="senior" <?= ($user['experience_level'] ?? '') === 'senior' ? 'selected' : '' ?>>Senior</option>
                <option value="lead" <?= ($user['experience_level'] ?? '') === 'lead' ? 'selected' : '' ?>>Lead</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Work Preference</label>
              <select name="work_preference" id="field-work-pref" class="form-select">
                <option value="any" <?= ($user['work_preference'] ?? 'any') === 'any' ? 'selected' : '' ?>>Any</option>
                <option value="remote" <?= ($user['work_preference'] ?? '') === 'remote' ? 'selected' : '' ?>>Remote</option>
                <option value="hybrid" <?= ($user['work_preference'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                <option value="onsite" <?= ($user['work_preference'] ?? '') === 'onsite' ? 'selected' : '' ?>>On-site</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Preferred Location</label>
            <input type="text" name="preferred_location" id="field-location" class="form-control"
                   value="<?= htmlspecialchars($user['preferred_location'] ?? '') ?>" placeholder="e.g. Dhaka, Bangladesh">
          </div>

          <div class="mb-3">
            <label class="form-label">Skills</label>
            <div class="skills-tag-container" id="onb-skills-box">
              <input type="text" class="skill-tag-input" placeholder="Add skill & press Enter...">
            </div>
            <input type="hidden" name="skills" id="onb-skills-hidden" value="<?= htmlspecialchars(json_encode($skillsArray)) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Career Goals</label>
            <textarea name="career_goals" id="field-goals" class="form-control" rows="2"
                      placeholder="Short-term career objectives..."><?= htmlspecialchars($user['career_goals'] ?? '') ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary flex-fill">
              Save Manually
            </button>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary flex-fill">
              Go to Dashboard
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initSkillsInput('onb-skills-box', 'onb-skills-hidden');

  const stream   = document.getElementById('inquiry-chat-stream');
  const form     = document.getElementById('inquiry-form');
  const input    = document.getElementById('inquiry-input');
  const sendBtn  = document.getElementById('inquiry-send-btn');
  const errBox   = document.getElementById('inquiry-error-box');

  stream.scrollTop = stream.scrollHeight;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    errBox.classList.add('d-none');
    btnLoading(sendBtn, true);

    try {
      const res = await Http.post('<?= BASE_URL ?>/onboarding/reply', { message: msg });

      const userBubble = document.createElement('div');
      userBubble.className = 'chat-msg user';
      userBubble.textContent = msg;
      stream.appendChild(userBubble);

      const aiBubble = document.createElement('div');
      aiBubble.className = 'chat-msg assistant';
      aiBubble.textContent = res.reply;
      stream.appendChild(aiBubble);

      input.value = '';
      stream.scrollTop = stream.scrollHeight;

      if (res.profile) {
        if (res.profile.target_role) document.getElementById('field-target-role').value = res.profile.target_role;
        if (res.profile.experience_level) document.getElementById('field-exp-level').value = res.profile.experience_level;
        if (res.profile.work_preference) document.getElementById('field-work-pref').value = res.profile.work_preference;
        if (res.profile.preferred_location) document.getElementById('field-location').value = res.profile.preferred_location;
        if (res.profile.career_goals) document.getElementById('field-goals').value = res.profile.career_goals;
        if (Array.isArray(res.profile.skills)) {
          document.getElementById('onb-skills-hidden').value = JSON.stringify(res.profile.skills);
          initSkillsInput('onb-skills-box', 'onb-skills-hidden');
        }
      }

      if (res.completed) {
        document.getElementById('inquiry-status-badge').textContent = 'Completed';
        Toast.success('Profile onboarding completed.');
      }
    } catch (err) {
      errBox.textContent = err.message || 'Unable to reach AI service. Your text is preserved; you may save fields manually.';
      errBox.classList.remove('d-none');
    } finally {
      btnLoading(sendBtn, false);
    }
  });
});
</script>
