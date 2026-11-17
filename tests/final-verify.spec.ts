import { test, expect } from '@playwright/test';

const CREDENTIALS = {
  Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
};

const ROLE_HOMES: Record<string, string> = {
  Student: '/student/dashboard.php',
  Faculty: '/faculty/dashboard.php',
  Admin: '/admin/dashboard.php',
  StatStaff: '/statstaff/dashboard.php',
};

const loginAs = async (page: any, role: string) => {
  const c = CREDENTIALS[role as keyof typeof CREDENTIALS];
  await page.goto('/index.php');
  await page.fill('input[name="email"]', c.email);
  await page.fill('input[name="password"]', c.password);
  await page.selectOption('select[name="user_type"]', role);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(ROLE_HOMES[role]);
};

// ==================== PUBLIC SITE ====================
test.describe('Public Site', () => {
  test('landing page loads', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Ashford University/);
    await expect(page.locator('a[href="#overview"]')).toBeVisible();
    await expect(page.locator('a[href="#academics"]')).toBeVisible();
    await expect(page.locator('a[href="#admissions"]')).toBeVisible();
    await expect(page.locator('a[href="#schedule"]')).toBeVisible();
  });

  test('all public pages load', async ({ page }) => {
    for (const url of ['/public/departments.php', '/public/courses.php', '/public/programs.php', '/public/schedule.php']) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });
});

// ==================== AUTH ====================
test.describe('Authentication', () => {
  for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff']) {
    test(`${role} login works`, async ({ page }) => {
      await loginAs(page, role);
      await expect(page).toHaveURL(ROLE_HOMES[role]);
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
    });
  }

  test('logout works', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.click('a[href*="logout.php"]');
    await expect(page).toHaveURL(/index\.php/);
  });
});

// ==================== ALL PAGES LOAD ====================
test.describe('All Portal Pages Load', () => {
  const studentPages = [
    '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
    '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
    '/student/holds.php', '/student/advisor.php', '/student/programs.php',
    '/student/catalog.php', '/student/buildings.php', '/student/information.php',
    '/student/advisor_appointment.php',
  ];

  const facultyPages = [
    '/faculty/dashboard.php', '/faculty/schedule.php', '/faculty/master-schedule.php',
    '/faculty/rosters.php', '/faculty/attendance-calendar.php', '/faculty/attendance-history.php',
    '/faculty/attendance-weekly.php', '/faculty/attendance-time-rules.php', '/faculty/attendance-window.php',
    '/faculty/advisees.php', '/faculty/catalog.php', '/faculty/profile.php',
  ];

  const adminPages = [
    '/admin/dashboard.php', '/admin/users.php', '/admin/academic.php', '/admin/sections.php',
    '/admin/semesters.php', '/admin/advisors.php', '/admin/holds.php', '/admin/exceptions.php',
    '/admin/degree-audit.php', '/admin/master-schedule.php', '/admin/audit.php',
  ];

  const statstaffPages = [
    '/statstaff/dashboard.php', '/statstaff/reports.php',
  ];

  test('all 13 student pages load', async ({ page }) => {
    await loginAs(page, 'Student');
    for (const url of studentPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('all 12 faculty pages load', async ({ page }) => {
    await loginAs(page, 'Faculty');
    for (const url of facultyPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('all 11 admin pages load', async ({ page }) => {
    await loginAs(page, 'Admin');
    for (const url of adminPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('all 2 statstaff pages load', async ({ page }) => {
    await loginAs(page, 'StatStaff');
    for (const url of statstaffPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });
});

// ==================== KEY FUNCTIONALITY ====================
test.describe('Key Functionality', () => {
  test('registration page has filters', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('select[name="department"]')).toBeVisible();
    await expect(page.locator('select[name="faculty"]')).toBeVisible();
    await expect(page.locator('select[name="seats"]')).toBeVisible();
  });

  test('schedule page has table', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/schedule.php');
    await expect(page.locator('table')).toBeVisible();
  });

  test('master schedule has validation report', async ({ page }) => {
    await loginAs(page, 'Admin');
    await page.goto('/admin/master-schedule.php');
    await expect(page.locator('h1')).toContainText('Master Schedule');
  });

  test('statstaff read-only enforcement', async ({ page }) => {
    await loginAs(page, 'StatStaff');
    await page.goto('/admin/users.php');
    await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
  });
});
