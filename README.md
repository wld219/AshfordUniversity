# Ashford University Fullstack Information System

## Overview

Ashford University Fullstack Information System is a full stack university registration system built for Ashford University. It provides role-based portals for students, faculty, administrators, and Statistics Department staff. The system handles course registration, scheduling, grading, transcripts, degree audits, holds, advising, and administrative oversight.

## Stack

The application uses PHP 8 with procedural PDO for database access, MySQL or MariaDB as the database server, HTML and CSS for presentation, and vanilla JavaScript for progressive enhancement. PHPMailer is bundled in the lib directory and requires no Composer installation. The project is ready for AWS deployment as described in the AWS deployment document.

## Prerequisites

Before installing, ensure you have the following:

1. PHP 8.0 or newer with the pdo_mysql extension
2. MySQL or MariaDB server
3. Apache web server serving the project folder, or the built-in PHP server
4. A web browser

## Installation

Follow these steps to set up and run the project.

Step 1. Copy the project folder into your web root directory. For example, on XAMPP place it in C:\xampp\htdocs\AshfordUni1-main.

Step 2. Configure the application by editing the .env file. The default settings work with a stock XAMPP MySQL installation. If you serve the project from a subfolder, set APP_BASE_PATH to the subfolder path.

Step 3. Create the database. Run these commands against your MySQL server:

mysql -u root -e "CREATE DATABASE ashford_university CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root ashford_university < database/schema.sql

Step 4. Run the migration files in order:

mysql -u root ashford_university < database/migrations/0002_registration_windows.sql
mysql -u root ashford_university < database/migrations/0003_semester_start.sql
mysql -u root ashford_university < database/migrations/0004_master_schedule.sql
mysql -u root ashford_university < database/migrations/0005_admin.sql

Step 5. Start Apache and MySQL. Open the installer in your browser:

http://localhost/AshfordUni1-main/install.php

The installer creates the first administrator account with Full-Access privileges. It only runs while the database has no users, so it disables itself after the first account is created.

Step 6. Open the portal:

http://localhost/AshfordUni1-main/

Sign in with the administrator account you created during installation.

## Using the Built-in PHP Server

If you do not have Apache, use the built-in PHP server. Run this command from the project root:

php -S localhost:8000 -t . router.php

Then open http://localhost:8000 in your browser.

## Project Structure

The project is organized into the following directories and files:

admin: Administrator portal. Each page is an entry point that loads actions for POST handlers, data files for queries, and partials for views.

student: Student portal using the same entry to actions to data to partials pattern.

faculty: Faculty portal using the same pattern for large pages.

statstaff: Statistics Department portal with a dashboard and read-only reports.

public: Unauthenticated public site. The index.php file is the marketing landing page with a vertical rail layout in public.css. The departments.php, courses.php, programs.php, and schedule.php pages share the top-bar layout in layout_public.php.

includes: Core application files including auth.php for roles and sessions, env.php for environment loading, functions.php as an aggregator, functions subdirectory with one module per domain rule, helpers.php as an aggregator, helpers subdirectory with input validation audit CSRF and export modules, password_reset.php, mail.php for PHPMailer integration, session.php, and layout.php.

config: Configuration files including database.php for PDO and environment variables, and auth.php for authentication settings.

database: Database schema in schema.sql and migration files in the migrations subdirectory.

lib: PHPMailer library bundled with no Composer requirement.

docs: Documentation files including MASTER_SCHEDULE.md and AWS_DEPLOYMENT.md.

Root level PHP files handle page-level actions and forms including register_form.php, install_form.php, and files ending in _actions.php.

## Portals and Roles

The system supports four user roles, each with its own portal and capabilities.

Student: Register for and drop courses, view schedules, grades, transcripts, degree audits, holds, advisor information, majors and minors, and update personal contact details.

Faculty: View assigned sections and class rosters, record attendance during meeting times, submit grades before deadlines, view advisees, and send student notifications.

Administrator: Manage users, academics, sections, semesters, advisors, holds, and grade exceptions, and view the audit log. Read-Only administrators cannot change anything.

Statistics Department staff: View read-only anonymous reports covering enrollment, grades, undergraduate and graduate totals, and capacity utilization.

## Business Rules

The following rules are enforced on the server side:

Three failed login attempts lock the user account.
Prerequisites must be met with the required minimum grade before registration.
Credit limits are 16 credits for full-time undergraduates and 8 credits for part-time undergraduates.
Holds block course registration.
Seats are locked using SELECT FOR UPDATE to prevent double booking.
Schedule conflicts are blocked.
Past or started semesters lock schedule edits.
Sections can only be cancelled before the semester starts and only when fewer than 10 students are enrolled.

## Forbidden Constraints

The following actions are prohibited by the system:

Students cannot register for graduate courses if undergraduate, or undergraduate courses if graduate.
Students cannot be both graduate and undergraduate, or both full-time and part-time.
Students cannot add a course section with a hold, without met prerequisites, with a time conflict, more than once per semester, beyond credit limits, without available seats, outside the four-week window, or for another student.
Students cannot declare more than two majors and minors combined, more than two majors, more than one minor, a minor without a major, or less than one major.
Faculty cannot assign grades outside their own course sections or outside the grading period.
Faculty cannot record attendance outside the current day or view course section rosters outside the current semester.
Administrators cannot view semester schedules before Fall 2026 or after Spring 2027, add students with holds, exceeded credit limits, missing prerequisites, time conflicts, duplicate enrollments, or without available seats, add students outside the four-week window, add an advisor to a student who already has one, or assign an advisor from a different department.
Statistics staff cannot view personally identifying information of anyone else, view roster statistics outside Fall 2026 and Spring 2027, or change passwords outside their own account.

## Security

The application implements the following security measures:

Passwords are hashed with password_hash and verified with password_verify.
Sessions regenerate on login and cookies are HttpOnly and SameSite Lax, with Secure over HTTPS.
All state-changing forms require a CSRF token.
All SQL uses prepared statements and all output is escaped with the e function.
Password reset tokens are random, hashed at rest, expiring, and single-use. Reset flows never reveal whether an account exists.
The .env file is git-ignored and is the only configuration file. It never leaves your machine.

## Configuration Required by the University

Configure semester dates and registration windows through Admin Semesters. Unset dates mean no restriction by design. Statistics reports use aggregate queries only by design. See the MASTER_SCHEDULE document for schedule semantics and the AWS_DEPLOYMENT document for production deployment.

## Sign In Accounts

When using the full university dataset, one account exists per role with the password Password123:

Admin: arianaaugustin@ashford.edu
Student: liamwalker@ashford.edu
Faculty: kevinbaptiste200001@ashford.edu
StatStaff: ericjones400001@ashford.edu

Each account lands on its own portal automatically after sign in:

Admin goes to /admin/dashboard.php
Student goes to /student/dashboard.php
Faculty goes to /faculty/dashboard.php
StatStaff goes to /statstaff/dashboard.php

To sign out, click Logout in the top right of the header.

## Troubleshooting

Database connection failed: Confirm MySQL is running and .env has the correct DB_HOST, DB_PORT, DB_USER, and DB_PASS.
Raw PHP code displayed or blank page: You started the server with the system PHP. Rerun using the PHP installation that has the MySQL driver.
Root URL shows the sign-in page instead of the landing page: Include router.php at the end of the php -S command.
Port 8000 already in use: Pick a different port, for example php -S localhost:8080 -t . router.php.
Invalid login credentials: Use the administrator account created during installation.
Permission denied: You are signed in as the wrong role. Admin pages require an Admin account.

## Stopping the Site

Press Ctrl+C in the terminal or PowerShell window running the web server. Press Ctrl+C in the terminal or PowerShell window running MySQL if you started it manually. Close both windows to fully stop the application.
