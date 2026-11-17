import { test, expect } from './auth-helper';

test.describe('Student Portal', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('dashboard loads after student login', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await expect(page).toHaveURL('/student/dashboard.php');
  });

  test('registration page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('h1')).toContainText('Course Registration');
  });

  test('schedule page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/schedule.php');
    await expect(page.locator('h1')).toContainText('My Schedule');
  });

  test('degree audit page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/degree-audit.php');
    await expect(page.locator('h1')).toContainText('Degree Audit');
  });

  test('transcript page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/transcript.php');
    await expect(page).toHaveURL('/student/transcript.php');
  });

  test('holds page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/holds.php');
    await expect(page).toHaveURL('/student/holds.php');
  });

  test('advisor page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/advisor.php');
    await expect(page).toHaveURL('/student/advisor.php');
  });

  test('programs page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/programs.php');
    await expect(page).toHaveURL('/student/programs.php');
  });

  test('catalog page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/catalog.php');
    await expect(page).toHaveURL('/student/catalog.php');
  });

  test('buildings page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/buildings.php');
    await expect(page).toHaveURL('/student/buildings.php');
  });

  test('information page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/information.php');
    await expect(page).toHaveURL('/student/information.php');
  });

  test('master schedule page loads', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/master-schedule.php');
    await expect(page).toHaveURL('/student/master-schedule.php');
  });
});
