# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> ADMIN PORTAL >> admin holds has resolution workflow
- Location: tests/comprehensive.spec.ts:259:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('button[type="button"]').filter({ hasText: 'Resolve' })
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" locator('button[type="button"]').filter({ hasText: 'Resolve' }) with timeout 5000ms
  - waiting for locator('button[type="button"]').filter({ hasText: 'Resolve' })

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
  - heading "Student Holds" [level=1]
  - paragraph: Registration
  - heading "Student holds" [level=2]
  - text: Read-Only
  - paragraph: A hold blocks course registration until it is removed or resolved.
  - searchbox "Search displayed table rows"
  - combobox "Filter displayed rows by status":
    - option "All statuses" [selected]
    - option "Active"
  - button "Reset"
  - button "Export CSV"
  - text: 81 / 81 displayed-page rows
  - table:
    - rowgroup:
      - row "Student ID ↕ Student ↕ Hold type ↕ Reason ↕ Date ↕ Expires ↕ Status ↕":
        - button "Student ID ↕"
        - button "Student ↕"
        - button "Hold type ↕"
        - button "Reason ↕"
        - button "Date ↕"
        - button "Expires ↕"
        - button "Status ↕"
      - row "100001 Liam Walker Financial — 2026-11-03 — Active":
        - cell "100001"
        - cell "Liam Walker"
        - cell "Financial"
        - cell "—"
        - cell "2026-11-03"
        - cell "—"
        - cell "Active"
      - row "101120 Marcus Carter Academic — 2026-08-23 — Active":
        - cell "101120"
        - cell "Marcus Carter"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-23"
        - cell "—"
        - cell "Active"
      - row "100560 Naomi Johnson Academic — 2026-08-23 — Active":
        - cell "100560"
        - cell "Naomi Johnson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-23"
        - cell "—"
        - cell "Active"
      - row "101400 Michael Ramirez Academic — 2026-08-23 — Active":
        - cell "101400"
        - cell "Michael Ramirez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-23"
        - cell "—"
        - cell "Active"
      - row "100280 Leah Robinson Academic — 2026-08-23 — Active":
        - cell "100280"
        - cell "Leah Robinson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-23"
        - cell "—"
        - cell "Active"
      - row "100840 Mia Rodriguez Academic — 2026-08-23 — Active":
        - cell "100840"
        - cell "Mia Rodriguez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-23"
        - cell "—"
        - cell "Active"
      - row "100540 Noah Brown Health — 2026-08-22 — Active":
        - cell "100540"
        - cell "Noah Brown"
        - cell "Health"
        - cell "—"
        - cell "2026-08-22"
        - cell "—"
        - cell "Active"
      - row "100260 Nicholas Jean Financial — 2026-08-22 — Active":
        - cell "100260"
        - cell "Nicholas Jean"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-22"
        - cell "—"
        - cell "Active"
      - row "100820 Gabriella Lopez Financial — 2026-08-22 — Active":
        - cell "100820"
        - cell "Gabriella Lopez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-22"
        - cell "—"
        - cell "Active"
      - row "101380 Camila Ramirez Health — 2026-08-22 — Active":
        - cell "101380"
        - cell "Camila Ramirez"
        - cell "Health"
        - cell "—"
        - cell "2026-08-22"
        - cell "—"
        - cell "Active"
      - row "101100 Kevin Rodriguez Financial — 2026-08-22 — Active":
        - cell "101100"
        - cell "Kevin Rodriguez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-22"
        - cell "—"
        - cell "Active"
      - row "100520 Victoria Desir Academic — 2026-08-21 — Active":
        - cell "100520"
        - cell "Victoria Desir"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-21"
        - cell "—"
        - cell "Active"
      - row "101360 Kayla Jackson Academic — 2026-08-21 — Active":
        - cell "101360"
        - cell "Kayla Jackson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-21"
        - cell "—"
        - cell "Active"
      - row "101080 Lauren King Academic — 2026-08-21 — Active":
        - cell "101080"
        - cell "Lauren King"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-21"
        - cell "—"
        - cell "Active"
      - row "100800 Zoe Robinson Academic — 2026-08-21 — Active":
        - cell "100800"
        - cell "Zoe Robinson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-21"
        - cell "—"
        - cell "Active"
      - row "100240 Naomi Sanchez Academic — 2026-08-21 — Active":
        - cell "100240"
        - cell "Naomi Sanchez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-21"
        - cell "—"
        - cell "Active"
      - row "100500 Christian Flores Financial — 2026-08-20 — Active":
        - cell "100500"
        - cell "Christian Flores"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-20"
        - cell "—"
        - cell "Active"
      - row "101340 Daniel Hall Financial — 2026-08-20 — Active":
        - cell "101340"
        - cell "Daniel Hall"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-20"
        - cell "—"
        - cell "Active"
      - row "100220 Jasmine Lewis Financial — 2026-08-20 — Active":
        - cell "100220"
        - cell "Jasmine Lewis"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-20"
        - cell "—"
        - cell "Active"
      - row "101060 Camila Rivera Financial — 2026-08-20 — Active":
        - cell "101060"
        - cell "Camila Rivera"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-20"
        - cell "—"
        - cell "Active"
      - row "100780 David Thomas Health — 2026-08-20 — Active":
        - cell "100780"
        - cell "David Thomas"
        - cell "Health"
        - cell "—"
        - cell "2026-08-20"
        - cell "—"
        - cell "Active"
      - row "101320 Andrew Carter Academic — 2026-08-19 — Active":
        - cell "101320"
        - cell "Andrew Carter"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "101040 Samantha Clark Academic — 2026-08-19 — Active":
        - cell "101040"
        - cell "Samantha Clark"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "100480 Vanessa Etienne Academic — 2026-08-19 — Active":
        - cell "100480"
        - cell "Vanessa Etienne"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "100200 Kayla Martin Academic — 2026-08-19 — Active":
        - cell "100200"
        - cell "Kayla Martin"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "101600 Zoe Patel Academic — 2026-08-19 — Active":
        - cell "101600"
        - cell "Zoe Patel"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "100760 Kiara Rodriguez Academic — 2026-08-19 — Active":
        - cell "100760"
        - cell "Kiara Rodriguez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-19"
        - cell "—"
        - cell "Active"
      - row "101300 Victoria Gonzalez Financial — 2026-08-18 — Active":
        - cell "101300"
        - cell "Victoria Gonzalez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "100180 Gabriel Jones Health — 2026-08-18 — Active":
        - cell "100180"
        - cell "Gabriel Jones"
        - cell "Health"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "101580 Zoe Kim Financial — 2026-08-18 — Active":
        - cell "101580"
        - cell "Zoe Kim"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "100460 Amanda Lewis Financial — 2026-08-18 — Active":
        - cell "100460"
        - cell "Amanda Lewis"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "100740 Ethan Ramirez Financial — 2026-08-18 — Active":
        - cell "100740"
        - cell "Ethan Ramirez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "101020 Ethan Thomas Health — 2026-08-18 — Active":
        - cell "101020"
        - cell "Ethan Thomas"
        - cell "Health"
        - cell "—"
        - cell "2026-08-18"
        - cell "—"
        - cell "Active"
      - row "101000 Liam Anderson Academic — 2026-08-17 — Active":
        - cell "101000"
        - cell "Liam Anderson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "100440 Grace Desir Academic — 2026-08-17 — Active":
        - cell "100440"
        - cell "Grace Desir"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "101560 Zoe Grant Academic — 2026-08-17 — Active":
        - cell "101560"
        - cell "Zoe Grant"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "101280 Jordan Moore Academic — 2026-08-17 — Active":
        - cell "101280"
        - cell "Jordan Moore"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "100720 Jason Robinson Academic — 2026-08-17 — Active":
        - cell "100720"
        - cell "Jason Robinson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "100160 Ava White Academic — 2026-08-17 — Active":
        - cell "100160"
        - cell "Ava White"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-17"
        - cell "—"
        - cell "Active"
      - row "101260 Christopher Altidor Health — 2026-08-16 — Active":
        - cell "101260"
        - cell "Christopher Altidor"
        - cell "Health"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "100140 Brianna Carter Financial — 2026-08-16 — Active":
        - cell "100140"
        - cell "Brianna Carter"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "101540 Zoe Ellis Financial — 2026-08-16 — Active":
        - cell "101540"
        - cell "Zoe Ellis"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "100700 Camila Thompson Financial — 2026-08-16 — Active":
        - cell "100700"
        - cell "Camila Thompson"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "100980 Sophia Walker Financial — 2026-08-16 — Active":
        - cell "100980"
        - cell "Sophia Walker"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "100420 Rachel White Health — 2026-08-16 — Active":
        - cell "100420"
        - cell "Rachel White"
        - cell "Health"
        - cell "—"
        - cell "2026-08-16"
        - cell "—"
        - cell "Active"
      - row "100680 Jessica Campbell Academic — 2026-08-15 — Active":
        - cell "100680"
        - cell "Jessica Campbell"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "101520 Zoe Carter Academic — 2026-08-15 — Active":
        - cell "101520"
        - cell "Zoe Carter"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "100400 Isabella Davis Academic — 2026-08-15 — Active":
        - cell "100400"
        - cell "Isabella Davis"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "100120 Ethan Gonzalez Academic — 2026-08-15 — Active":
        - cell "100120"
        - cell "Ethan Gonzalez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "101240 Ryan Green Academic — 2026-08-15 — Active":
        - cell "101240"
        - cell "Ryan Green"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "100960 Maria Walker Academic — 2026-08-15 — Active":
        - cell "100960"
        - cell "Maria Walker"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-15"
        - cell "—"
        - cell "Active"
      - row "100660 Samuel Baker Health — 2026-08-14 — Active":
        - cell "100660"
        - cell "Samuel Baker"
        - cell "Health"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "101220 Brandon Hall Financial — 2026-08-14 — Active":
        - cell "101220"
        - cell "Brandon Hall"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "100380 Brittany Hernandez Financial — 2026-08-14 — Active":
        - cell "100380"
        - cell "Brittany Hernandez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "101500 Nathan Joseph Health — 2026-08-14 — Active":
        - cell "101500"
        - cell "Nathan Joseph"
        - cell "Health"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "100940 Maya Pierre Financial — 2026-08-14 — Active":
        - cell "100940"
        - cell "Maya Pierre"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "100100 Casey Rodriguez Financial — 2026-08-14 — Active":
        - cell "100100"
        - cell "Casey Rodriguez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-14"
        - cell "—"
        - cell "Active"
      - row "100080 Lauren Altidor Academic — 2026-08-13 — Active":
        - cell "100080"
        - cell "Lauren Altidor"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "101200 Camila Harris Academic — 2026-08-13 — Active":
        - cell "101200"
        - cell "Camila Harris"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "100360 Kevin Louis Academic — 2026-08-13 — Active":
        - cell "100360"
        - cell "Kevin Louis"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "101480 Sophia Martin Academic — 2026-08-13 — Active":
        - cell "101480"
        - cell "Sophia Martin"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "100920 Madison Martinez Academic — 2026-08-13 — Active":
        - cell "100920"
        - cell "Madison Martinez"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "100640 Cameron Nelson Academic — 2026-08-13 — Active":
        - cell "100640"
        - cell "Cameron Nelson"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-13"
        - cell "—"
        - cell "Active"
      - row "101460 Mia Augustin Financial — 2026-08-12 — Active":
        - cell "101460"
        - cell "Mia Augustin"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "101180 Natalie Brown Financial — 2026-08-12 — Active":
        - cell "101180"
        - cell "Natalie Brown"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "100620 Daniel Hill Financial — 2026-08-12 — Active":
        - cell "100620"
        - cell "Daniel Hill"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "100900 Liam Pierre Health — 2026-08-12 — Active":
        - cell "100900"
        - cell "Liam Pierre"
        - cell "Health"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "100340 Lauren Thomas Financial — 2026-08-12 — Active":
        - cell "100340"
        - cell "Lauren Thomas"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "100060 Liam Thompson Health — 2026-08-12 — Active":
        - cell "100060"
        - cell "Liam Thompson"
        - cell "Health"
        - cell "—"
        - cell "2026-08-12"
        - cell "—"
        - cell "Active"
      - row "100040 Kayla Baptiste Academic — 2026-08-11 — Active":
        - cell "100040"
        - cell "Kayla Baptiste"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "101440 Mason Charles Academic — 2026-08-11 — Active":
        - cell "101440"
        - cell "Mason Charles"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "101160 Natalie Lee Academic — 2026-08-11 — Active":
        - cell "101160"
        - cell "Natalie Lee"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "100320 Mia Louis Academic — 2026-08-11 — Active":
        - cell "100320"
        - cell "Mia Louis"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "100600 Adrian Roberts Academic — 2026-08-11 — Active":
        - cell "100600"
        - cell "Adrian Roberts"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "100880 Isaiah Scott Academic — 2026-08-11 — Active":
        - cell "100880"
        - cell "Isaiah Scott"
        - cell "Academic"
        - cell "—"
        - cell "2026-08-11"
        - cell "—"
        - cell "Active"
      - row "101140 Mia Garcia Health — 2026-08-10 — Active":
        - cell "101140"
        - cell "Mia Garcia"
        - cell "Health"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
      - row "100580 Liam Gonzalez Financial — 2026-08-10 — Active":
        - cell "100580"
        - cell "Liam Gonzalez"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
      - row "101420 Brianna Johnson Financial — 2026-08-10 — Active":
        - cell "101420"
        - cell "Brianna Johnson"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
      - row "100860 Nicole Martin Financial — 2026-08-10 — Active":
        - cell "100860"
        - cell "Nicole Martin"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
      - row "100300 Olivia Thomas Health — 2026-08-10 — Active":
        - cell "100300"
        - cell "Olivia Thomas"
        - cell "Health"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
      - row "100020 Jordan Thompson Financial — 2026-08-10 — Active":
        - cell "100020"
        - cell "Jordan Thompson"
        - cell "Financial"
        - cell "—"
        - cell "2026-08-10"
        - cell "—"
        - cell "Active"
  - paragraph: Showing the 200 most recent holds.
```

# Test source

```ts
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
  255 |     await expect(page.locator('#users-bulk-form, #users-bulk-submit')).toBeVisible({ timeout: 5000 });
  256 |     await expect(page.locator('#users-export-csv')).toBeVisible();
  257 |   });
  258 | 
  259 |   test('admin holds has resolution workflow', async ({ context }) => {
  260 |     const page = await loginFresh(context, 'Admin');
  261 |     await page.goto('/admin/holds.php');
> 262 |     await expect(page.locator('button[type="button"]').filter({ hasText: 'Resolve' })).toBeVisible({ timeout: 5000 });
      |                                                                                        ^ Error: expect(locator).toBeVisible() failed
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