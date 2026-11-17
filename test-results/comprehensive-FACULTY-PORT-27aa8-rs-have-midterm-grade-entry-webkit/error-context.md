# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> FACULTY PORTAL >> faculty rosters have midterm grade entry
- Location: tests/comprehensive.spec.ts:194:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select')
Expected: visible
Error: strict mode violation: locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select') resolved to 8 elements:
    1) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '100515 Nicole Baker' }).getByRole('combobox')
    2) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '100379 Ava Brown' }).getByRole('combobox')
    3) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '101528 Maya Diaz mayadiaz@' }).getByRole('combobox')
    4) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '100959 Elijah Johnson' }).getByRole('combobox')
    5) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '101481 Nicholas Lee' }).getByRole('combobox')
    6) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka getByRole('row', { name: '100254 Victoria Louis' }).locator('select[name="midterm_grade"]')
    7) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka locator('tr:nth-child(13) > td:nth-child(4) > form > .roster-grade-select')
    8) <select name="midterm_grade" class="roster-grade-select" onchange="this.form.submit()">…</select> aka locator('tr:nth-child(15) > td:nth-child(4) > form > .roster-grade-select')

Call log:
  - Expect "toBeVisible" locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select') with timeout 5000ms
  - waiting for locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select')

```

# Page snapshot

```yaml
- generic [active] [ref=f3e1]:
  - banner [ref=f3e2]:
    - generic [ref=f3e3]:
      - generic [ref=f3e4]: "N"
      - generic [ref=f3e5]:
        - generic [ref=f3e6]: Ashford University
        - generic [ref=f3e7]: Faculty Portal
    - generic [ref=f3e8]:
      - generic [ref=f3e9]:
        - strong [ref=f3e10]: Kevin Baptiste
        - generic [ref=f3e11]: kevinbaptiste200001@ashford.edu
      - link "Logout" [ref=f3e12] [cursor=pointer]:
        - /url: /logout.php
  - generic [ref=f3e13]:
    - button "Collapse navigation" [expanded] [ref=f3e14] [cursor=pointer]: ☰
    - complementary [ref=f3e15]:
      - link "Overview" [ref=f3e16]:
        - /url: dashboard.php
      - link "My Schedule" [ref=f3e20]:
        - /url: schedule.php
      - link "Advising Appointments" [ref=f3e24]:
        - /url: advising_appointments.php
      - link "Master Schedule" [ref=f3e28]:
        - /url: master-schedule.php
      - link "Course Rosters" [ref=f3e34]:
        - /url: rosters.php
      - link "Attendance History" [ref=f3e38]:
        - /url: attendance-history.php
      - link "Attendance Calendar" [ref=f3e45]:
        - /url: attendance-calendar.php
      - link "Weekly Attendance" [ref=f3e51]:
        - /url: attendance-weekly.php
      - link "Advisees" [ref=f3e56]:
        - /url: advisees.php
      - link "Catalog" [ref=f3e63]:
        - /url: catalog.php
      - link "My Profile" [ref=f3e69]:
        - /url: profile.php
    - main [ref=f3e73]:
      - heading "Course Rosters" [level=1] [ref=f3e74]
      - generic [ref=f3e75]:
        - generic [ref=f3e76]:
          - heading "Course Rosters" [level=2] [ref=f3e77]
          - paragraph [ref=f3e78]: Faculty Portal · Academic Year 2026–2027
        - navigation "Semester" [ref=f3e79]:
          - link "Fall 2026" [ref=f3e80]:
            - /url: rosters.php?term=fall
          - link "Spring 2027 (TBD)" [ref=f3e81]:
            - /url: rosters.php?term=spring
        - generic [ref=f3e82]:
          - heading "Fall 2026 · Assigned Courses" [level=3] [ref=f3e83]
          - navigation "Courses" [ref=f3e84]:
            - link "BU3071 · CRN 10001" [ref=f3e85]:
              - /url: rosters.php?term=fall&crn=10001
        - generic [ref=f3e86]:
          - generic [ref=f3e87]:
            - text: Total Enrolled
            - strong [ref=f3e88]: "8"
          - generic [ref=f3e89]:
            - text: Average Attendance
            - strong [ref=f3e90]: N/A
          - generic [ref=f3e91]:
            - text: Final Grades Posted
            - strong [ref=f3e92]: 0 / 8
          - generic [ref=f3e93]:
            - text: Midterm Grades Posted
            - strong [ref=f3e94]: 0 / 8
        - generic [ref=f3e95]:
          - generic [ref=f3e97]:
            - heading "BU3071 — Information Technology & Application" [level=3] [ref=f3e98]
            - text: Student details, attendance and grade management
          - generic [ref=f3e99]:
            - generic [ref=f3e100]:
              - searchbox "Search displayed table rows" [ref=f3e101]
              - combobox "Filter displayed rows by status" [ref=f3e102]:
                - option "All statuses" [selected]
                - option "Enrolled"
              - button "Reset" [ref=f3e103] [cursor=pointer]
              - button "Export CSV" [ref=f3e104] [cursor=pointer]
              - generic [ref=f3e105]: 8 / 8 displayed-page rows
            - table [ref=f3e106]:
              - rowgroup [ref=f3e107]:
                - row [ref=f3e108]:
                  - button "Student ID ↕" [ref=f3e109] [cursor=pointer]
                  - button "Full Name ↕" [ref=f3e110] [cursor=pointer]
                  - button "University Email ↕" [ref=f3e111] [cursor=pointer]
                  - button "Midterm ↕" [ref=f3e112] [cursor=pointer]
                  - button "Overall ↕" [ref=f3e113] [cursor=pointer]
                  - button "Final ↕" [ref=f3e114] [cursor=pointer]
                  - button "Attendance % ↕" [ref=f3e115] [cursor=pointer]
                  - button "Absences ↕" [ref=f3e116] [cursor=pointer]
                  - button "Status ↕" [ref=f3e117] [cursor=pointer]
                  - button "Details ↕" [ref=f3e118] [cursor=pointer]
              - rowgroup [ref=f3e119]:
                - row [ref=f3e120]:
                  - cell "100515" [ref=f3e121]
                  - cell "Nicole Baker" [ref=f3e122]
                  - cell "nicolebaker@ashford.edu" [ref=f3e123]
                  - cell [ref=f3e124]:
                    - combobox [ref=f3e126]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e127]
                  - cell "Not Graded" [ref=f3e128]
                  - cell "Not Recorded" [ref=f3e129]
                  - cell "0" [ref=f3e130]
                  - cell "Enrolled" [ref=f3e131]
                  - cell [ref=f3e132]:
                    - button "View" [ref=f3e133] [cursor=pointer]
                - row [ref=f3e134]:
                  - cell "100379" [ref=f3e135]
                  - cell "Ava Brown" [ref=f3e136]
                  - cell "avabrown100379@ashford.edu" [ref=f3e137]
                  - cell [ref=f3e138]:
                    - combobox [ref=f3e140]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e141]
                  - cell "Not Graded" [ref=f3e142]
                  - cell "Not Recorded" [ref=f3e143]
                  - cell "0" [ref=f3e144]
                  - cell "Enrolled" [ref=f3e145]
                  - cell [ref=f3e146]:
                    - button "View" [ref=f3e147] [cursor=pointer]
                - row [ref=f3e148]:
                  - cell "101528" [ref=f3e149]
                  - cell "Maya Diaz" [ref=f3e150]
                  - cell "mayadiaz@ashford.edu" [ref=f3e151]
                  - cell [ref=f3e152]:
                    - combobox [ref=f3e154]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e155]
                  - cell "Not Graded" [ref=f3e156]
                  - cell "Not Recorded" [ref=f3e157]
                  - cell "0" [ref=f3e158]
                  - cell "Enrolled" [ref=f3e159]
                  - cell [ref=f3e160]:
                    - button "View" [ref=f3e161] [cursor=pointer]
                - row [ref=f3e162]:
                  - cell "100959" [ref=f3e163]
                  - cell "Elijah Johnson" [ref=f3e164]
                  - cell "elijahjohnson@ashford.edu" [ref=f3e165]
                  - cell [ref=f3e166]:
                    - combobox [ref=f3e168]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e169]
                  - cell "Not Graded" [ref=f3e170]
                  - cell "Not Recorded" [ref=f3e171]
                  - cell "0" [ref=f3e172]
                  - cell "Enrolled" [ref=f3e173]
                  - cell [ref=f3e174]:
                    - button "View" [ref=f3e175] [cursor=pointer]
                - row [ref=f3e176]:
                  - cell "101481" [ref=f3e177]
                  - cell "Nicholas Lee" [ref=f3e178]
                  - cell "nicholaslee@ashford.edu" [ref=f3e179]
                  - cell [ref=f3e180]:
                    - combobox [ref=f3e182]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e183]
                  - cell "Not Graded" [ref=f3e184]
                  - cell "Not Recorded" [ref=f3e185]
                  - cell "0" [ref=f3e186]
                  - cell "Enrolled" [ref=f3e187]
                  - cell [ref=f3e188]:
                    - button "View" [ref=f3e189] [cursor=pointer]
                - row [ref=f3e190]:
                  - cell "100254" [ref=f3e191]
                  - cell "Victoria Louis" [ref=f3e192]
                  - cell "victorialouis@ashford.edu" [ref=f3e193]
                  - cell [ref=f3e194]:
                    - combobox [ref=f3e196]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e197]
                  - cell "Not Graded" [ref=f3e198]
                  - cell "Not Recorded" [ref=f3e199]
                  - cell "0" [ref=f3e200]
                  - cell "Enrolled" [ref=f3e201]
                  - cell [ref=f3e202]:
                    - button "View" [ref=f3e203] [cursor=pointer]
                - row [ref=f3e204]:
                  - cell "100270" [ref=f3e205]
                  - cell "Alexis Scott" [ref=f3e206]
                  - cell "alexisscott@ashford.edu" [ref=f3e207]
                  - cell [ref=f3e208]:
                    - combobox [ref=f3e210]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e211]
                  - cell "Not Graded" [ref=f3e212]
                  - cell "Not Recorded" [ref=f3e213]
                  - cell "0" [ref=f3e214]
                  - cell "Enrolled" [ref=f3e215]
                  - cell [ref=f3e216]:
                    - button "View" [ref=f3e217] [cursor=pointer]
                - row [ref=f3e218]:
                  - cell "100842" [ref=f3e219]
                  - cell "Victoria Wright" [ref=f3e220]
                  - cell "victoriawright100842@ashford.edu" [ref=f3e221]
                  - cell [ref=f3e222]:
                    - combobox [ref=f3e224]:
                      - option "Not Graded" [selected]
                      - option "A"
                      - option "A-"
                      - option "B+"
                      - option "B"
                      - option "B-"
                      - option "C+"
                      - option "C"
                      - option "C-"
                      - option "D+"
                      - option "D"
                      - option "F"
                      - option "W"
                      - option "I"
                  - cell "Not Graded" [ref=f3e225]
                  - cell "Not Graded" [ref=f3e226]
                  - cell "Not Recorded" [ref=f3e227]
                  - cell "0" [ref=f3e228]
                  - cell "Enrolled" [ref=f3e229]
                  - cell [ref=f3e230]:
                    - button "View" [ref=f3e231] [cursor=pointer]
```

# Test source

```ts
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
  190 |       await expect(page.locator('body')).toBeVisible();
  191 |     }
  192 |   });
  193 | 
  194 |   test('faculty rosters have midterm grade entry', async ({ context }) => {
  195 |     const page = await loginFresh(context, 'Faculty');
  196 |     await page.goto('/faculty/rosters.php');
> 197 |     await expect(page.locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select')).toBeVisible({ timeout: 5000 });
      |                                                                                                      ^ Error: expect(locator).toBeVisible() failed
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
  291 |   test('reports page has new reports', async ({ context }) => {
  292 |     const page = await loginFresh(context, 'StatStaff');
  293 |     await page.goto('/statstaff/reports.php');
  294 |     await expect(page.locator('select[name="report"]')).toContainText('Enrollment trends over time');
  295 |     await expect(page.locator('select[name="report"]')).toContainText('Department summary');
  296 |   });
  297 | 
```