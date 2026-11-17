import { test, expect } from '@playwright/test';

test.describe('Registration Page', () => {
  test('loads the registration form', async ({ page }) => {
    await page.goto('/register.php');
    await expect(page).toHaveTitle(/Create Account/);
    await expect(page.locator('form')).toBeVisible();
  });

  test('has CSRF token field', async ({ page }) => {
    await page.goto('/register.php');
    await expect(page.locator('input[name="csrf_token"]')).toHaveAttribute('type', 'hidden');
  });

  test('has link back to login', async ({ page }) => {
    await page.goto('/register.php');
    await expect(page.locator('a[href*="index.php"]')).toBeVisible();
  });
});
