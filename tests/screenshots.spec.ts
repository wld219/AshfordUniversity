import { test, expect } from '@playwright/test';

test.describe('Ashford University Portal - Visual Screenshots', () => {
  test('captures public landing page', async ({ page }) => {
    await page.goto('/');
    await page.screenshot({ path: 'tests/screenshots/public-landing.png', fullPage: true });
  });

  test('captures departments page', async ({ page }) => {
    await page.goto('/public/departments.php');
    await page.screenshot({ path: 'tests/screenshots/public-departments.png', fullPage: true });
  });

  test('captures master schedule page', async ({ page }) => {
    await page.goto('/public/schedule.php');
    await page.screenshot({ path: 'tests/screenshots/public-schedule.png', fullPage: true });
  });

  test('captures sign-in page', async ({ page }) => {
    await page.goto('/index.php');
    await page.screenshot({ path: 'tests/screenshots/sign-in.png', fullPage: true });
  });

  test('captures register page', async ({ page }) => {
    await page.goto('/register.php');
    await page.screenshot({ path: 'tests/screenshots/register.png', fullPage: true });
  });
});
