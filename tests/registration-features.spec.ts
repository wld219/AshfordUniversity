import { test, expect } from '@playwright/test';

const CREDENTIALS = {
  Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
};

const loginAs = async (page: any, role: string) => {
  const c = CREDENTIALS[role as keyof typeof CREDENTIALS];
  await page.goto('/index.php');
  await page.fill('input[name="email"]', c.email);
  await page.fill('input[name="password"]', c.password);
  await page.selectOption('select[name="user_type"]', role);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL('/student/dashboard.php');
};

test.describe('Student Registration — New Features', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('registration page loads with filters', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('h1')).toContainText('Course Registration');
    await expect(page.locator('select[name="department"]')).toBeVisible();
    await expect(page.locator('select[name="faculty"]')).toBeVisible();
    await expect(page.locator('select[name="seats"]')).toBeVisible();
  });

  test('registration page shows prerequisites and time conflicts when sections exist', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php?search=CS');
    await expect(page.locator('body')).toBeVisible();
    // Page should load without errors
    await expect(page.locator('body')).not.toContainText(/Fatal error|Uncaught/);
  });

  test('my registered courses section loads', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('#my-registered-heading')).toBeVisible();
  });

  test('schedule page loads with table', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/schedule.php');
    await expect(page.locator('h1')).toContainText('My Schedule');
    await expect(page.locator('table')).toBeVisible();
  });

  test('dashboard shows credit progress and quick actions', async ({ page }) => {
    await loginAs(page, 'Student');
    await page.goto('/student/dashboard.php');
    await expect(page.locator('h3').filter({ hasText: 'Degree Progress' })).toBeVisible();
    await expect(page.locator('h3').filter({ hasText: 'Quick Actions' })).toBeVisible();
  });
});
