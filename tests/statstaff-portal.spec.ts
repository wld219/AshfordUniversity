import { test, expect } from './auth-helper';

test.describe('StatStaff Portal', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('dashboard loads after statstaff login', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'StatStaff');
    await expect(page).toHaveURL('/statstaff/dashboard.php');
  });

  test('reports page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'StatStaff');
    await page.goto('/statstaff/reports.php');
    await expect(page).toHaveURL('/statstaff/reports.php');
  });

  test('statstaff cannot access admin write pages', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'StatStaff');
    await page.goto('/admin/users.php');
    await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
  });
});
