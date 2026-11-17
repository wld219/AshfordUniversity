# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: final-verify.spec.ts >> All Portal Pages Load >> all 13 student pages load
- Location: tests\final-verify.spec.ts:91:3

# Error details

```
Error: expect(page).toHaveURL(expected) failed

Expected: "http://localhost:8000/student/advisor_appointment.php"
Received: "http://localhost:8000/student/advisor.php"
Timeout:  5000ms

Call log:
  - Expect "toHaveURL" with timeout 5000ms
    13 × locator resolved to <html lang="en">…</html>
       - unexpected value "http://localhost:8000/student/advisor.php"

```

```yaml
- banner:
  - text: N Ashford University Student Portal
  - strong: David Joseph
  - text: davidjoseph@ashford.edu
  - link "Logout":
    - /url: /logout.php
- complementary:
  - button "Collapse navigation" [expanded]: ☰
  - link "Overview":
    - /url: dashboard.php
  - link "My Schedule":
    - /url: schedule.php
  - link "Degree Audit":
    - /url: degree-audit.php
  - link "Holds":
    - /url: holds.php
  - link "My Advisor":
    - /url: advisor.php
  - link "Majors & Minors":
    - /url: programs.php
  - link "Catalog":
    - /url: catalog.php
  - link "Buildings":
    - /url: buildings.php
  - link "My Information":
    - /url: information.php
- main:
  - heading "My Advisor" [level=1]
  - heading "Advisor Information" [level=2]
  - paragraph:
    - strong: "Name:"
    - text: Associate Professor Zoe Walker
  - paragraph:
    - strong: "Email:"
    - link "zoewalker@ashford.edu":
      - /url: mailto:zoewalker@ashford.edu
  - paragraph:
    - strong: "Specialty:"
    - text: Computer Networks
  - paragraph:
    - strong: "Department(s):"
    - text: Computer Science
  - paragraph:
    - strong: "Advisor Assignment Date:"
    - text: 2026-08-24
  - paragraph:
    - link "📧 Email Your Advisor":
      - /url: mailto:zoewalker@ashford.edu
  - heading "Request Advising Appointment" [level=2]
  - paragraph: ⚠ Your advisor has not set any availability windows yet.
  - paragraph: Please contact your advisor directly to schedule an appointment.
  - heading "Advising Appointments" [level=2]
  - paragraph: No appointments scheduled.
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | const CREDENTIALS = {
  4   |   Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  5   |   Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  6   |   Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  7   |   StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
  8   | };
  9   | 
  10  | const ROLE_HOMES: Record<string, string> = {
  11  |   Student: '/student/dashboard.php',
  12  |   Faculty: '/faculty/dashboard.php',
  13  |   Admin: '/admin/dashboard.php',
  14  |   StatStaff: '/statstaff/dashboard.php',
  15  | };
  16  | 
  17  | const loginAs = async (page: any, role: string) => {
  18  |   const c = CREDENTIALS[role as keyof typeof CREDENTIALS];
  19  |   await page.goto('/index.php');
  20  |   await page.fill('input[name="email"]', c.email);
  21  |   await page.fill('input[name="password"]', c.password);
  22  |   await page.selectOption('select[name="user_type"]', role);
  23  |   await page.click('button[type="submit"]');
  24  |   await expect(page).toHaveURL(ROLE_HOMES[role]);
  25  | };
  26  | 
  27  | // ==================== PUBLIC SITE ====================
  28  | test.describe('Public Site', () => {
  29  |   test('landing page loads', async ({ page }) => {
  30  |     await page.goto('/');
  31  |     await expect(page).toHaveTitle(/Ashford University/);
  32  |     await expect(page.locator('a[href="#overview"]')).toBeVisible();
  33  |     await expect(page.locator('a[href="#academics"]')).toBeVisible();
  34  |     await expect(page.locator('a[href="#admissions"]')).toBeVisible();
  35  |     await expect(page.locator('a[href="#schedule"]')).toBeVisible();
  36  |   });
  37  | 
  38  |   test('all public pages load', async ({ page }) => {
  39  |     for (const url of ['/public/departments.php', '/public/courses.php', '/public/programs.php', '/public/schedule.php']) {
  40  |       await page.goto(url);
  41  |       await expect(page.locator('body')).toBeVisible();
  42  |     }
  43  |   });
  44  | });
  45  | 
  46  | // ==================== AUTH ====================
  47  | test.describe('Authentication', () => {
  48  |   for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff']) {
  49  |     test(`${role} login works`, async ({ page }) => {
  50  |       await loginAs(page, role);
  51  |       await expect(page).toHaveURL(ROLE_HOMES[role]);
  52  |       await expect(page.locator('.topbar')).toBeVisible();
  53  |       await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
  54  |     });
  55  |   }
  56  | 
  57  |   test('logout works', async ({ page }) => {
  58  |     await loginAs(page, 'Student');
  59  |     await page.click('a[href*="logout.php"]');
  60  |     await expect(page).toHaveURL(/index\.php/);
  61  |   });
  62  | });
  63  | 
  64  | // ==================== ALL PAGES LOAD ====================
  65  | test.describe('All Portal Pages Load', () => {
  66  |   const studentPages = [
  67  |     '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
  68  |     '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
  69  |     '/student/holds.php', '/student/advisor.php', '/student/programs.php',
  70  |     '/student/catalog.php', '/student/buildings.php', '/student/information.php',
  71  |     '/student/advisor_appointment.php',
  72  |   ];
  73  | 
  74  |   const facultyPages = [
  75  |     '/faculty/dashboard.php', '/faculty/schedule.php', '/faculty/master-schedule.php',
  76  |     '/faculty/rosters.php', '/faculty/attendance-calendar.php', '/faculty/attendance-history.php',
  77  |     '/faculty/attendance-weekly.php', '/faculty/attendance-time-rules.php', '/faculty/attendance-window.php',
  78  |     '/faculty/advisees.php', '/faculty/catalog.php', '/faculty/profile.php',
  79  |   ];
  80  | 
  81  |   const adminPages = [
  82  |     '/admin/dashboard.php', '/admin/users.php', '/admin/academic.php', '/admin/sections.php',
  83  |     '/admin/semesters.php', '/admin/advisors.php', '/admin/holds.php', '/admin/exceptions.php',
  84  |     '/admin/degree-audit.php', '/admin/master-schedule.php', '/admin/audit.php',
  85  |   ];
  86  | 
  87  |   const statstaffPages = [
  88  |     '/statstaff/dashboard.php', '/statstaff/reports.php',
  89  |   ];
  90  | 
  91  |   test('all 13 student pages load', async ({ page }) => {
  92  |     await loginAs(page, 'Student');
  93  |     for (const url of studentPages) {
  94  |       await page.goto(url);
> 95  |       await expect(page).toHaveURL(url);
      |                          ^ Error: expect(page).toHaveURL(expected) failed
  96  |       await expect(page.locator('body')).toBeVisible();
  97  |     }
  98  |   });
  99  | 
  100 |   test('all 12 faculty pages load', async ({ page }) => {
  101 |     await loginAs(page, 'Faculty');
  102 |     for (const url of facultyPages) {
  103 |       await page.goto(url);
  104 |       await expect(page).toHaveURL(url);
  105 |       await expect(page.locator('body')).toBeVisible();
  106 |     }
  107 |   });
  108 | 
  109 |   test('all 11 admin pages load', async ({ page }) => {
  110 |     await loginAs(page, 'Admin');
  111 |     for (const url of adminPages) {
  112 |       await page.goto(url);
  113 |       await expect(page).toHaveURL(url);
  114 |       await expect(page.locator('body')).toBeVisible();
  115 |     }
  116 |   });
  117 | 
  118 |   test('all 2 statstaff pages load', async ({ page }) => {
  119 |     await loginAs(page, 'StatStaff');
  120 |     for (const url of statstaffPages) {
  121 |       await page.goto(url);
  122 |       await expect(page).toHaveURL(url);
  123 |       await expect(page.locator('body')).toBeVisible();
  124 |     }
  125 |   });
  126 | });
  127 | 
  128 | // ==================== KEY FUNCTIONALITY ====================
  129 | test.describe('Key Functionality', () => {
  130 |   test('registration page has filters', async ({ page }) => {
  131 |     await loginAs(page, 'Student');
  132 |     await page.goto('/student/registration.php');
  133 |     await expect(page.locator('select[name="department"]')).toBeVisible();
  134 |     await expect(page.locator('select[name="faculty"]')).toBeVisible();
  135 |     await expect(page.locator('select[name="seats"]')).toBeVisible();
  136 |   });
  137 | 
  138 |   test('schedule page has table', async ({ page }) => {
  139 |     await loginAs(page, 'Student');
  140 |     await page.goto('/student/schedule.php');
  141 |     await expect(page.locator('table')).toBeVisible();
  142 |   });
  143 | 
  144 |   test('master schedule has validation report', async ({ page }) => {
  145 |     await loginAs(page, 'Admin');
  146 |     await page.goto('/admin/master-schedule.php');
  147 |     await expect(page.locator('h1')).toContainText('Master Schedule');
  148 |   });
  149 | 
  150 |   test('statstaff read-only enforcement', async ({ page }) => {
  151 |     await loginAs(page, 'StatStaff');
  152 |     await page.goto('/admin/users.php');
  153 |     await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
  154 |   });
  155 | });
  156 | 
```