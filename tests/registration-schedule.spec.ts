import { test, expect } from './auth-helper';

test.describe('Course Registration (Student)', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('shows registration window status', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('body')).toContainText(/registration|open|closed|window/i);
  });

  test('displays course sections with filters', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('select[name="department"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('select[name="faculty"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('select[name="seats"]')).toBeVisible({ timeout: 5000 });
  });

  test('shows registered courses count and credit limit', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/dashboard.php');
    await expect(page.locator('body')).toContainText(/credit|registered/i);
  });
});

test.describe('Master Schedule (Admin)', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('shows validation statistics', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/master-schedule.php');
    await expect(page.locator('body')).toContainText(/section|scheduled|validation|conflict/i);
  });

  test('master schedule page is accessible', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/master-schedule.php');
    await expect(page.locator('h1')).toContainText('Master Schedule');
  });
});
