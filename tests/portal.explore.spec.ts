import { test, expect } from '@playwright/test';

test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Ashford University Portal - Public Site', () => {
  test('loads the public landing page', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Ashford University/);
    await expect(page.locator('h1')).toContainText('Plan the whole term');
  });

  test('navigates to departments page', async ({ page }) => {
    await page.goto('/public/departments.php');
    await expect(page).toHaveTitle(/Departments/);
  });

  test('navigates to courses page', async ({ page }) => {
    await page.goto('/public/courses.php');
    await expect(page).toHaveTitle(/Courses/);
  });

  test('navigates to programs page', async ({ page }) => {
    await page.goto('/public/programs.php');
    await expect(page).toHaveTitle(/Majors & Minors/);
  });

  test('navigates to master schedule page', async ({ page }) => {
    await page.goto('/public/schedule.php');
    await expect(page).toHaveTitle(/Master Schedule/);
  });
});

test.describe('Ashford University Portal - Sign In', () => {
  test('loads the sign-in page', async ({ page }) => {
    await page.goto('/index.php');
    await expect(page).toHaveTitle(/Sign in/);
    await expect(page.locator('form.login-form')).toBeVisible();
  });

  test('has role selector with 4 options', async ({ page }) => {
    await page.goto('/index.php');
    const roleSelect = page.locator('select[name="user_type"]');
    await expect(roleSelect).toBeVisible();
    await expect(roleSelect.locator('option')).toHaveCount(5);
  });
});

test.describe('Ashford University Portal - Student Portal (requires login)', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('redirects to login when not authenticated', async ({ page }) => {
    await page.goto('/student/dashboard.php');
    await expect(page).toHaveTitle(/Sign in/);
  });
});

test.describe('Ashford University Portal - Navigation & Layout', () => {
  test('public landing page has skip link', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.skip-link')).toBeVisible();
  });

  test('public landing page has portal cards', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.landing-portals li')).toHaveCount(4);
  });
});
