# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> FACULTY PORTAL >> all pages load
- Location: tests/comprehensive.spec.ts:178:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator:  locator('body')
Expected: visible
Received: hidden
Timeout:  5000ms

Call log:
  - Expect "toBeVisible" locator('body') with timeout 5000ms
  - waiting for locator('body')
    14 × locator resolved to <body></body>
       - unexpected value "hidden"

```

# Test source

```ts
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
  107 |       await expect(page).toHaveURL(url);
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
> 190 |       await expect(page.locator('body')).toBeVisible();
      |                                          ^ Error: expect(locator).toBeVisible() failed
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
  208 |     await page.goto('/faculty/advising_appointments.php');
  209 |     await expect(page.locator('h3').filter({ hasText: 'Availability Window' })).toBeVisible();
  210 |     await expect(page.locator('button[type="submit"]').filter({ hasText: 'Add Availability' })).toBeVisible();
  211 |   });
  212 | 
  213 |   test('faculty profile has office info', async ({ context }) => {
  214 |     const page = await loginFresh(context, 'Faculty');
  215 |     await page.goto('/faculty/profile.php');
  216 |     await expect(page.locator('body')).toContainText(/Office|office/);
  217 |   });
  218 | 
  219 |   test('faculty advisees has academic status', async ({ context }) => {
  220 |     const page = await loginFresh(context, 'Faculty');
  221 |     await page.goto('/faculty/advisees.php');
  222 |     await expect(page.locator('body')).toContainText(/GPA|Credits|Holds|Registered/);
  223 |   });
  224 | 
  225 |   test('faculty pages have no JS errors', async ({ context }) => {
  226 |     const page = await loginFresh(context, 'Faculty');
  227 |     const errors: string[] = [];
  228 |     page.on('pageerror', (err) => errors.push(err.message));
  229 |     await page.goto('/faculty/dashboard.php');
  230 |     await page.goto('/faculty/advising_appointments.php');
  231 |     await page.goto('/faculty/attendance-weekly.php');
  232 |     expect(errors).toHaveLength(0);
  233 |   });
  234 | });
  235 | 
  236 | // ==================== ADMIN PORTAL ====================
  237 | test.describe('ADMIN PORTAL', () => {
  238 |   test('all pages load', async ({ context }) => {
  239 |     const page = await loginFresh(context, 'Admin');
  240 |     const pages = [
  241 |       '/admin/dashboard.php', '/admin/users.php', '/admin/academic.php', '/admin/sections.php',
  242 |       '/admin/semesters.php', '/admin/advisors.php', '/admin/holds.php', '/admin/exceptions.php',
  243 |       '/admin/degree-audit.php', '/admin/master-schedule.php', '/admin/audit.php',
  244 |     ];
  245 |     for (const url of pages) {
  246 |       await page.goto(url);
  247 |       await expect(page).toHaveURL(url);
  248 |       await expect(page.locator('body')).toBeVisible();
  249 |     }
  250 |   });
  251 | 
  252 |   test('admin users has bulk actions and CSV export', async ({ context }) => {
  253 |     const page = await loginFresh(context, 'Admin');
  254 |     await page.goto('/admin/users.php');
  255 |     await expect(page.locator('#users-bulk-form, #users-bulk-submit')).toBeVisible({ timeout: 5000 });
  256 |     await expect(page.locator('#users-export-csv')).toBeVisible();
  257 |   });
  258 | 
  259 |   test('admin holds has resolution workflow', async ({ context }) => {
  260 |     const page = await loginFresh(context, 'Admin');
  261 |     await page.goto('/admin/holds.php');
  262 |     await expect(page.locator('button[type="button"]').filter({ hasText: 'Resolve' })).toBeVisible({ timeout: 5000 });
  263 |   });
  264 | 
  265 |   test('admin master schedule has validation', async ({ context }) => {
  266 |     const page = await loginFresh(context, 'Admin');
  267 |     await page.goto('/admin/master-schedule.php');
  268 |     await expect(page.locator('h1')).toContainText('Master Schedule');
  269 |     await expect(page.locator('body')).toContainText(/section|scheduled|validation/i);
  270 |   });
  271 | 
  272 |   test('admin pages have no JS errors', async ({ context }) => {
  273 |     const page = await loginFresh(context, 'Admin');
  274 |     const errors: string[] = [];
  275 |     page.on('pageerror', (err) => errors.push(err.message));
  276 |     await page.goto('/admin/dashboard.php');
  277 |     await page.goto('/admin/users.php');
  278 |     await page.goto('/admin/master-schedule.php');
  279 |     expect(errors).toHaveLength(0);
  280 |   });
  281 | });
  282 | 
  283 | // ==================== STATSTAFF PORTAL ====================
  284 | test.describe('STATSTAFF PORTAL', () => {
  285 |   test('dashboard loads', async ({ context }) => {
  286 |     const page = await loginFresh(context, 'StatStaff');
  287 |     await page.goto('/statstaff/dashboard.php');
  288 |     await expect(page).toHaveURL('/statstaff/dashboard.php');
  289 |   });
  290 | 
```