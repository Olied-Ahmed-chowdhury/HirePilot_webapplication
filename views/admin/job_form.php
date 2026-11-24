<?php
/**
 * HirePilot AI — Admin Job Create & Edit Form
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

use HirePilot\Core\Csrf;

$isEdit = !empty($job);
$action = $isEdit ? BASE_URL . "/admin/jobs/{$job['id']}" : BASE_URL . "/admin/jobs";

$skillsArray = [];
if ($isEdit && !empty($job['skills_required'])) {
    $skillsArray = is_string($job['skills_required']) ? (json_decode($job['skills_required'], true) ?: []) : $job['skills_required'];
}
?>
<div class="mb-3">
  <a href="<?= BASE_URL ?>/admin/jobs" class="text-muted small"><i class="bi bi-arrow-left me-1"></i> Back to jobs list</a>
</div>

<div class="hp-card p-4">
  <h4 class="fw-800 mb-4"><?= $isEdit ? 'Edit Job Posting' : 'Create New Job Opportunity' ?></h4>

  <form action="<?= $action ?>" method="POST">
    <?= Csrf::field() ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Job Title *</label>
        <input type="text" name="title" class="form-control" placeholder="e.g. Senior Backend Engineer" value="<?= htmlspecialchars($job['title'] ?? '') ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Company Name *</label>
        <input type="text" name="company" class="form-control" placeholder="e.g. Acme Technologies" value="<?= htmlspecialchars($job['company'] ?? '') ?>" required>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Location / City</label>
        <input type="text" name="location" class="form-control" placeholder="e.g. Dhaka, Bangladesh or Remote" value="<?= htmlspecialchars($job['location'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Job Type</label>
        <select name="job_type" class="form-select">
          <option value="full-time" <?= ($job['job_type'] ?? '') === 'full-time' ? 'selected' : '' ?>>Full-Time</option>
          <option value="part-time" <?= ($job['job_type'] ?? '') === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
          <option value="contract" <?= ($job['job_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
          <option value="internship" <?= ($job['job_type'] ?? '') === 'internship' ? 'selected' : '' ?>>Internship</option>
          <option value="freelance" <?= ($job['job_type'] ?? '') === 'freelance' ? 'selected' : '' ?>>Freelance</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Experience Level</label>
        <select name="experience_level" class="form-select">
          <option value="entry" <?= ($job['experience_level'] ?? '') === 'entry' ? 'selected' : '' ?>>Entry Level</option>
          <option value="mid" <?= ($job['experience_level'] ?? 'mid') === 'mid' ? 'selected' : '' ?>>Mid Level</option>
          <option value="senior" <?= ($job['experience_level'] ?? '') === 'senior' ? 'selected' : '' ?>>Senior Level</option>
          <option value="lead" <?= ($job['experience_level'] ?? '') === 'lead' ? 'selected' : '' ?>>Lead / Architect</option>
        </select>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Min Salary (Monthly/Annual)</label>
        <input type="number" step="0.01" name="salary_min" class="form-control" placeholder="e.g. 50000" value="<?= htmlspecialchars((string)($job['salary_min'] ?? '')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Max Salary</label>
        <input type="number" step="0.01" name="salary_max" class="form-control" placeholder="e.g. 80000" value="<?= htmlspecialchars((string)($job['salary_max'] ?? '')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Currency</label>
        <input type="text" name="salary_currency" class="form-control" value="<?= htmlspecialchars($job['salary_currency'] ?? 'BDT') ?>">
      </div>
    </div>

    <!-- Required Skills Tag Box -->
    <div class="mb-3">
      <label class="form-label">Required Skills (Powering AI Relevance Engine)</label>
      <div class="skills-tag-container" id="admin-job-skills-box">
        <input type="text" class="skill-tag-input" placeholder="Type skill (e.g. PHP, Laravel, Docker) & press Enter...">
      </div>
      <input type="hidden" name="skills_required" id="admin-job-skills-hidden" value="<?= htmlspecialchars(json_encode($skillsArray)) ?>">
      <div class="form-text">Type a skill name and press Enter or comma.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Job Description *</label>
      <textarea name="description" class="form-control" rows="6" placeholder="Detailed job overview, company mission, role summary..." required><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Requirements & Qualifications</label>
      <textarea name="requirements" class="form-control" rows="4" placeholder="• 2+ years PHP OOP experience&#10;• Experience with MySQL query optimization..."><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Perks & Benefits</label>
      <textarea name="benefits" class="form-control" rows="3" placeholder="• Flexible remote options&#10;• Annual bonus & health insurance..."><?= htmlspecialchars($job['benefits'] ?? '') ?></textarea>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Application Deadline</label>
        <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($job['deadline'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Publication Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= ($job['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible to Job Seekers)</option>
          <option value="paused" <?= ($job['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
          <option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
      </div>

      <div class="col-md-4 d-flex align-items-end">
        <div class="form-check pb-2">
          <input class="form-check-input" type="checkbox" name="is_remote" value="1" id="is_remote_check" <?= !empty($job['is_remote']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold" for="is_remote_check">
            100% Remote Opportunity
          </label>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <a href="<?= BASE_URL ?>/admin/jobs" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary px-4 py-2">
        <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Publish Job Listing' ?>
      </button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initSkillsInput('admin-job-skills-box', 'admin-job-skills-hidden');
});
</script>
