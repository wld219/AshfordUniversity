# Ashford University Portal - Complete Setup Guide

This guide walks you through setting up the Ashford University Fullstack Information System from scratch, including PHP, MySQL, database import, and Playwright test automation.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Install PHP](#install-php)
3. [Install MySQL](#install-mysql)
4. [Configure the Database](#configure-the-database)
5. [Configure Environment](#configure-environment)
6. [Start the Server](#start-the-server)
7. [Install Playwright](#install-playwright)
8. [Run Playwright Tests](#run-playwright-tests)
9. [Test Credentials](#test-credentials)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

- macOS (this guide assumes macOS; adapt commands for Linux/Windows)
- Terminal access
- Administrator privileges for software installation

---

## 1. Install Homebrew

Homebrew is the package manager for macOS. Install it if you don't have it:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Add Homebrew to your PATH (follow the instructions printed after installation)
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
eval "$(/opt/homebrew/bin/brew shellenv)"
```

Verify:

```bash
brew --version
```

---

## 2. Install PHP

The project requires PHP 8.0+ with the `pdo_mysql` extension.

```bash
# Install PHP 8.4
brew install php@8.4

# Link PHP so `php` command works
brew link --overwrite php@8.4

# Verify installation
php --version

# Verify pdo_mysql extension is available
php -m | grep pdo_mysql
```

Expected output should show `pdo_mysql` in the list of modules.

---

## 3. Install MySQL

```bash
# Install MySQL
brew install mysql

# Start MySQL service
brew services start mysql

# Verify installation
mysql --version

# Secure the installation (recommended)
mysql_secure_installation
```

During `mysql_secure_installation`, you'll be prompted to:
- Set a root password (or leave blank for local development)
- Remove anonymous users
- Disable remote root login
- Remove the test database
- Reload privilege tables

---

## 4. Configure the Database

Create the database and import the schema:

```bash
# Log into MySQL as root
mysql -u root

# In the MySQL prompt:
CREATE DATABASE ashford_university CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Optional: create a dedicated application user
CREATE USER 'ashford_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ashford_university.* TO 'ashford_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

Now import the schema and all migrations **in order**:

```bash
# From the project root directory
mysql -u root ashford_university < database/schema.sql
mysql -u root ashford_university < database/migrations/0002_registration_windows.sql
mysql -u root ashford_university < database/migrations/0003_semester_start.sql
mysql -u root ashford_university < database/migrations/0004_master_schedule.sql
mysql -u root ashford_university < database/migrations/0005_admin.sql
mysql -u root ashford_university < database/migrations/0006_appointments.sql
mysql -u root ashford_university < database/migrations/0007_advising_workflow.sql
```

Verify tables were created:

```bash
mysql -u root ashford_university -e "SHOW TABLES;"
```

You should see ~47 tables including `User`, `Login`, `Course`, `Course_Section`, `Enrollment`, `Semester`, `Master_Schedule`, etc.

---

## 5. Configure Environment

The `.env` file already exists in the project root. Review and update it:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ashford_university
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8000
SESSION_NAME=ashford_university_session
APP_BASE_PATH=
```

**Important:** The `.env` file is git-ignored and should never be committed to version control.

If you set a MySQL root password during `mysql_secure_installation`, update `DB_PASS` accordingly.

---

## 6. Start the PHP Server

The project uses PHP's built-in web server with a custom router:

```bash
# From the project root
php -S localhost:8000 -t . router.php
```

The `router.php` file ensures that:
- `http://localhost:8000/` → opens the public landing page (`public/index.php`)
- `http://localhost:8000/index.php` → opens the sign-in page
- All other paths → served normally from the document root

Open your browser to: **http://localhost:8000**

To stop the server, press `Ctrl+C` in the terminal.

---

## 7. Install Playwright

Playwright is already installed in this project (via `npm install -D playwright`). If you need to reinstall:

```bash
# Initialize npm (if not already done)
npm init -y

# Install Playwright
npm install -D playwright

# Install browser binaries
npx playwright install
```

This installs Chromium, Firefox, and WebKit browsers for testing.

---

## 8. Run Playwright Tests

### Verify Environment First

```bash
node verify-setup.js
```

This checks that PHP, MySQL, Node.js, Playwright, and all project files are present.

### Run All Tests

```bash
npm test
```

### Run Specific Test Suites

| Command | What it tests |
|---------|--------------|
| `npm run test:auth` | Authentication flow (login form, role selector, error handling) |
| `npm run test:student` | Student portal pages (dashboard, registration, schedule, etc.) |
| `npm run test:faculty` | Faculty portal pages (dashboard, rosters, attendance, etc.) |
| `npm run test:admin` | Admin portal pages (users, sections, master schedule, etc.) |
| `npm run test:statstaff` | StatStaff portal pages (dashboard, reports, read-only) |
| `npm run test:registration` | Registration and master schedule functionality |
| `npm run test:all-portals` | All four role-based portals |
| `npm run test:screenshots` | Capture screenshots of all public pages |
| `npm run test:explore` | Navigation exploration tests |
| `npm run test:headed` | Run tests with visible browser window |
| `npm run test:ui` | Run tests with Playwright UI mode |

### View Test Report

```bash
npm run test:report
```

This opens the HTML test report in your browser.

---

## 9. Test Credentials

When using the full university dataset, one account exists per role with password `Password123`:

| Role | Email | Portal URL |
|------|-------|------------|
| Admin | arianaaugustin@ashford.edu | /admin/dashboard.php |
| Student | liamwalker@ashford.edu | /student/dashboard.php |
| Faculty | kevinbaptiste200001@ashford.edu | /faculty/dashboard.php |
| StatStaff | ericjones400001@ashford.edu | /statstaff/dashboard.php |

Each account lands on its own portal automatically after sign-in.

---

## 10. Troubleshooting

### Database Connection Failed
- Confirm MySQL is running: `brew services list | grep mysql`
- Confirm `.env` has correct `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`
- Try connecting manually: `mysql -u root -p ashford_university`

### Raw PHP Code Displayed
- You're using the system PHP without the MySQL driver
- Use Homebrew PHP: `brew install php@8.4`
- Verify: `php -m | grep pdo_mysql`

### Port 8000 Already in Use
- Use a different port: `php -S localhost:8080 -t . router.php`

### "/" Shows Sign-In Page Instead of Landing Page
- You forgot `router.php`: `php -S localhost:8000 -t . router.php`

### Playwright Tests Fail
- Ensure the PHP server is running on `localhost:8000`
- Run `npx playwright install` to ensure browsers are installed
- Check the HTML report: `npx playwright show-report`

### Permission Denied on Admin Pages
- You're signed in as the wrong role
- Admin pages require an Admin account with Full-Access security level

---

## Project Structure Reference

```
/AshfordUniversity/
├── index.php              # Sign-in page
├── router.php             # PHP built-in server router
├── login.php              # Login handler
├── logout.php             # Logout handler
├── register.php           # Student registration
├── install.php            # One-time installer
├── .env                   # Environment configuration
├── package.json           # npm project with Playwright
├── playwright.config.js   # Playwright configuration
├── verify-setup.js        # Environment verification script
├── config/
│   ├── database.php       # PDO connection
│   └── auth.php           # Auth configuration
├── includes/              # Core application logic
│   ├── auth.php           # Role helpers, CSRF, e() escape
│   ├── session.php        # Hardened session start
│   ├── layout.php         # Shared portal layout
│   ├── env.php            # .env reader
│   ├── functions.php      # Domain logic aggregator
│   ├── helpers.php        # Helper aggregator
│   ├── functions/         # 23 domain rule modules
│   └── helpers/           # 6 shared helpers
├── admin/                 # Administrator portal
│   ├── actions/           # 20 POST action handlers
│   └── partials/          # 46 view partials
├── student/               # Student portal
│   └── partials/          # 14 view partials
├── faculty/               # Faculty portal
│   └── partials/          # 2 view partials
├── statstaff/             # Statistics staff portal
│   └── partials/          # 1 view partial
├── public/                # Unauthenticated public site
│   └── layout_public.php  # Public layout
├── database/              # Schema and migrations
│   ├── schema.sql
│   └── migrations/
├── lib/PHPMailer/         # Bundled PHPMailer
├── assets/                # CSS, JS, images
│   ├── css/
│   ├── js/
│   ├── ashford-universal-ui/
│   ├── ashford-enhancements/
│   └── img/
├── tests/                 # Playwright test suite
│   ├── auth.spec.ts
│   ├── registration.spec.ts
│   ├── student-portal.spec.ts
│   ├── faculty-portal.spec.ts
│   ├── admin-portal.spec.ts
│   ├── statstaff-portal.spec.ts
│   ├── registration-schedule.spec.ts
│   ├── screenshots.spec.ts
│   ├── portal.explore.spec.ts
│   └── auth-helper.ts
├── docs/                  # Documentation
└── skills/                # Kilo agent skills
```

---

## Quick Start Summary

```bash
# 1. Install dependencies (one-time)
brew install php@8.4 mysql
brew services start mysql

# 2. Create database
mysql -u root -e "CREATE DATABASE ashford_university CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Import schema and migrations
mysql -u root ashford_university < database/schema.sql
mysql -u root ashford_university < database/migrations/0002_registration_windows.sql
mysql -u root ashford_university < database/migrations/0003_semester_start.sql
mysql -u root ashford_university < database/migrations/0004_master_schedule.sql
mysql -u root ashford_university < database/migrations/0005_admin.sql
mysql -u root ashford_university < database/migrations/0006_appointments.sql
mysql -u root ashford_university < database/migrations/0007_advising_workflow.sql

# 4. Configure .env (review existing file)
# 5. Start server
php -S localhost:8000 -t . router.php

# 6. Open http://localhost:8000 in browser

# 7. Run tests (in another terminal)
npm test
```
