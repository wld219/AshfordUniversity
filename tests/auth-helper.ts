import { test as base, expect } from '@playwright/test';

// Test credentials — using accounts that do NOT trigger 2FA
// 2FA is triggered for: liamwalker@ashford.edu (Student) and user_ID 300008 (Admin)
const CREDENTIALS = {
  Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
};

// Role home URLs
const ROLE_HOMES = {
  Student: '/student/dashboard.php',
  Faculty: '/faculty/dashboard.php',
  Admin: '/admin/dashboard.php',
  StatStaff: '/statstaff/dashboard.php',
};

type Role = keyof typeof CREDENTIALS;

const test = base.extend<{ role: Role }>({
  role: [undefined, { option: true }],
});

export const loginAs = async (page: any, role: Role) => {
  const creds = CREDENTIALS[role];
  await page.goto('/index.php');
  await page.fill('input[name="email"]', creds.email);
  await page.fill('input[name="password"]', creds.password);
  await page.selectOption('select[name="user_type"]', role);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(ROLE_HOMES[role]);
};

export { test, expect, CREDENTIALS, ROLE_HOMES };
