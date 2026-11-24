-- ============================================================
-- HirePilot AI — Seed Data
-- Author : Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
-- Run AFTER schema.sql:  mysql -u root -p hirepilot_db < database/seed.sql
--
-- Demo credentials:
--   Admin  → admin@hirepilot.ai   / Admin@1234
--   User   → demo@hirepilot.ai    / Demo@1234
-- ============================================================

USE hirepilot_db;

-- ── Disable safe-update mode temporarily ──────────────────────────────────────
SET SQL_SAFE_UPDATES = 0;

-- ── 1. USERS ─────────────────────────────────────────────────────────────────
-- Passwords hashed with PASSWORD_BCRYPT (cost 12)
-- Admin@1234 → $2y$12$V0qNk6e3TvC2DRJbBiOsKeKhbVj2zJWJZbA6HFI4N8yRoVw5BGTUK
-- Demo@1234  → $2y$12$Ue7Q2sP8l0TkgJmUv3JT6.mScvR1NCkbpLdF5ND9L7mSAdAMeLQJi

INSERT INTO users
    (name, email, password_hash, role, headline, bio, location, remote_pref,
     target_role, experience_yrs, skills, email_verified, is_active, created_at)
VALUES
(
    'Admin User',
    'admin@hirepilot.ai',
    '$2y$12$V0qNk6e3TvC2DRJbBiOsKeKhbVj2zJWJZbA6HFI4N8yRoVw5BGTUK',
    'admin',
    'HirePilot System Administrator',
    'Platform administrator managing job listings and users.',
    'Dhaka, Bangladesh',
    'any',
    'Product Manager',
    5,
    '["PHP","MySQL","System Administration","Project Management"]',
    1, 1,
    NOW()
),
(
    'Olied Ahmed (Demo)',
    'demo@hirepilot.ai',
    '$2y$12$Ue7Q2sP8l0TkgJmUv3JT6.mScvR1NCkbpLdF5ND9L7mSAdAMeLQJi',
    'user',
    'Full-Stack Web Developer',
    'Passionate PHP & JavaScript developer with hands-on experience building scalable web applications. Eager to join a dynamic team that values clean code and continuous learning.',
    'Dhaka, Bangladesh',
    'hybrid',
    'Full-Stack Developer',
    2,
    '["PHP","MySQL","JavaScript","HTML5","CSS3","Bootstrap","Laravel","Git","REST API","OOP"]',
    1, 1,
    NOW()
);

-- ── 2. JOBS ──────────────────────────────────────────────────────────────────
-- Posted by admin (id=1)

INSERT INTO jobs
    (created_by, title, company, location, is_remote, job_type, experience_level,
     salary_min, salary_max, salary_currency, description, requirements,
     benefits, skills_required, tags, deadline, status, created_at)
VALUES

-- Job 1
(
    1,
    'Junior PHP Developer',
    'TechNova BD',
    'Dhaka, Bangladesh',
    0,
    'full-time',
    'entry',
    25000.00, 40000.00, 'BDT',
    'TechNova BD is looking for a talented Junior PHP Developer to join our growing engineering team. You will build and maintain scalable web applications, collaborate with senior developers, and ship real features from day one.\n\nYou will work in an Agile environment with a focus on clean, testable code and continuous delivery.',
    '• BSc in CSE or equivalent\n• 0–2 years PHP experience\n• Familiarity with MySQL, HTML5, CSS3, Bootstrap\n• Understanding of REST APIs\n• Good problem-solving skills',
    '• Competitive salary\n• Remote-friendly Friday\n• Training budget\n• Medical insurance',
    '["PHP","MySQL","HTML5","CSS3","Bootstrap","OOP","Git"]',
    '["web","backend","entry-level","php"]',
    DATE_ADD(CURDATE(), INTERVAL 30 DAY),
    'active',
    NOW()
),

-- Job 2
(
    1,
    'Full-Stack Laravel Developer',
    'SoftByte Solutions',
    'Dhaka, Bangladesh',
    1,
    'full-time',
    'mid',
    60000.00, 90000.00, 'BDT',
    'SoftByte Solutions is hiring an experienced Full-Stack Laravel Developer to build enterprise-grade SaaS products. You will own end-to-end feature development across backend APIs and Vue.js front-ends.\n\nWe believe in developer autonomy, work-life balance, and continuous growth.',
    '• 2+ years Laravel experience\n• Strong Vue.js or React skills\n• REST API design & consumption\n• MySQL query optimization\n• Docker familiarity is a plus',
    '• 100% remote work\n• Annual performance bonus\n• 20 days paid leave\n• Home office stipend',
    '["Laravel","PHP","Vue.js","MySQL","REST API","Docker","Git","OOP"]',
    '["remote","laravel","full-stack","saas"]',
    DATE_ADD(CURDATE(), INTERVAL 25 DAY),
    'active',
    NOW()
),

-- Job 3
(
    1,
    'Frontend Developer (React)',
    'PixelCraft Agency',
    'Chittagong, Bangladesh',
    0,
    'full-time',
    'mid',
    45000.00, 70000.00, 'BDT',
    'PixelCraft Agency crafts beautiful digital experiences for global brands. We are looking for a creative Frontend Developer who loves turning Figma designs into pixel-perfect React components.\n\nYou will collaborate closely with our UI/UX team and backend engineers in a fast-paced agency environment.',
    '• 2+ years React.js experience\n• Strong CSS3 / Tailwind CSS skills\n• Figma-to-code workflow experience\n• REST API integration\n• Performance optimization',
    '• Creative work environment\n• Flexible hours\n• Regular team events\n• Learning & conference budget',
    '["React.js","JavaScript","CSS3","Tailwind CSS","Figma","REST API","Git"]',
    '["frontend","react","agency","ui"]',
    DATE_ADD(CURDATE(), INTERVAL 20 DAY),
    'active',
    NOW()
),

-- Job 4
(
    1,
    'Backend Python Developer',
    'DataDriven Inc.',
    'Remote',
    1,
    'full-time',
    'senior',
    100000.00, 150000.00, 'BDT',
    'DataDriven Inc. powers data analytics for Fortune-500 companies. We need a Senior Backend Python Developer to build high-throughput data pipelines, REST APIs, and ML model-serving infrastructure.',
    '• 4+ years Python experience\n• Django or FastAPI proficiency\n• PostgreSQL / Redis expertise\n• Experience with AWS or GCP\n• Strong system design skills',
    '• Fully remote\n• USD salary equivalent\n• Stock options\n• 30 days paid leave',
    '["Python","Django","FastAPI","PostgreSQL","Redis","AWS","Docker","CI/CD"]',
    '["python","backend","senior","remote","data"]',
    DATE_ADD(CURDATE(), INTERVAL 15 DAY),
    'active',
    NOW()
),

-- Job 5
(
    1,
    'DevOps Engineer',
    'CloudBridge Technologies',
    'Dhaka, Bangladesh',
    1,
    'full-time',
    'mid',
    70000.00, 110000.00, 'BDT',
    'CloudBridge Technologies is building the next-generation cloud infrastructure platform. We are looking for a DevOps Engineer to manage CI/CD pipelines, Kubernetes clusters, and cloud deployments across AWS & Azure.',
    '• 2+ years DevOps / SRE experience\n• Kubernetes & Docker proficiency\n• Terraform or Ansible experience\n• Strong Linux administration\n• Monitoring with Prometheus/Grafana',
    '• Remote-first culture\n• AWS/GCP certification support\n• Competitive salary\n• Annual training budget',
    '["DevOps","Kubernetes","Docker","Terraform","AWS","Linux","CI/CD","Prometheus"]',
    '["devops","cloud","kubernetes","remote"]',
    DATE_ADD(CURDATE(), INTERVAL 18 DAY),
    'active',
    NOW()
),

-- Job 6
(
    1,
    'UI/UX Designer',
    'CreativeMinds Studio',
    'Dhaka, Bangladesh',
    0,
    'full-time',
    'mid',
    40000.00, 65000.00, 'BDT',
    'CreativeMinds Studio designs world-class digital products. We are hiring a UI/UX Designer who is passionate about user-centered design and can deliver stunning interfaces that solve real user problems.',
    '• 2+ years UI/UX design experience\n• Proficiency in Figma\n• Strong portfolio of mobile & web designs\n• Understanding of accessibility (WCAG)\n• Basic front-end knowledge is a plus',
    '• Creative & collaborative environment\n• Flexible work hours\n• Design tool subscriptions\n• Regular design workshops',
    '["Figma","UI Design","UX Research","Prototyping","Adobe XD","Accessibility"]',
    '["design","ui","ux","figma","creative"]',
    DATE_ADD(CURDATE(), INTERVAL 22 DAY),
    'active',
    NOW()
),

-- Job 7
(
    1,
    'Mobile Developer (Flutter)',
    'AppForge Ltd.',
    'Remote',
    1,
    'contract',
    'mid',
    55000.00, 85000.00, 'BDT',
    'AppForge Ltd. develops high-quality cross-platform mobile apps for startups and enterprises. We are seeking a skilled Flutter Developer for a 6-month contract with strong potential for extension.',
    '• 2+ years Flutter development\n• Dart language proficiency\n• REST API integration experience\n• State management (Bloc/Riverpod)\n• Published app on Play Store/App Store',
    '• Fully remote contract\n• Flexible hours across time zones\n• Performance bonus',
    '["Flutter","Dart","Mobile Development","REST API","Firebase","Bloc","Git"]',
    '["mobile","flutter","dart","remote","contract"]',
    DATE_ADD(CURDATE(), INTERVAL 12 DAY),
    'active',
    NOW()
),

-- Job 8
(
    1,
    'Software Engineering Intern',
    'StartupHub Bangladesh',
    'Dhaka, Bangladesh',
    0,
    'internship',
    'entry',
    8000.00, 15000.00, 'BDT',
    'StartupHub Bangladesh is an early-stage startup building a talent marketplace. We offer a hands-on internship where you will contribute to real product features, not just documentation.\n\nGreat opportunity for CSE students in their 3rd or 4th year.',
    '• Currently enrolled in CS/CSE program\n• Basic knowledge of HTML, CSS, JavaScript or Python\n• Eagerness to learn and grow\n• Good communication skills',
    '• Paid internship\n• Mentorship from senior developers\n• Flexible hours around your schedule\n• Potential full-time offer',
    '["HTML","CSS","JavaScript","Python","Git","Problem Solving"]',
    '["internship","student","entry-level","startup"]',
    DATE_ADD(CURDATE(), INTERVAL 35 DAY),
    'active',
    NOW()
);

-- ── 3. SAMPLE APPLICATION (demo user → Job 1) ─────────────────────────────────
INSERT INTO applications
    (user_id, job_id, status, cover_letter, custom_notes, applied_at, created_at)
VALUES
(
    2,
    1,
    'applied',
    'Dear Hiring Team,\n\nI am excited to apply for the Junior PHP Developer position at TechNova BD. With 2 years of hands-on experience building PHP/MySQL web applications and a strong foundation in OOP, Bootstrap 5, and REST APIs, I am confident I can contribute meaningfully to your engineering team from day one.\n\nI look forward to the opportunity to discuss how my skills align with your needs.\n\nBest regards,\nOlied Ahmed (Demo)',
    'Applied via HirePilot AI — tailored cover letter generated by AI.',
    NOW(),
    NOW()
);

-- ── 4. SAMPLE NOTIFICATIONS ───────────────────────────────────────────────────
INSERT INTO notifications
    (user_id, type, title, message, link, reference_id, reference_type, is_read, created_at)
VALUES
(
    2,
    'application_status',
    'Application Submitted ✓',
    'Your application for "Junior PHP Developer" at TechNova BD has been submitted successfully.',
    '/applications',
    1,
    'application',
    0,
    NOW()
),
(
    2,
    'new_job',
    'New Job Match: Full-Stack Laravel Developer',
    'A new job matching your skills was posted by SoftByte Solutions. Apply before the deadline!',
    '/jobs/2',
    2,
    'job',
    0,
    NOW()
);

-- ── Re-enable safe updates ────────────────────────────────────────────────────
SET SQL_SAFE_UPDATES = 1;

