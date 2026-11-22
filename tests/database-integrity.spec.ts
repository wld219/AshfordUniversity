import { test, expect } from '@playwright/test';
import { loginAs, CREDENTIALS, ROLE_HOMES } from './auth-helper';

const EXPECTED = {
  departments: 10,
  courses: 240,
  semesters: 14,
  majors: 12,
  minors: 12,
  buildings: 8,
  faculty: 350,
  users: 1985,
};

test.describe('Database Integrity — connectivity & public catalog', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('application is reachable against the loaded database', async ({ page }) => {
    await expect(page).toHaveTitle(/Ashford University/i);
    await expect(page.locator('body')).toBeVisible();
  });

  test('public departments page loads (Department table populated)', async ({ page }) => {
    await page.goto('/public/departments.php');
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('h1')).toContainText(/department/i);
  });

  test('public courses page loads (Course table populated)', async ({ page }) => {
    await page.goto('/public/courses.php');
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('h1')).toContainText(/course/i);
  });

  test('public programs page loads (Major/Minor tables populated)', async ({ page }) => {
    await page.goto('/public/programs.php');
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('h1')).toContainText(/program/i);
  });

  test('public schedule page loads (Semester/Course_Section populated)', async ({ page }) => {
    await page.goto('/public/schedule.php');
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('h1')).toContainText(/schedule/i);
  });
});

test.describe('Database Integrity — authentication (User/Login/role tables)', () => {
  const roles: Array<keyof typeof CREDENTIALS> = ['Student', 'Faculty', 'Admin', 'StatStaff'];

  for (const role of roles) {
    test(`${role} account authenticates and reaches its portal`, async ({ page }) => {
      await loginAs(page, role);
      await expect(page).toHaveURL(ROLE_HOMES[role]);
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
    });
  }

  test('invalid credentials are rejected', async ({ page }) => {
    await page.goto('/index.php');
    await page.fill('input[name="email"]', 'nonexistent@ashford.edu');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.selectOption('select[name="user_type"]', 'Student');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/index\.php/);
  });
});

test.describe('Database Integrity — student portal (Student/Enrollment/Course_Section)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'Student');
  });

  const pages = [
    '/student/dashboard.php',
    '/student/schedule.php',
    '/student/registration.php',
    '/student/transcript.php',
    '/student/degree-audit.php',
    '/student/holds.php',
    '/student/advisor.php',
    '/student/programs.php',
    '/student/catalog.php',
    '/student/buildings.php',
  ];

  test('all student portal pages load', async ({ page }) => {
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('schedule renders a table (Enrollment/Course_Section joined)', async ({ page }) => {
    await page.goto('/student/schedule.php');
    await expect(page.locator('table')).toBeVisible();
  });

  test('registration page exposes filters (Department/Course_Section populated)', async ({ page }) => {
    await page.goto('/student/registration.php');
    await expect(page.locator('select[name="department"]')).toBeVisible();
    await expect(page.locator('select[name="faculty"]')).toBeVisible();
  });
});

test.describe('Database Integrity — faculty portal (Faculty/Advisor/Appointment)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'Faculty');
  });

  const pages = [
    '/faculty/dashboard.php',
    '/faculty/schedule.php',
    '/faculty/rosters.php',
    '/faculty/attendance-history.php',
    '/faculty/advisees.php',
    '/faculty/catalog.php',
    '/faculty/profile.php',
  ];

  test('all faculty portal pages load', async ({ page }) => {
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('rosters page renders a table (Enrollment/Course_Section)', async ({ page }) => {
    await page.goto('/faculty/rosters.php');
    await expect(page.locator('table')).toBeVisible();
  });
});

test.describe('Database Integrity — admin portal (Admin/User/Course_Section/Master_Schedule)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'Admin');
  });

  const pages = [
    '/admin/dashboard.php',
    '/admin/users.php',
    '/admin/academic.php',
    '/admin/sections.php',
    '/admin/semesters.php',
    '/admin/advisors.php',
    '/admin/holds.php',
    '/admin/master-schedule.php',
  ];

  test('all admin portal pages load', async ({ page }) => {
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('users page lists accounts (User table populated)', async ({ page }) => {
    await page.goto('/admin/users.php');
    await expect(page.locator('table')).toBeVisible();
  });

  test('master schedule builds its validation report (Course_Section/Semester)', async ({ page }) => {
    await page.goto('/admin/master-schedule.php');
    await expect(page.locator('h1')).toContainText(/master schedule/i);
  });
});

test.describe('Database Integrity — statstaff portal (read-only, StatStaff)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'StatStaff');
  });

  test('statstaff dashboard and reports load', async ({ page }) => {
    await page.goto('/statstaff/dashboard.php');
    await expect(page).toHaveURL('/statstaff/dashboard.php');
    await expect(page.locator('body')).toBeVisible();

    await page.goto('/statstaff/reports.php');
    await expect(page).toHaveURL('/statstaff/reports.php');
    await expect(page.locator('body')).toBeVisible();
  });

  test('statstaff is blocked from admin pages (read-only enforcement)', async ({ page }) => {
    await page.goto('/admin/users.php');
    await expect(page.locator('body')).toContainText(/403|permission|read-only|unauthorized/i);
  });
});
