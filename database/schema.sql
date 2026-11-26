-- ============================================================
-- HirePilot AI — Portable Database Schema (SRS v1.3)
-- Compatible with MySQL 8.x & SQLite 3 (via SchemaInitializer)
-- Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
-- ============================================================

CREATE DATABASE IF NOT EXISTS hirepilot_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hirepilot_db;

CREATE TABLE IF NOT EXISTS users (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    name                 VARCHAR(120) NOT NULL,
    email                VARCHAR(180) NOT NULL UNIQUE,
    password_hash        VARCHAR(255) NOT NULL,
    role                 VARCHAR(20)  NOT NULL DEFAULT 'user',
    onboarding_completed TINYINT      NOT NULL DEFAULT 0,
    is_active            TINYINT      NOT NULL DEFAULT 1,
    last_login_at        DATETIME     NULL,
    created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id            INT PRIMARY KEY,
    headline           VARCHAR(200) NULL,
    bio                TEXT         NULL,
    phone              VARCHAR(40)  NULL,
    target_role        VARCHAR(150) NULL,
    experience_level   VARCHAR(50)  NULL,
    experience_yrs     INT          NOT NULL DEFAULT 0,
    skills             TEXT         NULL,
    work_preference    VARCHAR(40)  NOT NULL DEFAULT 'any',
    preferred_location VARCHAR(150) NULL,
    career_goals       TEXT         NULL,
    ai_inquiry_history TEXT         NULL,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resumes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(120) NOT NULL,
    file_size     INT          NOT NULL,
    is_primary    TINYINT      NOT NULL DEFAULT 0,
    label         VARCHAR(120) NULL,
    parsed_text   TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    created_by       INT           NOT NULL,
    title            VARCHAR(200)  NOT NULL,
    company          VARCHAR(150)  NOT NULL,
    location         VARCHAR(150)  NULL,
    is_remote        TINYINT       NOT NULL DEFAULT 0,
    job_type         VARCHAR(40)   NOT NULL DEFAULT 'full-time',
    experience_level VARCHAR(40)   NOT NULL DEFAULT 'mid',
    salary_min       DECIMAL(12,2) NULL,
    salary_max       DECIMAL(12,2) NULL,
    salary_currency  VARCHAR(10)   NOT NULL DEFAULT 'BDT',
    description      TEXT          NOT NULL,
    requirements     TEXT          NULL,
    benefits         TEXT          NULL,
    skills_required  TEXT          NULL,
    external_url     VARCHAR(500)  NULL,
    deadline         DATE          NULL,
    status           VARCHAR(20)   NOT NULL DEFAULT 'active',
    view_count       INT           NOT NULL DEFAULT 0,
    apply_count      INT           NOT NULL DEFAULT 0,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT           NOT NULL,
    job_id           INT           NULL,
    resume_id        INT           NULL,
    company_name     VARCHAR(150)  NULL,
    job_title        VARCHAR(200)  NULL,
    status           VARCHAR(40)   NOT NULL DEFAULT 'saved',
    cover_letter     TEXT          NULL,
    custom_notes     TEXT          NULL,
    company_response TEXT          NULL,
    interview_date   DATETIME      NULL,
    offer_amount     DECIMAL(12,2) NULL,
    applied_at       DATETIME      NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_drafts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          NOT NULL,
    application_id  INT          NULL,
    job_id          INT          NULL,
    resume_id       INT          NULL,
    draft_type      VARCHAR(40)  NOT NULL DEFAULT 'cover_letter',
    subject         VARCHAR(300) NULL,
    body            TEXT         NOT NULL,
    ai_model_used   VARCHAR(100) NULL,
    status          VARCHAR(30)  NOT NULL DEFAULT 'draft',
    recipient_email VARCHAR(180) NULL,
    sent_at         DATETIME     NULL,
    send_log        TEXT         NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    type       VARCHAR(60)  NOT NULL DEFAULT 'system',
    title      VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    link       VARCHAR(500) NULL,
    is_read    TINYINT      NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_apps_user_status ON applications(user_id, status);
CREATE INDEX idx_notif_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_jobs_status ON jobs(status);
CREATE INDEX idx_drafts_user ON email_drafts(user_id);
