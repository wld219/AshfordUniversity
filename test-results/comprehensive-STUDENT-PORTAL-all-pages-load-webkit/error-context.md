# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> STUDENT PORTAL >> all pages load
- Location: tests/comprehensive.spec.ts:96:3

# Error details

```
Error: expect(page).toHaveURL(expected) failed

Expected: "http://localhost:8001/student/advisor_appointment.php"
Received: "http://localhost:8001/student/advisor.php"
Timeout:  5000ms

Call log:
  - Expect "toHaveURL" with timeout 5000ms
    14 × locator resolved to <html lang="en">…</html>
       - unexpected value "http://localhost:8001/student/advisor.php"

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
  - link "Master Schedule":
    - /url: master-schedule.php
  - link "Course Registration":
    - /url: registration.php
  - link "My Schedule":
    - /url: schedule.php
  - link "Degree Audit":
    - /url: degree-audit.php
  - link "Unofficial Transcript":
    - /url: transcript.php
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
    - text: Adjunct Professor Zara Waleed
  - paragraph:
    - strong: "Email:"
    - link "zara.waleed@ashford.edu":
      - /url: mailto:zara.waleed@ashford.edu
  - paragraph:
    - strong: "Specialty:"
    - text: Visual Arts
  - paragraph:
    - strong: "Department(s):"
    - text: Mathematics, Mathematics, Computer & Information Science
  - paragraph:
    - strong: "Advisor Assignment Date:"
    - text: 2026-11-02
  - paragraph:
    - link "📧 Email Your Advisor":
      - /url: mailto:zara.waleed@ashford.edu
  - heading "Request Advising Appointment" [level=2]
  - text: Select Date
  - textbox "Select Date"
  - text: Available Time Slot
  - combobox "Available Time Slot":
    - option "Select a time" [selected]
  - text: Reason for Appointment
  - textbox "Reason for Appointment":
    - /placeholder: "Example: Course registration or degree planning"
  - button "Request Appointment"
  - heading "Advisor Availability" [level=3]
  - searchbox "Search displayed table rows"
  - button "Reset"
  - button "Export CSV"
  - text: 1 / 1 displayed-page rows
  - table:
    - rowgroup:
      - row "Day ↕ Available Time ↕":
        - button "Day ↕"
        - button "Available Time ↕"
    - rowgroup:
      - row "Tuesday 9:00 AM – 11:00 AM":
        - cell "Tuesday":
          - strong: Tuesday
        - cell "9:00 AM – 11:00 AM"
  - heading "Advising Appointments" [level=2]
  - paragraph: No appointments scheduled.
```

# Test source

```ts
  7   |   StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
  8   | };
  9   | 
  10  | const ROLE_HOMES = {
  11  |   Student: '/student/dashboard.php',
  12  |   Faculty: '/faculty/dashboard.php',
  13  |   Admin: '/admin/dashboard.php',
  14  |   StatStaff: '/statstaff/dashboard.php',
  15  | };
  16  | 
  17  | type Role = keyof typeof CREDENTIALS;
  18  | 
  19  | async function loginFresh(context: any, role: Role) {
  20  |   const c = CREDENTIALS[role];
  21  |   const page = await context.newPage();
  22  |   await page.goto('/index.php');
  23  |   await page.fill('input[name="email"]', c.email);
  24  |   await page.fill('input[name="password"]', c.password);
  25  |   await page.selectOption('select[name="user_type"]', role);
  26  |   await page.click('button[type="submit"]');
  27  |   await expect(page).toHaveURL(ROLE_HOMES[role]);
  28  |   return page;
  29  | }
  30  | 
  31  | // ==================== PUBLIC SITE ====================
  32  | test.describe('PUBLIC SITE', () => {
  33  |   test('landing page loads with all sections', async ({ page }) => {
  34  |     await page.goto('/');
  35  |     await expect(page).toHaveTitle(/Ashford University/);
  36  |     await expect(page.locator('a[href="#overview"]')).toBeVisible();
  37  |     await expect(page.locator('a[href="#academics"]')).toBeVisible();
  38  |     await expect(page.locator('a[href="#admissions"]')).toBeVisible();
  39  |     await expect(page.locator('a[href="#schedule"]')).toBeVisible();
  40  |     await expect(page.locator('.landing-portals li')).toHaveCount(4);
  41  |     await expect(page.locator('.skip-link')).toBeVisible();
  42  |   });
  43  | 
  44  |   test('all public pages load', async ({ page }) => {
  45  |     for (const url of ['/public/departments.php', '/public/courses.php', '/public/programs.php', '/public/schedule.php']) {
  46  |       await page.goto(url);
  47  |       await expect(page.locator('body')).toBeVisible();
  48  |     }
  49  |   });
  50  | 
  51  |   test('public pages have no JS errors', async ({ page }) => {
  52  |     const errors: string[] = [];
  53  |     page.on('pageerror', (err) => errors.push(err.message));
  54  |     await page.goto('/');
  55  |     await page.goto('/public/courses.php');
  56  |     expect(errors).toHaveLength(0);
  57  |   });
  58  | });
  59  | 
  60  | // ==================== AUTHENTICATION ====================
  61  | test.describe('AUTHENTICATION', () => {
  62  |   for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
  63  |     test(`${role} login works`, async ({ context }) => {
  64  |       const page = await loginFresh(context, role);
  65  |       await expect(page.locator('.topbar')).toBeVisible();
  66  |       await expect(page.locator('.brand b')).toContainText('Ashford University');
  67  |       await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
  68  |     });
  69  |   }
  70  | 
  71  |   test('logout works', async ({ context }) => {
  72  |     const page = await loginFresh(context, 'Student');
  73  |     await page.click('a[href*="logout.php"]');
  74  |     await expect(page).toHaveURL(/index\.php/);
  75  |   });
  76  | 
  77  |   test('invalid credentials rejected', async ({ page }) => {
  78  |     await page.goto('/index.php');
  79  |     await page.fill('input[name="email"]', 'wrong@email.com');
  80  |     await page.fill('input[name="password"]', 'wrongpassword');
  81  |     await page.selectOption('select[name="user_type"]', 'Student');
  82  |     await page.click('button[type="submit"]');
  83  |     await expect(page).toHaveURL(/index\.php/);
  84  |   });
  85  | 
  86  | test('register page loads', async ({ page }) => {
  87  |     await page.goto('/register.php');
  88  |     await expect(page.locator('form')).toBeVisible();
  89  |     await expect(page.locator('input[name="csrf_token"]')).toHaveAttribute('type', 'hidden');
  90  |     await expect(page.locator('a[href*="index.php"]')).toBeVisible();
  91  |   });
  92  | });
  93  | 
  94  | // ==================== STUDENT PORTAL ====================
  95  | test.describe('STUDENT PORTAL', () => {
  96  |   test('all pages load', async ({ context }) => {
  97  |     const page = await loginFresh(context, 'Student');
  98  |     const pages = [
  99  |       '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
  100 |       '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
  101 |       '/student/holds.php', '/student/advisor.php', '/student/programs.php',
  102 |       '/student/catalog.php', '/student/buildings.php', '/student/information.php',
  103 |       '/student/advisor_appointment.php',
  104 |     ];
  105 |     for (const url of pages) {
  106 |       await page.goto(url);
> 107 |       await expect(page).toHaveURL(url);
      |                          ^ Error: expect(page).toHaveURL(expected) failed
  108 |       await expect(page.locator('body')).toBeVisible();
  109 |       await expect(page.locator('.topbar')).toBeVisible();
  110 |     }
  111 |   });
  112 | 
  113 |   test('student dashboard has progress and quick actions', async ({ context }) => {
  114 |     const page = await loginFresh(context, 'Student');
  115 |     await page.goto('/student/dashboard.php');
  116 |     await expect(page.locator('h3').filter({ hasText: 'Degree Progress' })).toBeVisible();
  117 |     await expect(page.locator('h3').filter({ hasText: 'Quick Actions' })).toBeVisible();
  118 |   });
  119 | 
  120 |   test('student registration has filters and waitlist', async ({ context }) => {
  121 |     const page = await loginFresh(context, 'Student');
  122 |     await page.goto('/student/registration.php');
  123 |     await expect(page.locator('h1')).toContainText('Course Registration');
  124 |     await expect(page.locator('select[name="department"]')).toBeVisible();
  125 |     await expect(page.locator('select[name="faculty"]')).toBeVisible();
  126 |   });
  127 | 
  128 |   test('student schedule has grades table', async ({ context }) => {
  129 |     const page = await loginFresh(context, 'Student');
  130 |     await page.goto('/student/schedule.php');
  131 |     await expect(page.locator('h1')).toContainText('My Schedule');
  132 |     await expect(page.locator('table')).toBeVisible();
  133 |   });
  134 | 
  135 |   test('student degree audit has progress bars', async ({ context }) => {
  136 |     const page = await loginFresh(context, 'Student');
  137 |     await page.goto('/student/degree-audit.php');
  138 |     await expect(page.locator('.progress-bar, .progress-fill')).toBeVisible({ timeout: 5000 });
  139 |   });
  140 | 
  141 |   test('student transcript has print button', async ({ context }) => {
  142 |     const page = await loginFresh(context, 'Student');
  143 |     await page.goto('/student/transcript.php');
  144 |     await expect(page.locator('button[onclick="window.print()"]')).toBeVisible();
  145 |   });
  146 | 
  147 |   test('student programs has comparison', async ({ context }) => {
  148 |     const page = await loginFresh(context, 'Student');
  149 |     await page.goto('/student/programs.php');
  150 |     await expect(page.locator('h2').filter({ hasText: 'Program Comparison' })).toBeVisible();
  151 |   });
  152 | 
  153 |   test('student catalog has search', async ({ context }) => {
  154 |     const page = await loginFresh(context, 'Student');
  155 |     await page.goto('/student/catalog.php');
  156 |     await expect(page.locator('input[name="search"]')).toBeVisible();
  157 |   });
  158 | 
  159 |   test('student buildings has room listings', async ({ context }) => {
  160 |     const page = await loginFresh(context, 'Student');
  161 |     await page.goto('/student/buildings.php');
  162 |     await expect(page.locator('body')).toContainText(/Building|Room/);
  163 |   });
  164 | 
  165 |   test('student pages have no JS errors', async ({ context }) => {
  166 |     const page = await loginFresh(context, 'Student');
  167 |     const errors: string[] = [];
  168 |     page.on('pageerror', (err) => errors.push(err.message));
  169 |     await page.goto('/student/dashboard.php');
  170 |     await page.goto('/student/registration.php');
  171 |     await page.goto('/student/schedule.php');
  172 |     expect(errors).toHaveLength(0);
  173 |   });
  174 | });
  175 | 
  176 | // ==================== FACULTY PORTAL ====================
  177 | test.describe('FACULTY PORTAL', () => {
  178 |   test('all pages load', async ({ context }) => {
  179 |     const page = await loginFresh(context, 'Faculty');
  180 |     const pages = [
  181 |       '/faculty/dashboard.php', '/faculty/schedule.php', '/faculty/master-schedule.php',
  182 |       '/faculty/rosters.php', '/faculty/attendance-calendar.php', '/faculty/attendance-history.php',
  183 |       '/faculty/attendance-weekly.php', '/faculty/attendance-time-rules.php', '/faculty/attendance-window.php',
  184 |       '/faculty/advisees.php', '/faculty/catalog.php', '/faculty/profile.php',
  185 |       '/faculty/advising_appointments.php',
  186 |     ];
  187 |     for (const url of pages) {
  188 |       await page.goto(url);
  189 |       await expect(page).toHaveURL(url);
  190 |       await expect(page.locator('body')).toBeVisible();
  191 |     }
  192 |   });
  193 | 
  194 |   test('faculty rosters have midterm grade entry', async ({ context }) => {
  195 |     const page = await loginFresh(context, 'Faculty');
  196 |     await page.goto('/faculty/rosters.php');
  197 |     await expect(page.locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select')).toBeVisible({ timeout: 5000 });
  198 |   });
  199 | 
  200 |   test('faculty attendance-weekly has section selector', async ({ context }) => {
  201 |     const page = await loginFresh(context, 'Faculty');
  202 |     await page.goto('/faculty/attendance-weekly.php');
  203 |     await expect(page.locator('select[name="crn"], .wa-course-tab')).toBeVisible({ timeout: 5000 });
  204 |   });
  205 | 
  206 |   test('faculty advising appointments has availability window', async ({ context }) => {
  207 |     const page = await loginFresh(context, 'Faculty');
```