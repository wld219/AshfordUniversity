# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> STUDENT PORTAL >> student degree audit has progress bars
- Location: tests/comprehensive.spec.ts:135:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('.progress-bar, .progress-fill')
Expected: visible
Error: strict mode violation: locator('.progress-bar, .progress-fill') resolved to 4 elements:
    1) <div class="progress-bar">…</div> aka locator('.progress-bar').first()
    2) <div class="progress-fill"></div> aka locator('.progress-fill').first()
    3) <div class="progress-bar">…</div> aka locator('section:nth-child(5) > .progress-wrap > .progress-bar')
    4) <div class="progress-fill"></div> aka locator('section:nth-child(5) > .progress-wrap > .progress-bar > .progress-fill')

Call log:
  - Expect "toBeVisible" locator('.progress-bar, .progress-fill') with timeout 5000ms
  - waiting for locator('.progress-bar, .progress-fill')

```

# Page snapshot

```yaml
- generic [active] [ref=f3e1]:
  - banner [ref=f3e2]:
    - generic [ref=f3e3]:
      - generic [ref=f3e4]: "N"
      - generic [ref=f3e5]:
        - generic [ref=f3e6]: Ashford University
        - generic [ref=f3e7]: Student Portal
    - generic [ref=f3e8]:
      - generic [ref=f3e9]:
        - strong [ref=f3e10]: David Joseph
        - generic [ref=f3e11]: davidjoseph@ashford.edu
      - link "Logout" [ref=f3e12] [cursor=pointer]:
        - /url: /logout.php
  - generic [ref=f3e13]:
    - complementary [ref=f3e14]:
      - button "Collapse navigation" [expanded] [ref=f3e15] [cursor=pointer]: ☰
      - link "Overview" [ref=f3e16] [cursor=pointer]:
        - /url: dashboard.php
      - link "Master Schedule" [ref=f3e24] [cursor=pointer]:
        - /url: master-schedule.php
      - link "Course Registration" [ref=f3e30] [cursor=pointer]:
        - /url: registration.php
      - link "My Schedule" [ref=f3e36] [cursor=pointer]:
        - /url: schedule.php
      - link "Degree Audit" [ref=f3e43] [cursor=pointer]:
        - /url: degree-audit.php
      - link "Unofficial Transcript" [ref=f3e49] [cursor=pointer]:
        - /url: transcript.php
      - link "Holds" [ref=f3e55] [cursor=pointer]:
        - /url: holds.php
      - link "My Advisor" [ref=f3e61] [cursor=pointer]:
        - /url: advisor.php
      - link "Majors & Minors" [ref=f3e67] [cursor=pointer]:
        - /url: programs.php
      - link "Catalog" [ref=f3e74] [cursor=pointer]:
        - /url: catalog.php
      - link "Buildings" [ref=f3e80] [cursor=pointer]:
        - /url: buildings.php
      - link "My Information" [ref=f3e86] [cursor=pointer]:
        - /url: information.php
    - main [ref=f3e93]:
      - heading "Degree Audit" [level=1] [ref=f3e94]
      - paragraph [ref=f3e95]: Requirements come from the published major and minor requirement lists. A requirement is met when your best recorded grade is at least the required minimum grade.
      - generic [ref=f3e96]:
        - generic [ref=f3e98]:
          - paragraph [ref=f3e99]: Major
          - heading "Cybersecurity" [level=2] [ref=f3e100]
        - paragraph [ref=f3e101]:
          - strong [ref=f3e102]: 1 of 13 requirements met
          - generic [ref=f3e103]: 8% complete
        - generic [ref=f3e104]: 1 of 13 courses completed
        - generic [ref=f3e108]:
          - searchbox "Search displayed table rows" [ref=f3e109]
          - combobox "Filter displayed rows by status" [ref=f3e110]:
            - option "All statuses" [selected]
            - option "Complete"
            - option "Not taken"
          - button "Reset" [ref=f3e111] [cursor=pointer]
          - button "Export CSV" [ref=f3e112] [cursor=pointer]
          - generic [ref=f3e113]: 13 / 13 displayed-page rows
        - table [ref=f3e115]:
          - rowgroup [ref=f3e116]:
            - row [ref=f3e117]:
              - button "Course ↕" [ref=f3e118] [cursor=pointer]
              - button "Title ↕" [ref=f3e119] [cursor=pointer]
              - button "Minimum grade ↕" [ref=f3e120] [cursor=pointer]
              - button "Your grade ↕" [ref=f3e121] [cursor=pointer]
              - button "Status ↕" [ref=f3e122] [cursor=pointer]
          - rowgroup [ref=f3e123]:
            - row [ref=f3e124]:
              - cell "CS1001" [ref=f3e125]
              - cell "Introduction to Programming" [ref=f3e126]
              - cell "C" [ref=f3e127]
              - cell "B" [ref=f3e128]
              - cell "Complete" [ref=f3e129]
            - row [ref=f3e131]:
              - cell "CS1002" [ref=f3e132]
              - cell "Object-Oriented Programming with Java" [ref=f3e133]
              - cell "C" [ref=f3e134]
              - cell "—" [ref=f3e135]
              - cell "Not taken" [ref=f3e136]
            - row [ref=f3e137]:
              - cell "CS1003" [ref=f3e138]
              - cell "Data Structures" [ref=f3e139]
              - cell "C" [ref=f3e140]
              - cell "—" [ref=f3e141]
              - cell "Not taken" [ref=f3e142]
            - row [ref=f3e143]:
              - cell "CS1004" [ref=f3e144]
              - cell "Database Systems" [ref=f3e145]
              - cell "C" [ref=f3e146]
              - cell "—" [ref=f3e147]
              - cell "Not taken" [ref=f3e148]
            - row [ref=f3e149]:
              - cell "CS1005" [ref=f3e150]
              - cell "Computer Networks" [ref=f3e151]
              - cell "C" [ref=f3e152]
              - cell "—" [ref=f3e153]
              - cell "Not taken" [ref=f3e154]
            - row [ref=f3e155]:
              - cell "CS2006" [ref=f3e156]
              - cell "Software Engineering" [ref=f3e157]
              - cell "C" [ref=f3e158]
              - cell "—" [ref=f3e159]
              - cell "Not taken" [ref=f3e160]
            - row [ref=f3e161]:
              - cell "CS2007" [ref=f3e162]
              - cell "Introduction to Computing" [ref=f3e163]
              - cell "C" [ref=f3e164]
              - cell "—" [ref=f3e165]
              - cell "Not taken" [ref=f3e166]
            - row [ref=f3e167]:
              - cell "CS2009" [ref=f3e168]
              - cell "Computer Organization" [ref=f3e169]
              - cell "C" [ref=f3e170]
              - cell "—" [ref=f3e171]
              - cell "Not taken" [ref=f3e172]
            - row [ref=f3e173]:
              - cell "CS3010" [ref=f3e174]
              - cell "Operating Systems" [ref=f3e175]
              - cell "C" [ref=f3e176]
              - cell "—" [ref=f3e177]
              - cell "Not taken" [ref=f3e178]
            - row [ref=f3e179]:
              - cell "CS3011" [ref=f3e180]
              - cell "Algorithm Design" [ref=f3e181]
              - cell "C" [ref=f3e182]
              - cell "—" [ref=f3e183]
              - cell "Not taken" [ref=f3e184]
            - row [ref=f3e185]:
              - cell "CS3012" [ref=f3e186]
              - cell "Introduction to Cybersecurity" [ref=f3e187]
              - cell "C" [ref=f3e188]
              - cell "—" [ref=f3e189]
              - cell "Not taken" [ref=f3e190]
            - row [ref=f3e191]:
              - cell "CS4014" [ref=f3e192]
              - cell "Mobile Application Development" [ref=f3e193]
              - cell "C" [ref=f3e194]
              - cell "—" [ref=f3e195]
              - cell "Not taken" [ref=f3e196]
            - row [ref=f3e197]:
              - cell "CS4018" [ref=f3e198]
              - cell "Computer Science Capstone" [ref=f3e199]
              - cell "C" [ref=f3e200]
              - cell "—" [ref=f3e201]
              - cell "Not taken" [ref=f3e202]
      - generic [ref=f3e203]:
        - generic [ref=f3e204]:
          - generic [ref=f3e205]:
            - paragraph [ref=f3e206]: Major
            - heading "Accounting" [level=2] [ref=f3e207]
          - generic [ref=f3e208]: Primary
        - paragraph [ref=f3e209]:
          - strong [ref=f3e210]: 0 of 13 requirements met
          - generic [ref=f3e211]: 0% complete
        - generic [ref=f3e212]: 0 of 13 courses completed
        - generic [ref=f3e215]:
          - searchbox "Search displayed table rows" [ref=f3e216]
          - combobox "Filter displayed rows by status" [ref=f3e217]:
            - option "All statuses" [selected]
            - option "Not taken"
          - button "Reset" [ref=f3e218] [cursor=pointer]
          - button "Export CSV" [ref=f3e219] [cursor=pointer]
          - generic [ref=f3e220]: 13 / 13 displayed-page rows
        - table [ref=f3e222]:
          - rowgroup [ref=f3e223]:
            - row [ref=f3e224]:
              - button "Course ↕" [ref=f3e225] [cursor=pointer]
              - button "Title ↕" [ref=f3e226] [cursor=pointer]
              - button "Minimum grade ↕" [ref=f3e227] [cursor=pointer]
              - button "Your grade ↕" [ref=f3e228] [cursor=pointer]
              - button "Status ↕" [ref=f3e229] [cursor=pointer]
          - rowgroup [ref=f3e230]:
            - row [ref=f3e231]:
              - cell "BU1001" [ref=f3e232]
              - cell "Introduction to Business" [ref=f3e233]
              - cell "C" [ref=f3e234]
              - cell "—" [ref=f3e235]
              - cell "Not taken" [ref=f3e236]
            - row [ref=f3e237]:
              - cell "BU1002" [ref=f3e238]
              - cell "Financial Accounting" [ref=f3e239]
              - cell "C" [ref=f3e240]
              - cell "—" [ref=f3e241]
              - cell "Not taken" [ref=f3e242]
            - row [ref=f3e243]:
              - cell "BU1003" [ref=f3e244]
              - cell "Managerial Accounting" [ref=f3e245]
              - cell "C" [ref=f3e246]
              - cell "—" [ref=f3e247]
              - cell "Not taken" [ref=f3e248]
            - row [ref=f3e249]:
              - cell "BU1005" [ref=f3e250]
              - cell "Corporate Finance" [ref=f3e251]
              - cell "C" [ref=f3e252]
              - cell "—" [ref=f3e253]
              - cell "Not taken" [ref=f3e254]
            - row [ref=f3e255]:
              - cell "BU2006" [ref=f3e256]
              - cell "Organizational Behavior" [ref=f3e257]
              - cell "C" [ref=f3e258]
              - cell "—" [ref=f3e259]
              - cell "Not taken" [ref=f3e260]
            - row [ref=f3e261]:
              - cell "BU2007" [ref=f3e262]
              - cell "Business Communication" [ref=f3e263]
              - cell "C" [ref=f3e264]
              - cell "—" [ref=f3e265]
              - cell "Not taken" [ref=f3e266]
            - row [ref=f3e267]:
              - cell "BU2008" [ref=f3e268]
              - cell "Business Law" [ref=f3e269]
              - cell "C" [ref=f3e270]
              - cell "—" [ref=f3e271]
              - cell "Not taken" [ref=f3e272]
            - row [ref=f3e273]:
              - cell "BU2009" [ref=f3e274]
              - cell "Microeconomics for Business" [ref=f3e275]
              - cell "C" [ref=f3e276]
              - cell "—" [ref=f3e277]
              - cell "Not taken" [ref=f3e278]
            - row [ref=f3e279]:
              - cell "BU3011" [ref=f3e280]
              - cell "Business Statistics" [ref=f3e281]
              - cell "C" [ref=f3e282]
              - cell "—" [ref=f3e283]
              - cell "Not taken" [ref=f3e284]
            - row [ref=f3e285]:
              - cell "BU3013" [ref=f3e286]
              - cell "Operations Management" [ref=f3e287]
              - cell "C" [ref=f3e288]
              - cell "—" [ref=f3e289]
              - cell "Not taken" [ref=f3e290]
            - row [ref=f3e291]:
              - cell "BU4016" [ref=f3e292]
              - cell "Tax Accounting" [ref=f3e293]
              - cell "C" [ref=f3e294]
              - cell "—" [ref=f3e295]
              - cell "Not taken" [ref=f3e296]
            - row [ref=f3e297]:
              - cell "BU4017" [ref=f3e298]
              - cell "Auditing" [ref=f3e299]
              - cell "C" [ref=f3e300]
              - cell "—" [ref=f3e301]
              - cell "Not taken" [ref=f3e302]
            - row [ref=f3e303]:
              - cell "BU4018" [ref=f3e304]
              - cell "Business Capstone" [ref=f3e305]
              - cell "C" [ref=f3e306]
              - cell "—" [ref=f3e307]
              - cell "Not taken" [ref=f3e308]
```

# Test source

```ts
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
> 138 |     await expect(page.locator('.progress-bar, .progress-fill')).toBeVisible({ timeout: 5000 });
      |                                                                 ^ Error: expect(locator).toBeVisible() failed
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
```