<?php
/**
 * HirePilot AI — Front Controller & Route Registry
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use HirePilot\Core\Auth;
use HirePilot\Core\Router;
use HirePilot\Controllers\AuthController;
use HirePilot\Controllers\DashboardController;
use HirePilot\Controllers\ProfileController;
use HirePilot\Controllers\ResumeController;
use HirePilot\Controllers\JobController;
use HirePilot\Controllers\EmailDraftController;
use HirePilot\Controllers\ApplicationController;
use HirePilot\Controllers\NotificationController;
use HirePilot\Controllers\AdminController;

// ── Root / Landing Route ──────────────────────────────────────────────────────
Router::get('/', function() {
    if (Auth::check()) {
        Auth::redirect('/dashboard');
    }
    Auth::redirect('/auth/login');
});

// ── Authentication & Google OAuth Routes ──────────────────────────────────────
Router::get('/auth/login',           [AuthController::class, 'showLogin']);
Router::post('/auth/login',          [AuthController::class, 'login']);
Router::get('/auth/register',        [AuthController::class, 'showRegister']);
Router::post('/auth/register',       [AuthController::class, 'register']);
Router::get('/auth/logout',          [AuthController::class, 'logout']);
Router::get('/auth/google',          [AuthController::class, 'googleRedirect']);
Router::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// ── Candidate Dashboard ───────────────────────────────────────────────────────
Router::get('/dashboard', [DashboardController::class, 'index']);

// ── Profile & Account Security ────────────────────────────────────────────────
Router::get('/profile',                 [ProfileController::class, 'show']);
Router::post('/profile',                [ProfileController::class, 'update']);
Router::get('/profile/change-password', [ProfileController::class, 'showChangePassword']);
Router::post('/profile/change-password',[ProfileController::class, 'updatePassword']);

// ── Resumes & Document Management ─────────────────────────────────────────────
Router::get('/resume',                 [ResumeController::class, 'index']);
Router::post('/resume/upload',         [ResumeController::class, 'upload']);
Router::get('/resume/{id}/download',   [ResumeController::class, 'download']);
Router::post('/resume/{id}/primary',   [ResumeController::class, 'setPrimary']);
Router::post('/resume/{id}/delete',    [ResumeController::class, 'delete']);

// ── Job Discovery & Matching ──────────────────────────────────────────────────
Router::get('/jobs',      [JobController::class, 'index']);
Router::get('/jobs/{id}', [JobController::class, 'show']);

// ── AI Writing Hub (Grok Cover Letters & Emails) ──────────────────────────────
Router::get('/emails',              [EmailDraftController::class, 'index']);
Router::get('/emails/create',       [EmailDraftController::class, 'create']);
Router::post('/emails/generate',    [EmailDraftController::class, 'generate']);
Router::get('/emails/{id}',         [EmailDraftController::class, 'show']);
Router::post('/emails/{id}/update', [EmailDraftController::class, 'update']);
Router::post('/emails/{id}/approve',[EmailDraftController::class, 'approve']);
Router::post('/emails/{id}/send',   [EmailDraftController::class, 'send']);
Router::post('/emails/{id}/delete', [EmailDraftController::class, 'delete']);

// ── Applications Tracker & Pipeline ───────────────────────────────────────────
Router::get('/applications',                [ApplicationController::class, 'index']);
Router::post('/applications',               [ApplicationController::class, 'store']);
Router::get('/applications/{id}',           [ApplicationController::class, 'show']);
Router::post('/applications/{id}/status',   [ApplicationController::class, 'updateStatus']);
Router::post('/applications/{id}/details',  [ApplicationController::class, 'updateDetails']);
Router::post('/applications/{id}/delete',   [ApplicationController::class, 'delete']);

// ── Real-Time AJAX Notifications ──────────────────────────────────────────────
Router::get('/notifications/fetch',    [NotificationController::class, 'fetch']);
Router::post('/notifications/{id}/read',[NotificationController::class, 'markRead']);
Router::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

// ── Admin Portal & Job CMS ────────────────────────────────────────────────────
Router::get('/admin',                     [AdminController::class, 'dashboard']);
Router::get('/admin/jobs',                [AdminController::class, 'jobs']);
Router::get('/admin/jobs/create',         [AdminController::class, 'createJob']);
Router::post('/admin/jobs',               [AdminController::class, 'storeJob']);
Router::get('/admin/jobs/{id}/edit',      [AdminController::class, 'editJob']);
Router::post('/admin/jobs/{id}',          [AdminController::class, 'updateJob']);
Router::post('/admin/jobs/{id}/toggle',   [AdminController::class, 'toggleJobStatus']);
Router::post('/admin/jobs/{id}/delete',   [AdminController::class, 'deleteJob']);
Router::get('/admin/users',               [AdminController::class, 'users']);
Router::post('/admin/users/{id}/toggle',  [AdminController::class, 'toggleUserStatus']);

// Dispatch request
Router::dispatch();
