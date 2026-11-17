# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: interconnect.spec.ts >> Portal Page Count Verification >> verifies total page count across all portals
- Location: tests\interconnect.spec.ts:304:3

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: page.fill: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Page snapshot

```yaml
- generic [active] [ref=f16e1]:
  - banner [ref=f16e2]:
    - generic [ref=f16e3]:
      - generic [ref=f16e4]: "N"
      - generic [ref=f16e5]:
        - generic [ref=f16e6]: Ashford University
        - generic [ref=f16e7]: Student Portal
    - generic [ref=f16e8]:
      - generic [ref=f16e9]:
        - strong [ref=f16e10]: David Joseph
        - generic [ref=f16e11]: davidjoseph@ashford.edu
      - link "Logout" [ref=f16e12] [cursor=pointer]:
        - /url: /logout.php
  - generic [ref=f16e13]:
    - complementary [ref=f16e14]:
      - button "Collapse navigation" [expanded] [ref=f16e15] [cursor=pointer]: ☰
      - link "Overview" [ref=f16e16] [cursor=pointer]:
        - /url: dashboard.php
      - link "My Schedule" [ref=f16e24] [cursor=pointer]:
        - /url: schedule.php
      - link "Degree Audit" [ref=f16e31] [cursor=pointer]:
        - /url: degree-audit.php
      - link "Holds" [ref=f16e37] [cursor=pointer]:
        - /url: holds.php
      - link "My Advisor" [ref=f16e43] [cursor=pointer]:
        - /url: advisor.php
      - link "Majors & Minors" [ref=f16e49] [cursor=pointer]:
        - /url: programs.php
      - link "Catalog" [ref=f16e56] [cursor=pointer]:
        - /url: catalog.php
      - link "Buildings" [ref=f16e62] [cursor=pointer]:
        - /url: buildings.php
      - link "My Information" [ref=f16e68] [cursor=pointer]:
        - /url: information.php
    - main [ref=f16e75]:
      - heading [level=1] [ref=f16e76]
      - generic [ref=f16e77]:
        - generic [ref=f16e78]:
          - heading "Welcome, David Joseph" [level=2] [ref=f16e79]
          - paragraph [ref=f16e80]: Undergraduate · Fall 2026
        - generic [ref=f16e81]:
          - generic [ref=f16e82]: Academic Semester
          - combobox "Academic Semester" [ref=f16e83] [cursor=pointer]:
            - option "Fall 2026" [selected]
            - option "Spring 2027"
          - button "Confirm" [ref=f16e84] [cursor=pointer]
        - generic [ref=f16e85]:
          - heading "My Current Courses" [level=3] [ref=f16e86]
          - generic [ref=f16e87]:
            - generic [ref=f16e88]:
              - searchbox "Search displayed table rows" [ref=f16e89]
              - button "Reset" [ref=f16e90] [cursor=pointer]
              - button "Export CSV" [ref=f16e91] [cursor=pointer]
              - generic [ref=f16e92]: 3 / 3 displayed-page rows
            - table [ref=f16e93]:
              - rowgroup [ref=f16e94]:
                - row [ref=f16e95]:
                  - button "Course ↕" [ref=f16e96] [cursor=pointer]
                  - button "Course Title ↕" [ref=f16e97] [cursor=pointer]
                  - button "Faculty ↕" [ref=f16e98] [cursor=pointer]
                  - button "Schedule ↕" [ref=f16e99] [cursor=pointer]
                  - button "Credits ↕" [ref=f16e100] [cursor=pointer]
              - rowgroup [ref=f16e101]:
                - row [ref=f16e102]:
                  - cell "1101" [ref=f16e103]
                  - cell "Introduction to Programming" [ref=f16e104]
                  - cell "Darius Harris" [ref=f16e105]
                  - cell "Mon, Wed 08:00-09:15" [ref=f16e106]
                  - cell "4" [ref=f16e107]
                - row [ref=f16e108]:
                  - cell "1107" [ref=f16e109]
                  - cell "Introduction to Computing" [ref=f16e110]
                  - cell "Joshua Scott" [ref=f16e111]
                  - cell "Mon, Wed 09:30-10:45" [ref=f16e112]
                  - cell "4" [ref=f16e113]
                - row [ref=f16e114]:
                  - cell "2101" [ref=f16e115]
                  - cell "Introduction to Business" [ref=f16e116]
                  - cell "Isabella Mitchell" [ref=f16e117]
                  - cell "Mon, Wed 11:00-12:15" [ref=f16e118]
                  - cell "4" [ref=f16e119]
          - paragraph [ref=f16e120]:
            - link "View My Schedule →" [ref=f16e121] [cursor=pointer]:
              - /url: schedule.php
        - generic [ref=f16e122]:
          - generic [ref=f16e123]:
            - heading "Academic Summary" [level=3] [ref=f16e124]
            - generic [ref=f16e125]:
              - generic [ref=f16e126]: Academic Level
              - strong [ref=f16e127]: Undergraduate
            - generic [ref=f16e128]:
              - generic [ref=f16e129]: Cumulative GPA
              - strong [ref=f16e130]: "3.57"
            - generic [ref=f16e131]:
              - generic [ref=f16e132]: Enrolled Credits
              - strong [ref=f16e133]: "12"
            - generic [ref=f16e134]:
              - generic [ref=f16e135]: Major
              - strong [ref=f16e136]: Cybersecurity
            - generic [ref=f16e137]:
              - generic [ref=f16e138]: Minor
              - strong [ref=f16e139]: Business Administration
          - generic [ref=f16e140]:
            - heading "Student Information" [level=3] [ref=f16e141]
            - generic [ref=f16e142]:
              - generic [ref=f16e143]: Student ID
              - strong [ref=f16e144]: "100002"
            - generic [ref=f16e145]:
              - generic [ref=f16e146]: Account Status
              - strong [ref=f16e147]: Logged in
            - generic [ref=f16e148]:
              - generic [ref=f16e149]: Academic Advisor
              - strong [ref=f16e150]: Zoe Walker
            - generic [ref=f16e151]:
              - generic [ref=f16e152]: Registration Holds
              - strong [ref=f16e153]: "0"
        - generic [ref=f16e154]:
          - heading "Degree Progress" [level=3] [ref=f16e155]
          - generic [ref=f16e156]:
            - generic [ref=f16e157]: Credits Earned
            - strong [ref=f16e158]: 12 / 120
          - paragraph [ref=f16e161]: 10% complete toward graduation (120 credits required)
        - generic [ref=f16e162]:
          - heading "Quick Actions" [level=3] [ref=f16e163]
          - generic [ref=f16e164]:
            - link "📅 View My Schedule" [ref=f16e165] [cursor=pointer]:
              - /url: schedule.php
              - generic [ref=f16e166]: 📅
              - generic [ref=f16e167]: View My Schedule
            - link "📋 Degree Audit" [ref=f16e168] [cursor=pointer]:
              - /url: degree-audit.php
              - generic [ref=f16e169]: 📋
              - generic [ref=f16e170]: Degree Audit
            - link "⚠️ Check Holds" [ref=f16e171] [cursor=pointer]:
              - /url: holds.php
              - generic [ref=f16e172]: ⚠️
              - generic [ref=f16e173]: Check Holds
            - link "👨‍🏫 Contact Advisor" [ref=f16e174] [cursor=pointer]:
              - /url: advisor.php
              - generic [ref=f16e175]: 👨‍🏫
              - generic [ref=f16e176]: Contact Advisor
```

# Test source

```ts
  1  | import { test as base, expect } from '@playwright/test';
  2  | 
  3  | // Test credentials — using accounts that do NOT trigger 2FA
  4  | // 2FA is triggered for: liamwalker@ashford.edu (Student) and user_ID 300008 (Admin)
  5  | const CREDENTIALS = {
  6  |   Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  7  |   Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  8  |   Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  9  |   StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
  10 | };
  11 | 
  12 | // Role home URLs
  13 | const ROLE_HOMES = {
  14 |   Student: '/student/dashboard.php',
  15 |   Faculty: '/faculty/dashboard.php',
  16 |   Admin: '/admin/dashboard.php',
  17 |   StatStaff: '/statstaff/dashboard.php',
  18 | };
  19 | 
  20 | type Role = keyof typeof CREDENTIALS;
  21 | 
  22 | const test = base.extend<{ role: Role }>({
  23 |   role: [undefined, { option: true }],
  24 | });
  25 | 
  26 | export const loginAs = async (page: any, role: Role) => {
  27 |   const creds = CREDENTIALS[role];
  28 |   await page.goto('/index.php');
> 29 |   await page.fill('input[name="email"]', creds.email);
     |              ^ Error: page.fill: Test timeout of 30000ms exceeded.
  30 |   await page.fill('input[name="password"]', creds.password);
  31 |   await page.selectOption('select[name="user_type"]', role);
  32 |   await page.click('button[type="submit"]');
  33 |   await expect(page).toHaveURL(ROLE_HOMES[role]);
  34 | };
  35 | 
  36 | export { test, expect, CREDENTIALS, ROLE_HOMES };
  37 | 
```