# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> ADMIN PORTAL >> admin users has bulk actions and CSV export
- Location: tests/comprehensive.spec.ts:252:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('#users-bulk-form, #users-bulk-submit')
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" locator('#users-bulk-form, #users-bulk-submit') with timeout 5000ms
  - waiting for locator('#users-bulk-form, #users-bulk-submit')

```

```yaml
- banner:
  - text: N Ashford University Admin Portal
  - strong: Ariana Augustin
  - text: arianaaugustin@ashford.edu
  - link "Logout":
    - /url: /logout.php
- button "Collapse navigation" [expanded]: ☰
- complementary:
  - link "Dashboard":
    - /url: dashboard.php
  - link "User Management":
    - /url: users.php
  - link "Degree Audit":
    - /url: degree-audit.php
  - link "Course Management":
    - /url: academic.php
  - link "Course Sections":
    - /url: sections.php
  - link "Master Schedule":
    - /url: master-schedule.php
  - link "Security & Logs":
    - /url: audit.php
- main:
  - heading "Users" [level=1]
  - paragraph: Administration
  - heading "User Management" [level=2]
  - text: Read-Only
  - tablist "User management":
    - button "List Users"
    - button "Student Holds"
  - heading "University User Directory" [level=3]
  - paragraph: Find students, faculty, administrators, and statistics staff.
  - text: Search Users
  - searchbox "Search Users"
  - text: User Role
  - combobox "User Role":
    - option "All Roles" [selected]
    - option "Students"
    - option "Faculty"
    - option "Administrators"
    - option "Statistics Staff"
  - text: Status (current page)
  - combobox "Status (current page)":
    - option "All Statuses" [selected]
    - option "Active"
    - option "Locked"
  - button "Search"
  - link "Clear":
    - /url: users.php
  - button "Export CSV"
  - text: Showing 25 of 2057 matching users · Page 1 of 83
  - table:
    - rowgroup:
      - row "User ID Full Name Email Role Status Failed Attempts Location Actions":
        - columnheader "User ID"
        - columnheader "Full Name"
        - columnheader "Email"
        - columnheader "Role"
        - columnheader "Status"
        - columnheader "Failed Attempts"
        - columnheader "Location"
        - columnheader "Actions"
    - rowgroup:
      - row "400050 Marcus Abbas marcus.abbas@ashford.edu Faculty Active 0 New York, NY":
        - cell "400050":
          - link "400050":
            - /url: degree-audit.php?student_id=400050&search=&role=&page=1#student-audit
        - cell "Marcus Abbas"
        - cell "marcus.abbas@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "New York, NY"
        - cell
      - row "100632 Adrian Adams adrianadams@ashford.edu Student Active 0 Baldwin, NY":
        - cell "100632":
          - link "100632":
            - /url: degree-audit.php?student_id=100632&search=&role=&page=1#student-audit
        - cell "Adrian Adams"
        - cell "adrianadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Baldwin, NY"
        - cell
      - row "200259 Adrian Adams adrianadams200259@ashford.edu Faculty Active 0 Freeport, NY":
        - cell "200259":
          - link "200259":
            - /url: degree-audit.php?student_id=200259&search=&role=&page=1#student-audit
        - cell "Adrian Adams"
        - cell "adrianadams200259@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "Freeport, NY"
        - cell
      - row "100144 Andre Adams andreadams@ashford.edu Student Active 0 Elmont, NY":
        - cell "100144":
          - link "100144":
            - /url: degree-audit.php?student_id=100144&search=&role=&page=1#student-audit
        - cell "Andre Adams"
        - cell "andreadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Elmont, NY"
        - cell
      - row "200338 Caleb Adams calebadams@ashford.edu Faculty Active 0 Mineola, NY":
        - cell "200338":
          - link "200338":
            - /url: degree-audit.php?student_id=200338&search=&role=&page=1#student-audit
        - cell "Caleb Adams"
        - cell "calebadams@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "Mineola, NY"
        - cell
      - row "100823 Chloe Adams chloeadams@ashford.edu Student Active 0 Freeport, NY":
        - cell "100823":
          - link "100823":
            - /url: degree-audit.php?student_id=100823&search=&role=&page=1#student-audit
        - cell "Chloe Adams"
        - cell "chloeadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Freeport, NY"
        - cell
      - row "200183 Elena Adams elenaadams@ashford.edu Faculty Active 0 Freeport, NY":
        - cell "200183":
          - link "200183":
            - /url: degree-audit.php?student_id=200183&search=&role=&page=1#student-audit
        - cell "Elena Adams"
        - cell "elenaadams@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "Freeport, NY"
        - cell
      - row "100514 Elijah Adams elijahadams@ashford.edu Student Active 0 Bronx, NY":
        - cell "100514":
          - link "100514":
            - /url: degree-audit.php?student_id=100514&search=&role=&page=1#student-audit
        - cell "Elijah Adams"
        - cell "elijahadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Bronx, NY"
        - cell
      - row "100739 Elijah Adams elijahadams100739@ashford.edu Student Active 0 Mineola, NY":
        - cell "100739":
          - link "100739":
            - /url: degree-audit.php?student_id=100739&search=&role=&page=1#student-audit
        - cell "Elijah Adams"
        - cell "elijahadams100739@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Mineola, NY"
        - cell
      - row "100156 Emily Adams emilyadams@ashford.edu Student Active 0 Baldwin, NY":
        - cell "100156":
          - link "100156":
            - /url: degree-audit.php?student_id=100156&search=&role=&page=1#student-audit
        - cell "Emily Adams"
        - cell "emilyadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Baldwin, NY"
        - cell
      - row "101371 Jada Adams jadaadams@ashford.edu Student Active 0 Westbury, NY":
        - cell "101371":
          - link "101371":
            - /url: degree-audit.php?student_id=101371&search=&role=&page=1#student-audit
        - cell "Jada Adams"
        - cell "jadaadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Westbury, NY"
        - cell
      - row "100544 Jasmine Adams jasmineadams@ashford.edu Student Active 0 Levittown, NY":
        - cell "100544":
          - link "100544":
            - /url: degree-audit.php?student_id=100544&search=&role=&page=1#student-audit
        - cell "Jasmine Adams"
        - cell "jasmineadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Levittown, NY"
        - cell
      - row "200055 Jasmine Adams jasmineadams200055@ashford.edu Faculty Active 0 Bronx, NY":
        - cell "200055":
          - link "200055":
            - /url: degree-audit.php?student_id=200055&search=&role=&page=1#student-audit
        - cell "Jasmine Adams"
        - cell "jasmineadams200055@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "Bronx, NY"
        - cell
      - row "100875 Jessica Adams jessicaadams@ashford.edu Student Active 0 Hempstead, NY":
        - cell "100875":
          - link "100875":
            - /url: degree-audit.php?student_id=100875&search=&role=&page=1#student-audit
        - cell "Jessica Adams"
        - cell "jessicaadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Hempstead, NY"
        - cell
      - row "200029 Jonathan Adams jonathanadams@ashford.edu Faculty Active 0 Queens, NY":
        - cell "200029":
          - link "200029":
            - /url: degree-audit.php?student_id=200029&search=&role=&page=1#student-audit
        - cell "Jonathan Adams"
        - cell "jonathanadams@ashford.edu"
        - cell "Faculty"
        - cell "Active"
        - cell "0"
        - cell "Queens, NY"
        - cell
      - row "100158 Julian Adams julianadams@ashford.edu Student Active 0 Baldwin, NY":
        - cell "100158":
          - link "100158":
            - /url: degree-audit.php?student_id=100158&search=&role=&page=1#student-audit
        - cell "Julian Adams"
        - cell "julianadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Baldwin, NY"
        - cell
      - row "101263 Leah Adams leahadams@ashford.edu Student Active 0 Mineola, NY":
        - cell "101263":
          - link "101263":
            - /url: degree-audit.php?student_id=101263&search=&role=&page=1#student-audit
        - cell "Leah Adams"
        - cell "leahadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Mineola, NY"
        - cell
      - row "101341 Leah Adams leahadams101341@ashford.edu Student Active 0 Levittown, NY":
        - cell "101341":
          - link "101341":
            - /url: degree-audit.php?student_id=101341&search=&role=&page=1#student-audit
        - cell "Leah Adams"
        - cell "leahadams101341@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Levittown, NY"
        - cell
      - row "300015 Malcolm Adams malcolmadams@ashford.edu Student Active 0 Brooklyn, NY":
        - cell "300015":
          - link "300015":
            - /url: degree-audit.php?student_id=300015&search=&role=&page=1#student-audit
        - cell "Malcolm Adams"
        - cell "malcolmadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Brooklyn, NY"
        - cell
      - row "101411 Marcus Adams marcusadams@ashford.edu Student Active 0 New Hyde Park, NY":
        - cell "101411":
          - link "101411":
            - /url: degree-audit.php?student_id=101411&search=&role=&page=1#student-audit
        - cell "Marcus Adams"
        - cell "marcusadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "New Hyde Park, NY"
        - cell
      - row "100868 Mason Adams masonadams@ashford.edu Student Active 0 New Hyde Park, NY":
        - cell "100868":
          - link "100868":
            - /url: degree-audit.php?student_id=100868&search=&role=&page=1#student-audit
        - cell "Mason Adams"
        - cell "masonadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "New Hyde Park, NY"
        - cell
      - row "101361 Mason Adams masonadams101361@ashford.edu Student Active 0 Levittown, NY":
        - cell "101361":
          - link "101361":
            - /url: degree-audit.php?student_id=101361&search=&role=&page=1#student-audit
        - cell "Mason Adams"
        - cell "masonadams101361@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Levittown, NY"
        - cell
      - row "101369 Matthew Adams matthewadams@ashford.edu Student Active 0 Valley Stream, NY":
        - cell "101369":
          - link "101369":
            - /url: degree-audit.php?student_id=101369&search=&role=&page=1#student-audit
        - cell "Matthew Adams"
        - cell "matthewadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Valley Stream, NY"
        - cell
      - row "101109 Mia Adams miaadams@ashford.edu Student Active 0 Mineola, NY":
        - cell "101109":
          - link "101109":
            - /url: degree-audit.php?student_id=101109&search=&role=&page=1#student-audit
        - cell "Mia Adams"
        - cell "miaadams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Mineola, NY"
        - cell
      - row "101173 Michael Adams michaeladams@ashford.edu Student Active 0 Jamaica, NY":
        - cell "101173":
          - link "101173":
            - /url: degree-audit.php?student_id=101173&search=&role=&page=1#student-audit
        - cell "Michael Adams"
        - cell "michaeladams@ashford.edu"
        - cell "Student"
        - cell "Active"
        - cell "0"
        - cell "Jamaica, NY"
        - cell
  - navigation "User directory pages":
    - text: Page 1 of 83
    - link "Next":
      - /url: "?search=&role=&page=2"
```

# Test source

```ts
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
> 255 |     await expect(page.locator('#users-bulk-form, #users-bulk-submit')).toBeVisible({ timeout: 5000 });
      |                                                                        ^ Error: expect(locator).toBeVisible() failed
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
  291 |   test('reports page has new reports', async ({ context }) => {
  292 |     const page = await loginFresh(context, 'StatStaff');
  293 |     await page.goto('/statstaff/reports.php');
  294 |     await expect(page.locator('select[name="report"]')).toContainText('Enrollment trends over time');
  295 |     await expect(page.locator('select[name="report"]')).toContainText('Department summary');
  296 |   });
  297 | 
  298 |   test('read-only enforcement on admin pages', async ({ context }) => {
  299 |     const page = await loginFresh(context, 'StatStaff');
  300 |     const adminPages = ['/admin/users.php', '/admin/sections.php', '/admin/semesters.php', '/admin/academic.php'];
  301 |     for (const url of adminPages) {
  302 |       await page.goto(url);
  303 |       await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
  304 |     }
  305 |   });
  306 | });
  307 | 
  308 | // ==================== CROSS-PORTAL ====================
  309 | test.describe('CROSS-PORTAL', () => {
  310 |   test('all portals share header and logout', async ({ browser }) => {
  311 |     for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
  312 |       const context = await browser.newContext();
  313 |       const page = await loginFresh(context, role);
  314 |       await expect(page.locator('.topbar')).toBeVisible();
  315 |       await expect(page.locator('.brand b')).toContainText('Ashford University');
  316 |       await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
  317 |       await context.close();
  318 |     }
  319 |   });
  320 | 
  321 |   test('sign-in page links to register and public catalog', async ({ page }) => {
  322 |     await page.goto('/index.php');
  323 |     await expect(page.locator('a[href*="register.php"]')).toBeVisible();
  324 |     await expect(page.locator('a[href*="public/index.php"]')).toBeVisible();
  325 |   });
  326 | });
  327 | 
```