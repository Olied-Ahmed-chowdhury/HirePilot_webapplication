-- ============================================================
-- HirePilot AI — Database Schema
-- Author : Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
-- Engine : MySQL 8.x  |  Charset: utf8mb4_unicode_ci
-- Run    : mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS hirepilot_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hirepilot_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. USERS ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120)    NOT NULL,
    email           VARCHAR(180)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NULL COMMENT 'NULL when using Google OAuth',
    role            ENUM('user','admin') NOT NULL DEFAULT 'user',
    avatar          VARCHAR(255)    NULL COMMENT 'Profile picture URL or upload path',

    -- Profile / preferences
    headline        VARCHAR(200)    NULL COMMENT 'e.g. "Full-Stack Developer"',
    bio             TEXT            NULL,
    phone           VARCHAR(30)     NULL,
    location        VARCHAR(100)    NULL,
    remote_pref     ENUM('remote','onsite','hybrid','any') DEFAULT 'any',
    target_role     VARCHAR(120)    NULL,
    experience_yrs  TINYINT UNSIGNED DEFAULT 0,
    skills          JSON            NULL COMMENT 'Array of skill strings',
    linkedin_url    VARCHAR(255)    NULL,
    github_url      VARCHAR(255)    NULL,
    portfolio_url   VARCHAR(255)    NULL,

    -- Google OAuth
    google_id       VARCHAR(100)    NULL UNIQUE,
    google_token    TEXT            NULL,

    -- Account state
    email_verified  TINYINT(1)      NOT NULL DEFAULT 0,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    last_login_at   DATETIME        NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_email  (email),
    INDEX idx_users_role   (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 2. RESUMES ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS resumes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    filename        VARCHAR(255)    NOT NULL COMMENT 'Safe server-side filename',
    original_name   VARCHAR(255)    NOT NULL COMMENT 'Original upload name',
    mime_type       VARCHAR(80)     NOT NULL,
    file_size       INT UNSIGNED    NOT NULL COMMENT 'Bytes',
    file_path       VARCHAR(500)    NOT NULL COMMENT 'Relative path in storage/',
    is_primary      TINYINT(1)      NOT NULL DEFAULT 0,
    label           VARCHAR(100)    NULL COMMENT 'User-given label e.g. "Software Dev Resume"',
    parsed_text     LONGTEXT        NULL COMMENT 'Extracted text for AI context',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_resumes_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_resumes_user    (user_id),
    INDEX idx_resumes_primary (user_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 3. JOBS ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS jobs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by      INT UNSIGNED    NOT NULL COMMENT 'Admin user who posted',
    title           VARCHAR(200)    NOT NULL,
    company         VARCHAR(150)    NOT NULL,
    company_logo    VARCHAR(255)    NULL,
    location        VARCHAR(150)    NULL,
    is_remote       TINYINT(1)      NOT NULL DEFAULT 0,
    job_type        ENUM('full-time','part-time','contract','internship','freelance')
                                    NOT NULL DEFAULT 'full-time',
    experience_level ENUM('entry','mid','senior','lead','executive')
                                    NOT NULL DEFAULT 'mid',
    salary_min      DECIMAL(12,2)   NULL,
    salary_max      DECIMAL(12,2)   NULL,
    salary_currency VARCHAR(5)      NOT NULL DEFAULT 'BDT',
    description     LONGTEXT        NOT NULL,
    requirements    TEXT            NULL COMMENT 'Bullet-point requirements',
    benefits        TEXT            NULL,
    skills_required JSON            NULL COMMENT 'Array of required skill strings',
    tags            JSON            NULL COMMENT 'Extra searchable tags',
    application_url VARCHAR(500)    NULL COMMENT 'External apply link (optional)',
    deadline        DATE            NULL,
    status          ENUM('active','paused','closed') NOT NULL DEFAULT 'active',
    view_count      INT UNSIGNED    NOT NULL DEFAULT 0,
    apply_count     INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_jobs_creator FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE RESTRICT,

    INDEX idx_jobs_status   (status),
    INDEX idx_jobs_type     (job_type),
    INDEX idx_jobs_remote   (is_remote),
    INDEX idx_jobs_level    (experience_level),
    INDEX idx_jobs_deadline (deadline),
    FULLTEXT INDEX ft_jobs_search (title, company, description, requirements)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 4. APPLICATIONS ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS applications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED    NOT NULL,
    job_id              INT UNSIGNED    NOT NULL,
    resume_id           INT UNSIGNED    NULL COMMENT 'Which resume was used',
    email_draft_id      INT UNSIGNED    NULL COMMENT 'Associated cover letter/email draft',

    status              ENUM(
                            'saved',
                            'applied',
                            'under_review',
                            'interview_scheduled',
                            'offer_received',
                            'rejected',
                            'withdrawn'
                        ) NOT NULL DEFAULT 'saved',

    cover_letter        TEXT            NULL COMMENT 'Submitted cover letter text',
    custom_notes        TEXT            NULL COMMENT 'Private applicant notes',
    company_response    TEXT            NULL COMMENT 'HR/company reply notes',
    applied_at          DATETIME        NULL,
    interview_date      DATETIME        NULL,
    offer_amount        DECIMAL(12,2)   NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_apps_user    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_apps_job     FOREIGN KEY (job_id)    REFERENCES jobs(id)    ON DELETE CASCADE,
    CONSTRAINT fk_apps_resume  FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL,

    UNIQUE KEY uq_user_job (user_id, job_id),
    INDEX idx_apps_user   (user_id),
    INDEX idx_apps_job    (job_id),
    INDEX idx_apps_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 5. EMAIL DRAFTS ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS email_drafts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    job_id          INT UNSIGNED    NULL,
    resume_id       INT UNSIGNED    NULL,
    application_id  INT UNSIGNED    NULL,

    draft_type      ENUM('cover_letter','application_email','follow_up','thank_you')
                                    NOT NULL DEFAULT 'cover_letter',

    subject         VARCHAR(300)    NULL,
    body            LONGTEXT        NOT NULL,
    ai_prompt_used  TEXT            NULL COMMENT 'The prompt sent to the AI',
    ai_model_used   VARCHAR(80)     NULL COMMENT 'Model identifier (grok-beta, gpt-4o, etc.)',

    status          ENUM('draft','approved','sent','archived')
                                    NOT NULL DEFAULT 'draft',

    recipient_email VARCHAR(180)    NULL,
    sent_at         DATETIME        NULL,
    send_log        TEXT            NULL COMMENT 'JSON log of send attempt',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_drafts_user   FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    CONSTRAINT fk_drafts_job    FOREIGN KEY (job_id)         REFERENCES jobs(id)         ON DELETE SET NULL,
    CONSTRAINT fk_drafts_resume FOREIGN KEY (resume_id)      REFERENCES resumes(id)      ON DELETE SET NULL,
    CONSTRAINT fk_drafts_app    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,

    INDEX idx_drafts_user   (user_id),
    INDEX idx_drafts_job    (job_id),
    INDEX idx_drafts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 6. NOTIFICATIONS ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    type            VARCHAR(80)     NOT NULL COMMENT 'e.g. application_status, new_job, email_sent',
    title           VARCHAR(200)    NOT NULL,
    message         TEXT            NOT NULL,
    link            VARCHAR(500)    NULL COMMENT 'Optional action URL',
    reference_id    INT UNSIGNED    NULL COMMENT 'Related entity id (application, job, etc.)',
    reference_type  VARCHAR(60)     NULL COMMENT 'Type of related entity',
    is_read         TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notif_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_notif_user    (user_id),
    INDEX idx_notif_unread  (user_id, is_read),
    INDEX idx_notif_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── 7. CSRF TOKENS (server-side token store) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS csrf_tokens (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token       VARCHAR(64)   NOT NULL UNIQUE,
    user_id     INT UNSIGNED  NULL,
    session_id  VARCHAR(128)  NOT NULL,
    expires_at  DATETIME      NOT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_csrf_token   (token),
    INDEX idx_csrf_session (session_id),
    INDEX idx_csrf_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;

