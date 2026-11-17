import { test, expect } from './auth-helper';

test.describe('Faculty Portal', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('dashboard loads after faculty login', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await expect(page).toHaveURL('/faculty/dashboard.php');
  });

  test('schedule page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/schedule.php');
    await expect(page).toHaveURL('/faculty/schedule.php');
  });

  test('master schedule page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/master-schedule.php');
    await expect(page).toHaveURL('/faculty/master-schedule.php');
  });

  test('rosters page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/rosters.php');
    await expect(page).toHaveURL('/faculty/rosters.php');
  });

  test('attendance calendar page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/attendance-calendar.php');
    await expect(page).toHaveURL('/faculty/attendance-calendar.php');
  });

  test('attendance history page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/attendance-history.php');
    await expect(page).toHaveURL('/faculty/attendance-history.php');
  });

  test('advisees page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/advisees.php');
    await expect(page).toHaveURL('/faculty/advisees.php');
  });

  test('catalog page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/catalog.php');
    await expect(page).toHaveURL('/faculty/catalog.php');
  });

  test('profile page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/profile.php');
    await expect(page).toHaveURL('/faculty/profile.php');
  });
});
