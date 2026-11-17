import { test, expect } from './auth-helper';

test.describe('Admin Portal', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('dashboard loads after admin login', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await expect(page).toHaveURL('/admin/dashboard.php');
  });

  test('users page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/users.php');
    await expect(page).toHaveURL('/admin/users.php');
  });

  test('academic page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/academic.php');
    await expect(page).toHaveURL('/admin/academic.php');
  });

  test('sections page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/sections.php');
    await expect(page).toHaveURL('/admin/sections.php');
  });

  test('semesters page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/semesters.php');
    await expect(page).toHaveURL('/admin/semesters.php');
  });

  test('advisors page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/advisors.php');
    await expect(page).toHaveURL('/admin/advisors.php');
  });

  test('holds page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/holds.php');
    await expect(page).toHaveURL('/admin/holds.php');
  });

  test('exceptions page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/exceptions.php');
    await expect(page).toHaveURL('/admin/exceptions.php');
  });

  test('degree audit page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/degree-audit.php');
    await expect(page).toHaveURL('/admin/degree-audit.php');
  });

  test('master schedule page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/master-schedule.php');
    await expect(page).toHaveURL('/admin/master-schedule.php');
  });

  test('audit page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/audit.php');
    await expect(page).toHaveURL('/admin/audit.php');
  });
});
