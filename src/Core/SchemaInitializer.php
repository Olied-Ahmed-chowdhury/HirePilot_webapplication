<?php
/**
 * HirePilot AI — Portable Schema Initializer & Auto-Seeder
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Ensures all required tables exist in both MySQL and SQLite modes,
 * and seeds demo/admin accounts + initial jobs on first run.
 */

declare(strict_types=1);

namespace HirePilot\Core;

class SchemaInitializer
{
    private static bool $checked = false;

    public static function ensureInitialized(Database $db): void
    {
        if (self::$checked) {
            return;
        }
        self::$checked = true;

        $driver = $db->getDriver();
        $pdo    = $db->getConnection();

        // Check if core tables exist
        if ($driver === 'sqlite') {
            $exists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_profiles'")->fetch();
        } else {
            $exists = $pdo->query("SHOW TABLES LIKE 'user_profiles'")->fetch();
        }

        if (!$exists) {
            self::createTables($db, $driver);
        }

        // Seed only if users table is empty
        $userCount = (int) ($db->fetchOne("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
        if ($userCount === 0) {
            self::seedInitialData($db);
        }
    }

    private static function createTables(Database $db, string $driver): void
    {
        $pk     = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $engine = $driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';

        $statements = [
            "CREATE TABLE IF NOT EXISTS users (
                id {$pk},
                name VARCHAR(120) NOT NULL,
                email VARCHAR(180) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'user',
                onboarding_completed TINYINT NOT NULL DEFAULT 0,
                is_active TINYINT NOT NULL DEFAULT 1,
                last_login_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS user_profiles (
                user_id INT PRIMARY KEY,
                headline VARCHAR(200) NULL,
                bio TEXT NULL,
                phone VARCHAR(40) NULL,
                target_role VARCHAR(150) NULL,
                experience_level VARCHAR(50) NULL,
                experience_yrs INT NOT NULL DEFAULT 0,
                skills TEXT NULL,
                work_preference VARCHAR(40) NOT NULL DEFAULT 'any',
                preferred_location VARCHAR(150) NULL,
                career_goals TEXT NULL,
                ai_inquiry_history TEXT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS resumes (
                id {$pk},
                user_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type VARCHAR(120) NOT NULL,
                file_size INT NOT NULL,
                is_primary TINYINT NOT NULL DEFAULT 0,
                label VARCHAR(120) NULL,
                parsed_text TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS jobs (
                id {$pk},
                created_by INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                company VARCHAR(150) NOT NULL,
                location VARCHAR(150) NULL,
                is_remote TINYINT NOT NULL DEFAULT 0,
                job_type VARCHAR(40) NOT NULL DEFAULT 'full-time',
                experience_level VARCHAR(40) NOT NULL DEFAULT 'mid',
                salary_min DECIMAL(12,2) NULL,
                salary_max DECIMAL(12,2) NULL,
                salary_currency VARCHAR(10) NOT NULL DEFAULT 'BDT',
                description TEXT NOT NULL,
                requirements TEXT NULL,
                benefits TEXT NULL,
                skills_required TEXT NULL,
                external_url VARCHAR(500) NULL,
                deadline DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                view_count INT NOT NULL DEFAULT 0,
                apply_count INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS applications (
                id {$pk},
                user_id INT NOT NULL,
                job_id INT NULL,
                resume_id INT NULL,
                company_name VARCHAR(150) NULL,
                job_title VARCHAR(200) NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'saved',
                cover_letter TEXT NULL,
                custom_notes TEXT NULL,
                company_response TEXT NULL,
                interview_date DATETIME NULL,
                offer_amount DECIMAL(12,2) NULL,
                applied_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
                FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS email_drafts (
                id {$pk},
                user_id INT NOT NULL,
                application_id INT NULL,
                job_id INT NULL,
                resume_id INT NULL,
                draft_type VARCHAR(40) NOT NULL DEFAULT 'cover_letter',
                subject VARCHAR(300) NULL,
                body TEXT NOT NULL,
                ai_model_used VARCHAR(100) NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'draft',
                recipient_email VARCHAR(180) NULL,
                sent_at DATETIME NULL,
                send_log TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
                FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL
            ){$engine}",

            "CREATE TABLE IF NOT EXISTS notifications (
                id {$pk},
                user_id INT NOT NULL,
                type VARCHAR(60) NOT NULL DEFAULT 'system',
                title VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                link VARCHAR(500) NULL,
                is_read TINYINT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ){$engine}"
        ];

        $pdo = $db->getConnection();
        foreach ($statements as $sql) {
            $pdo->exec($sql);
        }

        // Portable indexes
        $indexes = [
            "CREATE INDEX idx_apps_user_status ON applications(user_id, status)",
            "CREATE INDEX idx_notif_user_read ON notifications(user_id, is_read)",
            "CREATE INDEX idx_jobs_status ON jobs(status)",
            "CREATE INDEX idx_drafts_user ON email_drafts(user_id)"
        ];
        foreach ($indexes as $idxSql) {
            try {
                $pdo->exec($idxSql);
            } catch (\Throwable) {
                // Ignore if already exists
            }
        }
    }

    private static function seedInitialData(Database $db): void
    {
        $now = date('Y-m-d H:i:s');
        $adminHash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 10]);
        $demoHash  = password_hash('Demo@1234', PASSWORD_BCRYPT, ['cost' => 10]);

        $db->beginTransaction();
        try {
            // 1. Admin User (id 1)
            $db->execute(
                "INSERT INTO users (name, email, password_hash, role, onboarding_completed, is_active, created_at, updated_at)
                 VALUES (:name, :email, :hash, 'admin', 1, 1, :ca, :ua)",
                [':name' => 'Admin User', ':email' => 'admin@hirepilot.ai', ':hash' => $adminHash, ':ca' => $now, ':ua' => $now]
            );
            $adminId = (int) $db->lastInsertId();

            $db->execute(
                "INSERT INTO user_profiles (user_id, headline, target_role, experience_level, experience_yrs, skills, work_preference, preferred_location, updated_at)
                 VALUES (:uid, 'System Administrator', 'Platform Operations', 'senior', 5, :skills, 'any', 'Dhaka, Bangladesh', :ua)",
                [':uid' => $adminId, ':skills' => json_encode(['PHP', 'MySQL', 'SQLite', 'System Administration']), ':ua' => $now]
            );

            // 2. Demo Candidate User (id 2)
            $db->execute(
                "INSERT INTO users (name, email, password_hash, role, onboarding_completed, is_active, created_at, updated_at)
                 VALUES (:name, :email, :hash, 'user', 1, 1, :ca, :ua)",
                [':name' => 'Olied Ahmed (Demo)', ':email' => 'demo@hirepilot.ai', ':hash' => $demoHash, ':ca' => $now, ':ua' => $now]
            );
            $demoId = (int) $db->lastInsertId();

            $db->execute(
                "INSERT INTO user_profiles (user_id, headline, bio, target_role, experience_level, experience_yrs, skills, work_preference, preferred_location, career_goals, updated_at)
                 VALUES (:uid, :hl, :bio, :role, 'mid', 2, :skills, 'hybrid', 'Dhaka, Bangladesh', :goals, :ua)",
                [
                    ':uid'    => $demoId,
                    ':hl'     => 'Full-Stack Web Developer',
                    ':bio'    => 'Software Engineering student and full-stack developer experienced in PHP OOP, MySQL, JavaScript, and REST APIs.',
                    ':role'   => 'Full-Stack Developer',
                    ':skills' => json_encode(['PHP', 'MySQL', 'JavaScript', 'HTML5', 'CSS3', 'Bootstrap', 'REST API', 'Git', 'OOP']),
                    ':goals'  => 'Join an engineering team building high-reliability web software.',
                    ':ua'     => $now,
                ]
            );

            // 3. Seed 8 realistic jobs
            $jobs = [
                [
                    'title' => 'Junior PHP Backend Developer',
                    'company' => 'TechNova BD',
                    'location' => 'Dhaka, Bangladesh',
                    'is_remote' => 0,
                    'job_type' => 'full-time',
                    'experience_level' => 'entry',
                    'salary_min' => 30000,
                    'salary_max' => 45000,
                    'skills' => ['PHP', 'MySQL', 'OOP', 'REST API', 'Git', 'Bootstrap'],
                    'desc' => "TechNova BD is hiring a Junior PHP Developer to build and maintain production web platforms using OOP PHP and relational databases.\n\nYou will collaborate with senior engineers on API endpoints, query optimization, and automated testing.",
                    'req' => "- BSc in Software Engineering / CSE\n- Solid understanding of PHP 8 OOP and PDO\n- Experience with MySQL, HTML5, CSS3, JavaScript\n- Familiarity with Git version control",
                    'url' => 'https://example.com/careers/technova-php'
                ],
                [
                    'title' => 'Full-Stack Laravel & Vue Developer',
                    'company' => 'SoftByte Solutions',
                    'location' => 'Remote',
                    'is_remote' => 1,
                    'job_type' => 'full-time',
                    'experience_level' => 'mid',
                    'salary_min' => 65000,
                    'salary_max' => 95000,
                    'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL', 'REST API', 'Docker'],
                    'desc' => "SoftByte Solutions builds B2B SaaS products for international clients. We need a Full-Stack Developer comfortable owning features end-to-end across Laravel APIs and modern JavaScript frontends.",
                    'req' => "- 2+ years experience with PHP / Laravel\n- Strong relational database design skills\n- Experience integrating third-party REST APIs\n- Clear written communication for remote collaboration",
                    'url' => 'https://example.com/careers/softbyte-fullstack'
                ],
                [
                    'title' => 'Frontend Engineer (JavaScript / React)',
                    'company' => 'PixelCraft Systems',
                    'location' => 'Chittagong, Bangladesh',
                    'is_remote' => 0,
                    'job_type' => 'full-time',
                    'experience_level' => 'mid',
                    'salary_min' => 50000,
                    'salary_max' => 75000,
                    'skills' => ['JavaScript', 'React', 'HTML5', 'CSS3', 'Bootstrap', 'Git'],
                    'desc' => "Join PixelCraft Systems to build responsive, accessible web interfaces and data-dense enterprise dashboards.",
                    'req' => "- 2+ years modern JavaScript (ES6+)\n- Deep knowledge of CSS Grid, Flexbox, and component architecture\n- Experience consuming JSON REST APIs",
                    'url' => 'https://example.com/careers/pixelcraft-frontend'
                ],
                [
                    'title' => 'Backend Python & FastAPI Engineer',
                    'company' => 'DataDriven Inc.',
                    'location' => 'Remote',
                    'is_remote' => 1,
                    'job_type' => 'full-time',
                    'experience_level' => 'senior',
                    'salary_min' => 110000,
                    'salary_max' => 160000,
                    'skills' => ['Python', 'FastAPI', 'PostgreSQL', 'Docker', 'REST API', 'AWS'],
                    'desc' => "Design high-throughput APIs and data processing microservices for analytics pipelines.",
                    'req' => "- 4+ years Python backend engineering\n- Strong SQL optimization and distributed systems experience\n- Production experience with Docker and CI/CD",
                    'url' => 'https://example.com/careers/datadriven-python'
                ],
                [
                    'title' => 'DevOps & Cloud Reliability Engineer',
                    'company' => 'CloudBridge Technologies',
                    'location' => 'Dhaka, Bangladesh',
                    'is_remote' => 1,
                    'job_type' => 'full-time',
                    'experience_level' => 'mid',
                    'salary_min' => 75000,
                    'salary_max' => 115000,
                    'skills' => ['Linux', 'Docker', 'Kubernetes', 'AWS', 'CI/CD', 'Git'],
                    'desc' => "Automate infrastructure provisioning, container orchestration, and observability across multi-region deployments.",
                    'req' => "- 2+ years managing Linux servers and Docker\n- Experience with GitHub Actions or GitLab CI\n- Knowledge of Nginx, Apache, and SSL/TLS",
                    'url' => 'https://example.com/careers/cloudbridge-devops'
                ],
                [
                    'title' => 'Software Engineering Intern',
                    'company' => 'StartupHub Bangladesh',
                    'location' => 'Dhaka, Bangladesh',
                    'is_remote' => 0,
                    'job_type' => 'internship',
                    'experience_level' => 'entry',
                    'salary_min' => 12000,
                    'salary_max' => 18000,
                    'skills' => ['PHP', 'HTML5', 'CSS3', 'JavaScript', 'MySQL', 'Git'],
                    'desc' => "A structured 4-month paid engineering internship for final-year Software Engineering / CSE students with mentorship and full-time conversion opportunity.",
                    'req' => "- Enrolled in Software Engineering or Computer Science\n- Academic or personal project experience with PHP/JS/SQL\n- Eagerness to learn production workflows",
                    'url' => 'https://example.com/careers/startuphub-intern'
                ],
                [
                    'title' => 'Mobile App Developer (Flutter)',
                    'company' => 'AppForge Ltd.',
                    'location' => 'Remote',
                    'is_remote' => 1,
                    'job_type' => 'contract',
                    'experience_level' => 'mid',
                    'salary_min' => 55000,
                    'salary_max' => 85000,
                    'skills' => ['Flutter', 'Dart', 'REST API', 'Firebase', 'Git'],
                    'desc' => "6-month remote contract building cross-platform iOS and Android client applications integrated with RESTful backends.",
                    'req' => "- 2+ years Flutter & Dart\n- Experience with state management and offline caching\n- Portfolio of shipped mobile apps",
                    'url' => 'https://example.com/careers/appforge-flutter'
                ],
                [
                    'title' => 'QA & Test Automation Engineer',
                    'company' => 'Veritas Software',
                    'location' => 'Sylhet, Bangladesh',
                    'is_remote' => 0,
                    'job_type' => 'full-time',
                    'experience_level' => 'entry',
                    'salary_min' => 35000,
                    'salary_max' => 55000,
                    'skills' => ['SQL', 'REST API', 'JavaScript', 'Git', 'Testing'],
                    'desc' => "Own end-to-end regression suites, API contract validation, and release quality gates.",
                    'req' => "- Understanding of SDLC, black-box/white-box testing\n- Ability to write SQL verification queries\n- Familiarity with Postman and browser DevTools",
                    'url' => 'https://example.com/careers/veritas-qa'
                ],
            ];

            foreach ($jobs as $j) {
                $db->execute(
                    "INSERT INTO jobs
                        (created_by, title, company, location, is_remote, job_type, experience_level,
                         salary_min, salary_max, salary_currency, description, requirements, skills_required,
                         external_url, status, created_at)
                     VALUES
                        (:cb, :title, :company, :loc, :rem, :jtype, :exp, :smin, :smax, 'BDT', :desc, :req, :skills, :url, 'active', :ca)",
                    [
                        ':cb'      => $adminId,
                        ':title'   => $j['title'],
                        ':company' => $j['company'],
                        ':loc'     => $j['location'],
                        ':rem'     => $j['is_remote'],
                        ':jtype'   => $j['job_type'],
                        ':exp'     => $j['experience_level'],
                        ':smin'    => $j['salary_min'],
                        ':smax'    => $j['salary_max'],
                        ':desc'    => $j['desc'],
                        ':req'     => $j['req'],
                        ':skills'  => json_encode($j['skills']),
                        ':url'     => $j['url'],
                        ':ca'      => $now,
                    ]
                );
            }

            // 4. Seed 1 sample application & notification for demo user
            $db->execute(
                "INSERT INTO applications (user_id, job_id, company_name, job_title, status, cover_letter, custom_notes, applied_at, created_at, updated_at)
                 VALUES (:uid, 1, 'TechNova BD', 'Junior PHP Backend Developer', 'applied', :cl, 'Submitted via HirePilot AI.', :aa, :ca, :ua)",
                [
                    ':uid' => $demoId,
                    ':cl'  => "Dear Hiring Team at TechNova BD,\n\nI am writing to apply for the Junior PHP Backend Developer role. With hands-on experience in PHP OOP, MySQL, REST APIs, and Bootstrap, I am prepared to contribute immediately to your engineering roadmap.\n\nSincerely,\nOlied Ahmed",
                    ':aa'  => $now,
                    ':ca'  => $now,
                    ':ua'  => $now,
                ]
            );

            $db->execute(
                "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                 VALUES (:uid, 'application_status', 'Application Submitted', 'Your application for Junior PHP Backend Developer at TechNova BD has been recorded.', '/applications', 0, :ca)",
                [':uid' => $demoId, ':ca' => $now]
            );

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
