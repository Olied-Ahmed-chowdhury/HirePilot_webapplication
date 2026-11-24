# 🚀 HirePilot AI — AI-Powered Job Search & Application Management System

> **Final Project Submission**  
> **Student Name:** Olied Ahmed Chowdhury  
> **Student ID:** 242-134-017  
> **Batch:** 7th Batch  
> **Repository:** [HirePilot_webapplication](https://github.com/Olied-Ahmed-chowdhury/HirePilot_webapplication)

---

## 📖 Project Overview
**HirePilot AI** is a modern, modular SaaS web application built with **PHP 8 (OOP)**, **MySQL**, **Bootstrap 5**, and **Vanilla JavaScript Fetch (AJAX)**. It bridges the gap between job discovery and application submission by combining:
1. **Intelligent Job Matching:** Relevance scoring (0–100%) computed dynamically from user skills, experience, and remote preferences.
2. **AI Writing Assistant (Grok AI / X.AI):** Automatic generation of tailored cover letters and direct outreach emails personalized to the job description and candidate resume. (Includes a deterministic local mock engine if an API key is not configured).
3. **Application Lifecycle Tracker:** Real-time pipeline management (`saved`, `applied`, `under_review`, `interview_scheduled`, `offer_received`, `rejected`, `withdrawn`) with instant AJAX status transitions and in-app notifications.
4. **Google Authentication & Native Auth:** Secure sign-in via Google OAuth 2.0 or Bcrypt-hashed email/password credentials with CSRF protection and session hardening.
5. **Resume Management:** PDF/DOCX file validation, secure storage, and text extraction for AI context.
6. **Role-Based Admin Console:** Full Job CMS, candidate monitoring, and system metrics.

---

## 🛠️ Technology Stack & Architecture

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.x (Strict Types, PSR-4 Autoloading, Singleton PDO, OOP Controllers & Models) |
| **Database** | MySQL 8.x with InnoDB, Foreign Keys, Indexes, JSON columns, and Fulltext Search |
| **Frontend** | HTML5, Modern Custom CSS3 Design System, Bootstrap 5.3, Bootstrap Icons |
| **AJAX / Dynamic** | Vanilla JS `fetch()` API with CSRF headers, loading states, toasts, and debouncing |
| **AI Integration** | Grok AI (`https://api.x.ai/v1/chat/completions`) + Local deterministic fallback engine |
| **OAuth & Mail** | Google OAuth 2.0, SMTP / Safe Simulated Mail Logger (`storage/logs/mail.log`) |

---

## 🗄️ Database Architecture (`database/schema.sql`)

The database consists of 7 normalized tables:
- **`users`**: User identity, roles (`user`, `admin`), profile bio, target role, skills (JSON), remote preference, Google OAuth IDs.
- **`resumes`**: Uploaded resume files, MIME types, file sizes, primary flags, and parsed text.
- **`jobs`**: Admin-managed job listings, skills required (JSON), experience level, compensation, application deadlines, view and apply counters.
- **`applications`**: Candidate application records, 7-stage status tracking, interview dates, offers, custom notes, and company response log.
- **`email_drafts`**: AI-generated cover letters & outreach emails, status workflow (`draft` ➔ `approved` ➔ `sent`), recipient addresses, transmission logs.
- **`notifications`**: User notifications with read/unread tracking and direct entity deep-links.
- **`csrf_tokens`**: Token store for session security.

---

## 🔑 Demo Credentials (`database/seed.sql`)

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@hirepilot.ai` | `Admin@1234` |
| **Candidate (Demo)** | `demo@hirepilot.ai` | `Demo@1234` |

*(Quick autofill buttons are built directly into the login screen for instant testing).*

---

## ⚙️ Installation & Setup Guide

### 1. Requirements
- **Web Server:** Apache (with `mod_rewrite` enabled) via XAMPP, Laragon, or standalone.
- **PHP:** Version 8.1 or higher (with `pdo_mysql`, `curl`, `fileinfo`, `zip` extensions enabled).
- **Database:** MySQL 8.0+ or MariaDB 10.4+.

### 2. Database Import
Open phpMyAdmin or MySQL CLI and run:
```bash
# 1. Create database and tables
mysql -u root -p < database/schema.sql

# 2. Seed initial jobs and demo accounts
mysql -u root -p hirepilot_db < database/seed.sql
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Update your credentials in `.env`:
```ini
APP_URL=http://localhost/HirePilot_webapplication/public

DB_HOST=localhost
DB_NAME=hirepilot_db
DB_USER=root
DB_PASS=

# Grok AI Configuration
AI_PROVIDER=grok
AI_API_KEY=your_grok_api_key_here
AI_MODEL=grok-beta

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/HirePilot_webapplication/public/auth/google/callback
```

### 4. Run Application
Place this project inside your web server directory (e.g. `C:\xampp\htdocs\HirePilot_webapplication` or virtual host).  
Navigate to:
```
http://localhost/HirePilot_webapplication/public
```

---

## 📋 Completed Milestones & Git History

- [x] **Milestone 1:** Project skeleton, PSR-4 autoloader, PDO singleton (`src/Core/Database.php`), CSRF guard, Flash messaging, normalized DB schema & seed data.
- [x] **Milestone 2:** Modern UI layout shell (`app.css`, `app.js`, responsive sidebar/navbar), auth controllers, profile editing, skills tag system, Google OAuth integration.
- [x] **Milestone 3:** Resume upload engine, strict MIME/size validation, DOCX/PDF text parsing for AI context, primary resume management.
- [x] **Milestone 4:** Job discovery, filter pills, search debouncing, server-side pagination, and AI skill relevance scoring (`JobMatchingService`).
- [x] **Milestone 5:** AI drafting layer (`AiDraftingService`) connected with Grok API + deterministic fallback, email approval workflow, preview/safe transmission service (`EmailService`).
- [x] **Milestone 6:** Application pipeline CRUD, real-time AJAX status updates, custom notes, company response tracker, and notification bell polling.
- [x] **Milestone 7:** Admin console, platform statistics (users, jobs, apps, AI drafts), and complete Job CMS.
- [x] **Milestone 8:** Comprehensive documentation, zero PHP syntax errors across all 54 files, verified security practices.

---

**Developed by Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)**
