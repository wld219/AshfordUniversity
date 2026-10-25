# Ashford University Fullstack Information System

A full-stack university registration system for Ashford University, with
role-based portals for students, faculty, administrators, and Statistics
Department staff.

**Stack:** PHP 8 (procedural, PDO) · MySQL/MariaDB · HTML/CSS/vanilla
JavaScript · PHPMailer (bundled in `lib/PHPMailer`) · AWS-ready
deployment (see `docs/AWS_DEPLOYMENT.md`)

---

## Quick start (XAMPP)

### 1. Prerequisites

- PHP 8.0 or newer with `pdo_mysql`
- MySQL / MariaDB (XAMPP is fine)
- Apache serving the project folder (or `php -S`)

### 2. Install files

Copy the project into your web root, for example:

```
C:\xampp\htdocs\AshfordUni1-main\
```

### 3. Configure

Edit `.env` and adjust if needed (defaults work with a stock XAMPP
MySQL). To serve from a subfolder, set
`APP_BASE_PATH=/AshfordUni1-main`.

### 4. Create the database schema

```bash
mysql -u root -e "CREATE DATABASE ashford_university
	CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root ashford_university < database/schema.sql
```

The schema already contains every table, the `Admin` access-control table,
and the `v_section_seats` view. Existing databases created before the
Admin table was added should also run the files in
`database/migrations/` in order:

```bash
mysql -u root ashford_university < database/migrations/0005_admin.sql
```

The application ships with **no sample data**. All university content
(departments, courses, majors, semesters, users, rooms) is created through
the administrator portal or the installer, so the codebase contains only
the functional application.

### 5. Create the first administrator

Start Apache and MySQL, then open:

```
http://localhost/AshfordUni1-main/install.php
```

The installer creates one Full-Access administrator and then disables
itself (it only runs while the database has no users).

### 6. Use the portal

```
http://localhost/AshfordUni1-main/
```

Sign in with the administrator account, then build the catalog and create
accounts:

1. **Admin → Academic** — departments, courses, prerequisites, majors,
   minors, and their requirements.
2. **Admin → Users** — student, faculty, and statistics-staff accounts.
3. **Admin → Semesters** — semester start dates and registration windows.
4. **Admin → Sections** and **Admin → Master Schedule** — course
   sections, rooms, meeting times, and publishing.
5. Students may also self-register from the login page.

### Built-in PHP server (no Apache)

```bash
php -S localhost:8000 -t .
```

---

## Project structure

```
admin/       Administrator portal. Each page is an entry point that loads
             actions/ (POST handlers), *_data.php (queries), and
             partials/ (views)
student/     Student portal (same entry → actions → data → partials pattern)
faculty/     Faculty portal (same pattern for large pages)
statstaff/   Statistics Department portal: dashboard + read-only reports
public/      Unauthenticated site. index.php is the marketing landing page
              (its own vertical-rail layout in public.css); departments.php,
              courses.php, programs.php, and schedule.php share the top-bar
              layout in layout_public.php
includes/    auth.php (roles/sessions), functions.php (aggregator),
             functions/*.php (one module per domain rule), helpers.php
             (aggregator), helpers/*.php (input/validation/audit/CSRF/
             export), password_reset.php, mail.php (PHPMailer), env.php,
             session.php, layout.php
config/      database.php (PDO + .env), auth.php (auth settings)
database/    schema.sql, migrations/*.sql
lib/         PHPMailer (no Composer required)
docs/        MASTER_SCHEDULE.md, AWS_DEPLOYMENT.md
register_form.php, install_form.php, *_actions.php  page-level action and
                                              form modules for root pages
```

## Portals and roles

| Role                        | Ability                                                                                                                                       |
| --------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| Student                     | Register/drop courses, view schedules, grades, transcripts, degree audit, holds, advisor, majors/minors, update own contact details           |
| Faculty                     | View assigned sections and rosters, record attendance during meeting time, submit grades before the deadline, view advisees                   |
| Administrator               | Manage users, academics, sections, semesters, advisors, holds, grade exceptions; view audit log. `Read-Only` admins cannot change anything. |
| Statistics Department staff | Read-only anonymous reports (enrollment, grades, UG/grad totals, capacity utilization)                                                        |

Key rules enforced server-side: three failed logins lock the account;
prerequisites must be met with the required minimum grade; credit limits
(16 full-time / 8 part-time undergraduates); holds block registration;
seats are locked with `SELECT … FOR UPDATE`; schedule conflicts are
blocked; past/started semesters lock schedule edits; sections can only be
cancelled before the semester starts with fewer than 10 enrolled.

---

## Security notes

- Passwords are hashed with `password_hash()`; verified with
  `password_verify()`.
- Sessions regenerate on login; cookies are `HttpOnly` + `SameSite=Lax`
  (+`Secure` over HTTPS).
- All state-changing forms require a CSRF token.
- All SQL uses prepared statements; all output is escaped with `e()`.
- Password-reset tokens are random, hashed at rest, expiring, single-use,
  and resets never reveal whether an account exists.
- `.env` is git-ignored and is the only configuration file. It never
  leaves your machine.

## Configuration required by the university

- Semester dates and windows (`Admin → Semesters`); unset dates mean "no
  restriction" by design.
- Statistics reports use aggregate queries only, by design.
- See `docs/MASTER_SCHEDULE.md` for schedule semantics and
  `docs/AWS_DEPLOYMENT.md` for production deployment.
