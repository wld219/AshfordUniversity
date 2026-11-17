import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/index.php');
  });

  test('displays login form with all required fields', async ({ page }) => {
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('select[name="user_type"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('shows role selector with Student, Faculty, Admin, StatStaff options', async ({ page }) => {
    const select = page.locator('select[name="user_type"]');
    await expect(select).toBeVisible();
    await expect(select).toContainText('Student');
    await expect(select).toContainText('Faculty');
    await expect(select).toContainText('Administrator');
    await expect(select).toContainText('Statistics Department Staff');
  });

  test('rejects empty email and password', async ({ page }) => {
    await page.fill('input[name="email"]', '');
    await page.fill('input[name="password"]', '');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/index\.php/);
  });

  test('has forgot password link', async ({ page }) => {
    await expect(page.locator('a[href*="forgot_password"]')).toBeVisible();
  });

  test('has link to public catalog', async ({ page }) => {
    await expect(page.locator('a[href*="public/index.php"]')).toBeVisible();
  });

  test('has link to register page', async ({ page }) => {
    await expect(page.locator('a[href*="register.php"]')).toBeVisible();
  });
});
