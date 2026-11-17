# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: interconnect.spec.ts >> Public Site — All Pages & Navigation >> landing page loads with all sections
- Location: tests\interconnect.spec.ts:32:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('a[href="index.php"]')
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" locator('a[href="index.php"]') with timeout 5000ms
  - waiting for locator('a[href="index.php"]')

```

```yaml
- link "Skip to content":
  - /url: "#main-content"
- complementary:
  - img "Ashford University"
  - navigation "Page sections":
    - link "Overview":
      - /url: "#overview"
    - link "Academics":
      - /url: "#academics"
    - link "Admissions":
      - /url: "#admissions"
    - link "Schedule":
      - /url: "#schedule"
  - paragraph: Students, faculty, administrators, and statistics staff each have their own portal.
  - link "Open your portal":
    - /url: /index.php
- main:
  - paragraph: Ashford University
  - heading "Plan the whole term before registration opens." [level=1]
  - paragraph: The catalog, prerequisites, program requirements, and master schedule are published in the open. Meeting times, rooms, instructors, and open seats are visible before you sign in, so you can build a schedule that holds together.
  - link "Explore degree programs":
    - /url: /public/programs.php
  - link "See the published schedule":
    - /url: /public/schedule.php
  - img "Ashford University"
  - list:
    - listitem: Student portal Enroll
    - listitem: Faculty portal Teach
    - listitem: Administrator portal Manage
    - listitem: Statistics portal Report
  - paragraph: Academics
  - heading "Departments, prerequisites, and requirements in one place." [level=2]
  - paragraph: Before you commit to a term, you can see what each department teaches, what every course requires, and what a major or minor actually asks of you.
  - article:
    - paragraph: Departments
    - heading "Know who teaches what" [level=3]
    - paragraph: Every academic unit with its courses and contact information, published for prospective and current students alike.
    - link "Browse departments":
      - /url: /public/departments.php
  - article:
    - paragraph: Programs
    - heading "Map credits to a degree" [level=3]
    - paragraph: Majors and minors list the courses they require, so you can plan four years instead of guessing one term at a time.
    - link "Compare programs":
      - /url: /public/programs.php
  - article:
    - paragraph: Prerequisites
    - heading "See the gate before you reach it" [level=3]
    - paragraph: Each prerequisite carries its minimum grade. You know what you must earn before a course unlocks.
    - link "Check prerequisites":
      - /url: /public/courses.php
  - paragraph: Admissions
  - heading "Four steps from interest to enrolled." [level=2]
  - paragraph: The same tools you use to plan are the ones you use to register. Nothing is hidden behind a login until it concerns your own record.
  - list:
    - listitem:
      - text: "01"
      - heading "Create your account" [level=3]
      - paragraph: Register as a student from the public site.
    - listitem:
      - text: "02"
      - heading "Read the catalog" [level=3]
      - paragraph: Courses, credits, levels, and prerequisites.
    - listitem:
      - text: "03"
      - heading "Build the schedule" [level=3]
      - paragraph: Meeting times, rooms, and open seats, checked for conflicts.
    - listitem:
      - text: "04"
      - heading "Register in your window" [level=3]
      - paragraph: Enrollment opens and closes on published dates.
  - paragraph:
    - link "Create your student account":
      - /url: /register.php
  - paragraph: Schedule
  - heading "The master schedule is the single source of truth." [level=2]
  - paragraph: Every published section carries its meeting time, room, instructor, and seat count. A schedule is only published once every section has a room, an authorized instructor, a valid meeting time, and no room or teaching conflicts.
  - region "Ashford University at a glance":
    - article:
      - text: "10"
      - paragraph: Academic departments
    - article:
      - text: "240"
      - paragraph: Courses in the catalog
    - article:
      - text: 2,533
      - paragraph: Sections published this term
    - article:
      - text: "4"
      - paragraph: Role portals
- contentinfo:
  - paragraph:
    - text: © 2026 Ashford University ·
    - link "Departments":
      - /url: /public/departments.php
    - text: ·
    - link "Courses":
      - /url: /public/courses.php
    - text: ·
    - link "Programs":
      - /url: /public/programs.php
    - text: ·
    - link "Master schedule":
      - /url: /public/schedule.php
  - paragraph:
    - link "Sign in to your portal":
      - /url: /index.php
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | // Test credentials — using accounts that do NOT trigger 2FA
  4   | const CREDENTIALS = {
  5   |   Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  6   |   Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  7   |   Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  8   |   StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
  9   | };
  10  | 
  11  | const ROLE_HOMES = {
  12  |   Student: '/student/dashboard.php',
  13  |   Faculty: '/faculty/dashboard.php',
  14  |   Admin: '/admin/dashboard.php',
  15  |   StatStaff: '/statstaff/dashboard.php',
  16  | };
  17  | 
  18  | type Role = keyof typeof CREDENTIALS;
  19  | 
  20  | const loginAs = async (page: any, role: Role) => {
  21  |   const creds = CREDENTIALS[role];
  22  |   await page.goto('/index.php');
  23  |   await page.fill('input[name="email"]', creds.email);
  24  |   await page.fill('input[name="password"]', creds.password);
  25  |   await page.selectOption('select[name="user_type"]', role);
  26  |   await page.click('button[type="submit"]');
  27  |   await expect(page).toHaveURL(ROLE_HOMES[role]);
  28  | };
  29  | 
  30  | // ==================== PUBLIC SITE ====================
  31  | test.describe('Public Site — All Pages & Navigation', () => {
  32  |   test('landing page loads with all sections', async ({ page }) => {
  33  |     await page.goto('/');
  34  |     await expect(page).toHaveTitle(/Ashford University/);
  35  | 
  36  |     // Check all nav section anchors exist (same-page navigation)
  37  |     await expect(page.locator('a[href="#overview"]')).toBeVisible();
  38  |     await expect(page.locator('a[href="#academics"]')).toBeVisible();
  39  |     await expect(page.locator('a[href="#admissions"]')).toBeVisible();
  40  |     await expect(page.locator('a[href="#schedule"]')).toBeVisible();
  41  | 
  42  |     // Check portal cards
  43  |     await expect(page.locator('.landing-portals li')).toHaveCount(4);
  44  | 
  45  |     // Check "Open your portal" button
> 46  |     await expect(page.locator('a[href="index.php"]')).toBeVisible();
      |                                                       ^ Error: expect(locator).toBeVisible() failed
  47  | 
  48  |     // Footer links
  49  |     await expect(page.locator('a[href*="public/departments.php"]')).toBeVisible();
  50  |     await expect(page.locator('a[href*="public/courses.php"]')).toBeVisible();
  51  |     await expect(page.locator('a[href*="public/programs.php"]')).toBeVisible();
  52  |     await expect(page.locator('a[href*="public/schedule.php"]')).toBeVisible();
  53  |   });
  54  | 
  55  |   test('all public pages load', async ({ page }) => {
  56  |     const publicPages = [
  57  |       '/public/departments.php',
  58  |       '/public/courses.php',
  59  |       '/public/programs.php',
  60  |       '/public/schedule.php',
  61  |     ];
  62  | 
  63  |     for (const url of publicPages) {
  64  |       await page.goto(url);
  65  |       await expect(page.locator('body')).toBeVisible();
  66  |       await expect(page.locator('nav[aria-label="Public navigation"]')).toBeVisible();
  67  |     }
  68  |   });
  69  | });
  70  | 
  71  | // ==================== AUTHENTICATION ====================
  72  | test.describe('Authentication — Login & Role-Based Redirect', () => {
  73  |   for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
  74  |     test(`${role} login redirects to correct portal`, async ({ page }) => {
  75  |       await loginAs(page, role);
  76  |       await expect(page).toHaveURL(ROLE_HOMES[role]);
  77  |     });
  78  |   }
  79  | 
  80  |   test('invalid credentials show error', async ({ page }) => {
  81  |     await page.goto('/index.php');
  82  |     await page.fill('input[name="email"]', 'wrong@email.com');
  83  |     await page.fill('input[name="password"]', 'wrongpassword');
  84  |     await page.selectOption('select[name="user_type"]', 'Student');
  85  |     await page.click('button[type="submit"]');
  86  |     await expect(page).toHaveURL(/index\.php/);
  87  |   });
  88  | 
  89  |   test('logout returns to sign-in', async ({ page }) => {
  90  |     const { loginAs } = await import('./auth-helper');
  91  |     await loginAs(page, 'Student');
  92  |     await page.click('a[href*="logout.php"]');
  93  |     await expect(page).toHaveURL(/index\.php/);
  94  |   });
  95  | });
  96  | 
  97  | // ==================== STUDENT PORTAL ====================
  98  | test.describe('Student Portal — All Pages Load', () => {
  99  |   test.use({ storageState: { cookies: [], origins: [] } });
  100 | 
  101 |   const studentPages = [
  102 |     { url: '/student/dashboard.php', titleContains: '' },
  103 |     { url: '/student/master-schedule.php', titleContains: '' },
  104 |     { url: '/student/registration.php', titleContains: 'Course Registration' },
  105 |     { url: '/student/schedule.php', titleContains: 'My Schedule' },
  106 |     { url: '/student/degree-audit.php', titleContains: 'Degree Audit' },
  107 |     { url: '/student/transcript.php', titleContains: '' },
  108 |     { url: '/student/holds.php', titleContains: '' },
  109 |     { url: '/student/advisor.php', titleContains: '' },
  110 |     { url: '/student/programs.php', titleContains: '' },
  111 |     { url: '/student/catalog.php', titleContains: '' },
  112 |     { url: '/student/buildings.php', titleContains: '' },
  113 |     { url: '/student/information.php', titleContains: '' },
  114 |     { url: '/student/advisor_appointment.php', titleContains: '' },
  115 |   ];
  116 | 
  117 |   test('all student pages load via direct URL', async ({ page }) => {
  118 |     const { loginAs } = await import('./auth-helper');
  119 |     await loginAs(page, 'Student');
  120 | 
  121 |     for (const p of studentPages) {
  122 |       await page.goto(p.url);
  123 |       await expect(page).toHaveURL(p.url);
  124 |       await expect(page.locator('body')).toBeVisible();
  125 |       await expect(page.locator('.topbar')).toBeVisible();
  126 |       await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
  127 |     }
  128 |   });
  129 | 
  130 |   test('registration page has filters and add/drop forms', async ({ page }) => {
  131 |     const { loginAs } = await import('./auth-helper');
  132 |     await loginAs(page, 'Student');
  133 |     await page.goto('/student/registration.php');
  134 | 
  135 |     await expect(page.locator('select[name="department"]')).toBeVisible();
  136 |     await expect(page.locator('select[name="faculty"]')).toBeVisible();
  137 |     await expect(page.locator('select[name="seats"]')).toBeVisible();
  138 |   });
  139 | 
  140 |   test('schedule page shows enrolled courses table', async ({ page }) => {
  141 |     const { loginAs } = await import('./auth-helper');
  142 |     await loginAs(page, 'Student');
  143 |     await page.goto('/student/schedule.php');
  144 |     await expect(page.locator('table')).toBeVisible();
  145 |   });
  146 | });
```