# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: interconnect.spec.ts >> Admin Portal — All Pages Load >> dashboard shows university overview stats
- Location: tests\interconnect.spec.ts:219:3

# Error details

```
TypeError: (0 , _test.expect)(...).toHaveCountGreaterThan is not a function
```

# Page snapshot

```yaml
- generic [active] [ref=f3e1]:
  - banner [ref=f3e2]:
    - generic [ref=f3e3]:
      - generic [ref=f3e4]: "N"
      - generic [ref=f3e5]:
        - generic [ref=f3e6]: Ashford University
        - generic [ref=f3e7]: Admin Portal
    - generic [ref=f3e8]:
      - generic [ref=f3e9]:
        - strong [ref=f3e10]: Ariana Augustin
        - generic [ref=f3e11]: arianaaugustin@ashford.edu
      - link "Logout" [ref=f3e12] [cursor=pointer]:
        - /url: /logout.php
  - generic [ref=f3e13]:
    - button "Collapse navigation" [expanded] [ref=f3e14] [cursor=pointer]: ☰
    - complementary [ref=f3e15]:
      - link "Dashboard" [ref=f3e16] [cursor=pointer]:
        - /url: dashboard.php
      - link "User Management" [ref=f3e24] [cursor=pointer]:
        - /url: users.php
      - link "Degree Audit" [ref=f3e31] [cursor=pointer]:
        - /url: degree-audit.php
      - link "Course Management" [ref=f3e35] [cursor=pointer]:
        - /url: academic.php
      - link "Course Sections" [ref=f3e41] [cursor=pointer]:
        - /url: sections.php
      - link "Master Schedule" [ref=f3e47] [cursor=pointer]:
        - /url: master-schedule.php
      - link "Security & Logs" [ref=f3e53] [cursor=pointer]:
        - /url: audit.php
    - main [ref=f3e59]:
      - heading "Admin Overview" [level=1] [ref=f3e60]
      - generic [ref=f3e61]:
        - generic [ref=f3e62]:
          - generic [ref=f3e63]:
            - paragraph [ref=f3e64]: Administration
            - heading "University Overview" [level=2] [ref=f3e65]
            - paragraph [ref=f3e66]: Academic administration at a glance
          - generic [ref=f3e67]: Security Level 1 — Full-Access Administrator
        - region "University statistics" [ref=f3e68]:
          - generic [ref=f3e69]:
            - generic [ref=f3e70]: Students
            - strong [ref=f3e71]: 1,600
          - generic [ref=f3e72]:
            - generic [ref=f3e73]: Faculty
            - strong [ref=f3e74]: "350"
          - generic [ref=f3e75]:
            - generic [ref=f3e76]: Course Sections
            - strong [ref=f3e77]: 2,533
        - generic [ref=f3e79]:
          - generic [ref=f3e80]:
            - heading "Fall 2026 Semester" [level=3] [ref=f3e81]
            - paragraph [ref=f3e82]: 372 sections · 372 active
          - link "View Master Schedule" [ref=f3e83] [cursor=pointer]:
            - /url: /admin/master-schedule.php
        - generic [ref=f3e84]:
          - generic [ref=f3e85]:
            - heading "Recent Course Sections" [level=3] [ref=f3e86]
            - link "View All Sections" [ref=f3e87] [cursor=pointer]:
              - /url: /admin/sections.php
          - generic [ref=f3e88]:
            - searchbox "Search displayed table rows" [ref=f3e89]
            - combobox "Filter displayed rows by status" [ref=f3e90]:
              - option "All statuses" [selected]
              - option "Active"
            - button "Reset" [ref=f3e91] [cursor=pointer]
            - button "Export CSV" [ref=f3e92] [cursor=pointer]
            - generic [ref=f3e93]: 5 / 5 displayed-page rows
          - table [ref=f3e95]:
            - rowgroup [ref=f3e96]:
              - row [ref=f3e97]:
                - button "CRN ↕" [ref=f3e98] [cursor=pointer]
                - button "Course ↕" [ref=f3e99] [cursor=pointer]
                - button "Section ↕" [ref=f3e100] [cursor=pointer]
                - button "Semester ↕" [ref=f3e101] [cursor=pointer]
                - button "Enrolled (Demo) ↕" [ref=f3e102] [cursor=pointer]
                - button "Seats Remaining ↕" [ref=f3e103] [cursor=pointer]
                - button "Status ↕" [ref=f3e104] [cursor=pointer]
            - rowgroup [ref=f3e105]:
              - row [ref=f3e106]:
                - cell [ref=f3e107]:
                  - text: "140024"
                  - button "Copy CRN 140024" [ref=f3e108] [cursor=pointer]: Copy
                - cell "Corporate Finance" [ref=f3e109]
                - cell "02" [ref=f3e110]
                - cell "Spring 2022" [ref=f3e111]
                - cell "0" [ref=f3e112]
                - cell "4" [ref=f3e113]
                - cell "Active" [ref=f3e114]
              - row [ref=f3e115]:
                - cell [ref=f3e116]:
                  - text: "140023"
                  - button "Copy CRN 140023" [ref=f3e117] [cursor=pointer]: Copy
                - cell "Principles of Marketing" [ref=f3e118]
                - cell "02" [ref=f3e119]
                - cell "Spring 2022" [ref=f3e120]
                - cell "0" [ref=f3e121]
                - cell "4" [ref=f3e122]
                - cell "Active" [ref=f3e123]
              - row [ref=f3e124]:
                - cell [ref=f3e125]:
                  - text: "140022"
                  - button "Copy CRN 140022" [ref=f3e126] [cursor=pointer]: Copy
                - cell "Computer Networks" [ref=f3e127]
                - cell "02" [ref=f3e128]
                - cell "Spring 2022" [ref=f3e129]
                - cell "0" [ref=f3e130]
                - cell "4" [ref=f3e131]
                - cell "Active" [ref=f3e132]
              - row [ref=f3e133]:
                - cell [ref=f3e134]:
                  - text: "140021"
                  - button "Copy CRN 140021" [ref=f3e135] [cursor=pointer]: Copy
                - cell "Database Systems" [ref=f3e136]
                - cell "02" [ref=f3e137]
                - cell "Spring 2022" [ref=f3e138]
                - cell "0" [ref=f3e139]
                - cell "4" [ref=f3e140]
                - cell "Active" [ref=f3e141]
              - row [ref=f3e142]:
                - cell [ref=f3e143]:
                  - text: "140020"
                  - button "Copy CRN 140020" [ref=f3e144] [cursor=pointer]: Copy
                - cell "Literature and Society" [ref=f3e145]
                - cell "01" [ref=f3e146]
                - cell "Spring 2022" [ref=f3e147]
                - cell "0" [ref=f3e148]
                - cell "2" [ref=f3e149]
                - cell "Active" [ref=f3e150]
```

# Test source

```ts
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
  147 | 
  148 | // ==================== FACULTY PORTAL ====================
  149 | test.describe('Faculty Portal — All Pages Load', () => {
  150 |   test.use({ storageState: { cookies: [], origins: [] } });
  151 | 
  152 |   const facultyPages = [
  153 |     '/faculty/dashboard.php',
  154 |     '/faculty/schedule.php',
  155 |     '/faculty/master-schedule.php',
  156 |     '/faculty/rosters.php',
  157 |     '/faculty/attendance-calendar.php',
  158 |     '/faculty/attendance-history.php',
  159 |     '/faculty/attendance-weekly.php',
  160 |     '/faculty/attendance-time-rules.php',
  161 |     '/faculty/attendance-window.php',
  162 |     '/faculty/advisees.php',
  163 |     '/faculty/catalog.php',
  164 |     '/faculty/profile.php',
  165 |   ];
  166 | 
  167 |   test('all faculty pages load via direct URL', async ({ page }) => {
  168 |     const { loginAs } = await import('./auth-helper');
  169 |     await loginAs(page, 'Faculty');
  170 | 
  171 |     for (const url of facultyPages) {
  172 |       await page.goto(url);
  173 |       await expect(page).toHaveURL(url);
  174 |       await expect(page.locator('body')).toBeVisible();
  175 |     }
  176 |   });
  177 | 
  178 |   test('dashboard shows quick actions', async ({ page }) => {
  179 |     const { loginAs } = await import('./auth-helper');
  180 |     await loginAs(page, 'Faculty');
  181 |     await page.goto('/faculty/dashboard.php');
  182 | 
  183 |     await expect(page.locator('a[href*="advisees.php"]')).toBeVisible();
  184 |     await expect(page.locator('a[href*="rosters.php"]')).toBeVisible();
  185 |     await expect(page.locator('a[href*="attendance-calendar.php"]')).toBeVisible();
  186 |     await expect(page.locator('a[href*="master-schedule.php"]')).toBeVisible();
  187 |   });
  188 | });
  189 | 
  190 | // ==================== ADMIN PORTAL ====================
  191 | test.describe('Admin Portal — All Pages Load', () => {
  192 |   test.use({ storageState: { cookies: [], origins: [] } });
  193 | 
  194 |   const adminPages = [
  195 |     '/admin/dashboard.php',
  196 |     '/admin/users.php',
  197 |     '/admin/academic.php',
  198 |     '/admin/sections.php',
  199 |     '/admin/semesters.php',
  200 |     '/admin/advisors.php',
  201 |     '/admin/holds.php',
  202 |     '/admin/exceptions.php',
  203 |     '/admin/degree-audit.php',
  204 |     '/admin/master-schedule.php',
  205 |     '/admin/audit.php',
  206 |   ];
  207 | 
  208 |   test('all admin pages load via direct URL', async ({ page }) => {
  209 |     const { loginAs } = await import('./auth-helper');
  210 |     await loginAs(page, 'Admin');
  211 | 
  212 |     for (const url of adminPages) {
  213 |       await page.goto(url);
  214 |       await expect(page).toHaveURL(url);
  215 |       await expect(page.locator('body')).toBeVisible();
  216 |     }
  217 |   });
  218 | 
  219 |   test('dashboard shows university overview stats', async ({ page }) => {
  220 |     const { loginAs } = await import('./auth-helper');
  221 |     await loginAs(page, 'Admin');
  222 |     await page.goto('/admin/dashboard.php');
  223 | 
  224 |     await expect(page.locator('body')).toContainText(/student|faculty|course/i);
> 225 |     await expect(page.locator('.ao-stat, .dashboard-stat')).toHaveCountGreaterThan(0);
      |                                                             ^ TypeError: (0 , _test.expect)(...).toHaveCountGreaterThan is not a function
  226 |   });
  227 | 
  228 |   test('master schedule page has validation report', async ({ page }) => {
  229 |     const { loginAs } = await import('./auth-helper');
  230 |     await loginAs(page, 'Admin');
  231 |     await page.goto('/admin/master-schedule.php');
  232 | 
  233 |     await expect(page.locator('h1')).toContainText('Master Schedule');
  234 |     await expect(page.locator('body')).toContainText(/section|scheduled|validation/i);
  235 |   });
  236 | });
  237 | 
  238 | // ==================== STATSTAFF PORTAL ====================
  239 | test.describe('StatStaff Portal — All Pages & Read-Only Access', () => {
  240 |   test.use({ storageState: { cookies: [], origins: [] } });
  241 | 
  242 |   test('dashboard and reports load', async ({ page }) => {
  243 |     const { loginAs } = await import('./auth-helper');
  244 |     await loginAs(page, 'StatStaff');
  245 | 
  246 |     await page.goto('/statstaff/dashboard.php');
  247 |     await expect(page).toHaveURL('/statstaff/dashboard.php');
  248 | 
  249 |     await page.goto('/statstaff/reports.php');
  250 |     await expect(page).toHaveURL('/statstaff/reports.php');
  251 |   });
  252 | 
  253 |   test('statstaff cannot access admin pages', async ({ page }) => {
  254 |     const { loginAs } = await import('./auth-helper');
  255 |     await loginAs(page, 'StatStaff');
  256 | 
  257 |     const adminPages = [
  258 |       '/admin/users.php',
  259 |       '/admin/sections.php',
  260 |       '/admin/semesters.php',
  261 |       '/admin/academic.php',
  262 |     ];
  263 | 
  264 |     for (const url of adminPages) {
  265 |       await page.goto(url);
  266 |       await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
  267 |     }
  268 |   });
  269 | });
  270 | 
  271 | // ==================== CROSS-PORTAL INTERCONNECT ====================
  272 | test.describe('Cross-Portal Interconnectivity', () => {
  273 |   test('sign-in page links to register and public catalog', async ({ page }) => {
  274 |     await page.goto('/index.php');
  275 |     await expect(page.locator('a[href*="register.php"]')).toBeVisible();
  276 |     await expect(page.locator('a[href*="public/index.php"]')).toBeVisible();
  277 |   });
  278 | 
  279 |   test('register page links back to sign-in', async ({ page }) => {
  280 |     await page.goto('/register.php');
  281 |     await expect(page.locator('a[href*="index.php"]')).toBeVisible();
  282 |   });
  283 | 
  284 |   test('all portals share header and logout (separate contexts)', async ({ browser }) => {
  285 |     for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
  286 |       const context = await browser.newContext();
  287 |       const page = await context.newPage();
  288 |       await page.goto('/index.php');
  289 |       await page.fill('input[name="email"]', CREDENTIALS[role].email);
  290 |       await page.fill('input[name="password"]', CREDENTIALS[role].password);
  291 |       await page.selectOption('select[name="user_type"]', role);
  292 |       await page.click('button[type="submit"]');
  293 |       await expect(page).toHaveURL(ROLE_HOMES[role]);
  294 |       await expect(page.locator('.topbar')).toBeVisible();
  295 |       await expect(page.locator('.brand b')).toContainText('Ashford University');
  296 |       await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
  297 |       await context.close();
  298 |     }
  299 |   });
  300 | });
  301 | 
  302 | // ==================== PAGE COUNT SUMMARY ====================
  303 | test.describe('Portal Page Count Verification', () => {
  304 |   test('verifies total page count across all portals', async ({ page }) => {
  305 |     const { loginAs } = await import('./auth-helper');
  306 | 
  307 |     const counts = {
  308 |       Public: 4,  // departments, courses, programs, schedule
  309 |       Student: 13,
  310 |       Faculty: 12,
  311 |       Admin: 11,
  312 |       StatStaff: 2,
  313 |     };
  314 | 
  315 |     // Student
  316 |     await loginAs(page, 'Student');
  317 |     for (const url of [
  318 |       '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
  319 |       '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
  320 |       '/student/holds.php', '/student/advisor.php', '/student/programs.php',
  321 |       '/student/catalog.php', '/student/buildings.php', '/student/information.php',
  322 |       '/student/advisor_appointment.php',
  323 |     ]) {
  324 |       await page.goto(url);
  325 |       await expect(page.locator('body')).toBeVisible();
```