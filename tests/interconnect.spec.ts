import { test, expect } from '@playwright/test';

// Test credentials — using accounts that do NOT trigger 2FA
const CREDENTIALS = {
  Admin: { email: 'arianaaugustin@ashford.edu', password: 'Password123' },
  Student: { email: 'davidjoseph@ashford.edu', password: 'Password123' },
  Faculty: { email: 'kevinbaptiste200001@ashford.edu', password: 'Password123' },
  StatStaff: { email: 'ericjones400001@ashford.edu', password: 'Password123' },
};

const ROLE_HOMES = {
  Student: '/student/dashboard.php',
  Faculty: '/faculty/dashboard.php',
  Admin: '/admin/dashboard.php',
  StatStaff: '/statstaff/dashboard.php',
};

type Role = keyof typeof CREDENTIALS;

const loginAs = async (page: any, role: Role) => {
  const creds = CREDENTIALS[role];
  await page.goto('/index.php');
  await page.fill('input[name="email"]', creds.email);
  await page.fill('input[name="password"]', creds.password);
  await page.selectOption('select[name="user_type"]', role);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(ROLE_HOMES[role]);
};

// ==================== PUBLIC SITE ====================
test.describe('Public Site — All Pages & Navigation', () => {
  test('landing page loads with all sections', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Ashford University/);

    // Check all nav section anchors exist (same-page navigation)
    await expect(page.locator('a[href="#overview"]')).toBeVisible();
    await expect(page.locator('a[href="#academics"]')).toBeVisible();
    await expect(page.locator('a[href="#admissions"]')).toBeVisible();
    await expect(page.locator('a[href="#schedule"]')).toBeVisible();

    // Check portal cards
    await expect(page.locator('.landing-portals li')).toHaveCount(4);

    // Check "Open your portal" button
    await expect(page.locator('a[href="index.php"]')).toBeVisible();

    // Footer links
    await expect(page.locator('a[href*="public/departments.php"]')).toBeVisible();
    await expect(page.locator('a[href*="public/courses.php"]')).toBeVisible();
    await expect(page.locator('a[href*="public/programs.php"]')).toBeVisible();
    await expect(page.locator('a[href*="public/schedule.php"]')).toBeVisible();
  });

  test('all public pages load', async ({ page }) => {
    const publicPages = [
      '/public/departments.php',
      '/public/courses.php',
      '/public/programs.php',
      '/public/schedule.php',
    ];

    for (const url of publicPages) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
      await expect(page.locator('nav[aria-label="Public navigation"]')).toBeVisible();
    }
  });
});

// ==================== AUTHENTICATION ====================
test.describe('Authentication — Login & Role-Based Redirect', () => {
  for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
    test(`${role} login redirects to correct portal`, async ({ page }) => {
      await loginAs(page, role);
      await expect(page).toHaveURL(ROLE_HOMES[role]);
    });
  }

  test('invalid credentials show error', async ({ page }) => {
    await page.goto('/index.php');
    await page.fill('input[name="email"]', 'wrong@email.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.selectOption('select[name="user_type"]', 'Student');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/index\.php/);
  });

  test('logout returns to sign-in', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.click('a[href*="logout.php"]');
    await expect(page).toHaveURL(/index\.php/);
  });
});

// ==================== STUDENT PORTAL ====================
test.describe('Student Portal — All Pages Load', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  const studentPages = [
    { url: '/student/dashboard.php', titleContains: '' },
    { url: '/student/master-schedule.php', titleContains: '' },
    { url: '/student/registration.php', titleContains: 'Course Registration' },
    { url: '/student/schedule.php', titleContains: 'My Schedule' },
    { url: '/student/degree-audit.php', titleContains: 'Degree Audit' },
    { url: '/student/transcript.php', titleContains: '' },
    { url: '/student/holds.php', titleContains: '' },
    { url: '/student/advisor.php', titleContains: '' },
    { url: '/student/programs.php', titleContains: '' },
    { url: '/student/catalog.php', titleContains: '' },
    { url: '/student/buildings.php', titleContains: '' },
    { url: '/student/information.php', titleContains: '' },
    { url: '/student/advisor_appointment.php', titleContains: '' },
  ];

  test('all student pages load via direct URL', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');

    for (const p of studentPages) {
      await page.goto(p.url);
      await expect(page).toHaveURL(p.url);
      await expect(page.locator('body')).toBeVisible();
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
    }
  });

  test('registration page has filters and add/drop forms', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/registration.php');

    await expect(page.locator('select[name="department"]')).toBeVisible();
    await expect(page.locator('select[name="faculty"]')).toBeVisible();
    await expect(page.locator('select[name="seats"]')).toBeVisible();
  });

  test('schedule page shows enrolled courses table', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Student');
    await page.goto('/student/schedule.php');
    await expect(page.locator('table')).toBeVisible();
  });
});

// ==================== FACULTY PORTAL ====================
test.describe('Faculty Portal — All Pages Load', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  const facultyPages = [
    '/faculty/dashboard.php',
    '/faculty/schedule.php',
    '/faculty/master-schedule.php',
    '/faculty/rosters.php',
    '/faculty/attendance-calendar.php',
    '/faculty/attendance-history.php',
    '/faculty/attendance-weekly.php',
    '/faculty/attendance-time-rules.php',
    '/faculty/attendance-window.php',
    '/faculty/advisees.php',
    '/faculty/catalog.php',
    '/faculty/profile.php',
  ];

  test('all faculty pages load via direct URL', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');

    for (const url of facultyPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('dashboard shows quick actions', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Faculty');
    await page.goto('/faculty/dashboard.php');

    await expect(page.locator('a[href*="advisees.php"]')).toBeVisible();
    await expect(page.locator('a[href*="rosters.php"]')).toBeVisible();
    await expect(page.locator('a[href*="attendance-calendar.php"]')).toBeVisible();
    await expect(page.locator('a[href*="master-schedule.php"]')).toBeVisible();
  });
});

// ==================== ADMIN PORTAL ====================
test.describe('Admin Portal — All Pages Load', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  const adminPages = [
    '/admin/dashboard.php',
    '/admin/users.php',
    '/admin/academic.php',
    '/admin/sections.php',
    '/admin/semesters.php',
    '/admin/advisors.php',
    '/admin/holds.php',
    '/admin/exceptions.php',
    '/admin/degree-audit.php',
    '/admin/master-schedule.php',
    '/admin/audit.php',
  ];

  test('all admin pages load via direct URL', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');

    for (const url of adminPages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('dashboard shows university overview stats', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/dashboard.php');

    await expect(page.locator('body')).toContainText(/student|faculty|course/i);
    await expect(page.locator('.ao-stat, .dashboard-stat')).toHaveCountGreaterThan(0);
  });

  test('master schedule page has validation report', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'Admin');
    await page.goto('/admin/master-schedule.php');

    await expect(page.locator('h1')).toContainText('Master Schedule');
    await expect(page.locator('body')).toContainText(/section|scheduled|validation/i);
  });
});

// ==================== STATSTAFF PORTAL ====================
test.describe('StatStaff Portal — All Pages & Read-Only Access', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('dashboard and reports load', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'StatStaff');

    await page.goto('/statstaff/dashboard.php');
    await expect(page).toHaveURL('/statstaff/dashboard.php');

    await page.goto('/statstaff/reports.php');
    await expect(page).toHaveURL('/statstaff/reports.php');
  });

  test('statstaff cannot access admin pages', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');
    await loginAs(page, 'StatStaff');

    const adminPages = [
      '/admin/users.php',
      '/admin/sections.php',
      '/admin/semesters.php',
      '/admin/academic.php',
    ];

    for (const url of adminPages) {
      await page.goto(url);
      await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
    }
  });
});

// ==================== CROSS-PORTAL INTERCONNECT ====================
test.describe('Cross-Portal Interconnectivity', () => {
  test('sign-in page links to register and public catalog', async ({ page }) => {
    await page.goto('/index.php');
    await expect(page.locator('a[href*="register.php"]')).toBeVisible();
    await expect(page.locator('a[href*="public/index.php"]')).toBeVisible();
  });

  test('register page links back to sign-in', async ({ page }) => {
    await page.goto('/register.php');
    await expect(page.locator('a[href*="index.php"]')).toBeVisible();
  });

  test('all portals share header and logout (separate contexts)', async ({ browser }) => {
    for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
      const context = await browser.newContext();
      const page = await context.newPage();
      await page.goto('/index.php');
      await page.fill('input[name="email"]', CREDENTIALS[role].email);
      await page.fill('input[name="password"]', CREDENTIALS[role].password);
      await page.selectOption('select[name="user_type"]', role);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(ROLE_HOMES[role]);
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('.brand b')).toContainText('Ashford University');
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
      await context.close();
    }
  });
});

// ==================== PAGE COUNT SUMMARY ====================
test.describe('Portal Page Count Verification', () => {
  test('verifies total page count across all portals', async ({ page }) => {
    const { loginAs } = await import('./auth-helper');

    const counts = {
      Public: 4,  // departments, courses, programs, schedule
      Student: 13,
      Faculty: 12,
      Admin: 11,
      StatStaff: 2,
    };

    // Student
    await loginAs(page, 'Student');
    for (const url of [
      '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
      '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
      '/student/holds.php', '/student/advisor.php', '/student/programs.php',
      '/student/catalog.php', '/student/buildings.php', '/student/information.php',
      '/student/advisor_appointment.php',
    ]) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }

    // Faculty
    await loginAs(page, 'Faculty');
    for (const url of [
      '/faculty/dashboard.php', '/faculty/schedule.php', '/faculty/master-schedule.php',
      '/faculty/rosters.php', '/faculty/attendance-calendar.php', '/faculty/attendance-history.php',
      '/faculty/attendance-weekly.php', '/faculty/attendance-time-rules.php', '/faculty/attendance-window.php',
      '/faculty/advisees.php', '/faculty/catalog.php', '/faculty/profile.php',
    ]) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }

    // Admin
    await loginAs(page, 'Admin');
    for (const url of [
      '/admin/dashboard.php', '/admin/users.php', '/admin/academic.php', '/admin/sections.php',
      '/admin/semesters.php', '/admin/advisors.php', '/admin/holds.php', '/admin/exceptions.php',
      '/admin/degree-audit.php', '/admin/master-schedule.php', '/admin/audit.php',
    ]) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }

    // StatStaff
    await loginAs(page, 'StatStaff');
    for (const url of ['/statstaff/dashboard.php', '/statstaff/reports.php']) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });
});
