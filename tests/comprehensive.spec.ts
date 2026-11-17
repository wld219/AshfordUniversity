import { test, expect } from '@playwright/test';

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

async function loginFresh(context: any, role: Role) {
  const c = CREDENTIALS[role];
  const page = await context.newPage();
  await page.goto('/index.php');
  await page.fill('input[name="email"]', c.email);
  await page.fill('input[name="password"]', c.password);
  await page.selectOption('select[name="user_type"]', role);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(ROLE_HOMES[role]);
  return page;
}

// ==================== PUBLIC SITE ====================
test.describe('PUBLIC SITE', () => {
  test('landing page loads with all sections', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Ashford University/);
    await expect(page.locator('a[href="#overview"]')).toBeVisible();
    await expect(page.locator('a[href="#academics"]')).toBeVisible();
    await expect(page.locator('a[href="#admissions"]')).toBeVisible();
    await expect(page.locator('a[href="#schedule"]')).toBeVisible();
    await expect(page.locator('.landing-portals li')).toHaveCount(4);
    await expect(page.locator('.skip-link')).toBeVisible();
  });

  test('all public pages load', async ({ page }) => {
    for (const url of ['/public/departments.php', '/public/courses.php', '/public/programs.php', '/public/schedule.php']) {
      await page.goto(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('public pages have no JS errors', async ({ page }) => {
    const errors: string[] = [];
    page.on('pageerror', (err) => errors.push(err.message));
    await page.goto('/');
    await page.goto('/public/courses.php');
    expect(errors).toHaveLength(0);
  });
});

// ==================== AUTHENTICATION ====================
test.describe('AUTHENTICATION', () => {
  for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
    test(`${role} login works`, async ({ context }) => {
      const page = await loginFresh(context, role);
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('.brand b')).toContainText('Ashford University');
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
    });
  }

  test('logout works', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.click('a[href*="logout.php"]');
    await expect(page).toHaveURL(/index\.php/);
  });

  test('invalid credentials rejected', async ({ page }) => {
    await page.goto('/index.php');
    await page.fill('input[name="email"]', 'wrong@email.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.selectOption('select[name="user_type"]', 'Student');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/index\.php/);
  });

test('register page loads', async ({ page }) => {
    await page.goto('/register.php');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="csrf_token"]')).toHaveAttribute('type', 'hidden');
    await expect(page.locator('a[href*="index.php"]')).toBeVisible();
  });
});

// ==================== STUDENT PORTAL ====================
test.describe('STUDENT PORTAL', () => {
  test('all pages load', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    const pages = [
      '/student/dashboard.php', '/student/master-schedule.php', '/student/registration.php',
      '/student/schedule.php', '/student/degree-audit.php', '/student/transcript.php',
      '/student/holds.php', '/student/advisor.php', '/student/programs.php',
      '/student/catalog.php', '/student/buildings.php', '/student/information.php',
      '/student/advisor_appointment.php',
    ];
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
      await expect(page.locator('.topbar')).toBeVisible();
    }
  });

  test('student dashboard has progress and quick actions', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/dashboard.php');
    await expect(page.locator('h3').filter({ hasText: 'Degree Progress' })).toBeVisible();
    await expect(page.locator('h3').filter({ hasText: 'Quick Actions' })).toBeVisible();
  });

  test('student registration has filters and waitlist', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/registration.php');
    await expect(page.locator('h1')).toContainText('Course Registration');
    await expect(page.locator('select[name="department"]')).toBeVisible();
    await expect(page.locator('select[name="faculty"]')).toBeVisible();
  });

  test('student schedule has grades table', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/schedule.php');
    await expect(page.locator('h1')).toContainText('My Schedule');
    await expect(page.locator('table')).toBeVisible();
  });

  test('student degree audit has progress bars', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/degree-audit.php');
    await expect(page.locator('.progress-bar, .progress-fill')).toBeVisible({ timeout: 5000 });
  });

  test('student transcript has print button', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/transcript.php');
    await expect(page.locator('button[onclick="window.print()"]')).toBeVisible();
  });

  test('student programs has comparison', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/programs.php');
    await expect(page.locator('h2').filter({ hasText: 'Program Comparison' })).toBeVisible();
  });

  test('student catalog has search', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/catalog.php');
    await expect(page.locator('input[name="search"]')).toBeVisible();
  });

  test('student buildings has room listings', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    await page.goto('/student/buildings.php');
    await expect(page.locator('body')).toContainText(/Building|Room/);
  });

  test('student pages have no JS errors', async ({ context }) => {
    const page = await loginFresh(context, 'Student');
    const errors: string[] = [];
    page.on('pageerror', (err) => errors.push(err.message));
    await page.goto('/student/dashboard.php');
    await page.goto('/student/registration.php');
    await page.goto('/student/schedule.php');
    expect(errors).toHaveLength(0);
  });
});

// ==================== FACULTY PORTAL ====================
test.describe('FACULTY PORTAL', () => {
  test('all pages load', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    const pages = [
      '/faculty/dashboard.php', '/faculty/schedule.php', '/faculty/master-schedule.php',
      '/faculty/rosters.php', '/faculty/attendance-calendar.php', '/faculty/attendance-history.php',
      '/faculty/attendance-weekly.php', '/faculty/attendance-time-rules.php', '/faculty/attendance-window.php',
      '/faculty/advisees.php', '/faculty/catalog.php', '/faculty/profile.php',
      '/faculty/advising_appointments.php',
    ];
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('faculty rosters have midterm grade entry', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    await page.goto('/faculty/rosters.php');
    await expect(page.locator('select[name="midterm_grade"], .midterm-badge, .roster-grade-select')).toBeVisible({ timeout: 5000 });
  });

  test('faculty attendance-weekly has section selector', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    await page.goto('/faculty/attendance-weekly.php');
    await expect(page.locator('select[name="crn"], .wa-course-tab')).toBeVisible({ timeout: 5000 });
  });

  test('faculty advising appointments has availability window', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    await page.goto('/faculty/advising_appointments.php');
    await expect(page.locator('h3').filter({ hasText: 'Availability Window' })).toBeVisible();
    await expect(page.locator('button[type="submit"]').filter({ hasText: 'Add Availability' })).toBeVisible();
  });

  test('faculty profile has office info', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    await page.goto('/faculty/profile.php');
    await expect(page.locator('body')).toContainText(/Office|office/);
  });

  test('faculty advisees has academic status', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    await page.goto('/faculty/advisees.php');
    await expect(page.locator('body')).toContainText(/GPA|Credits|Holds|Registered/);
  });

  test('faculty pages have no JS errors', async ({ context }) => {
    const page = await loginFresh(context, 'Faculty');
    const errors: string[] = [];
    page.on('pageerror', (err) => errors.push(err.message));
    await page.goto('/faculty/dashboard.php');
    await page.goto('/faculty/advising_appointments.php');
    await page.goto('/faculty/attendance-weekly.php');
    expect(errors).toHaveLength(0);
  });
});

// ==================== ADMIN PORTAL ====================
test.describe('ADMIN PORTAL', () => {
  test('all pages load', async ({ context }) => {
    const page = await loginFresh(context, 'Admin');
    const pages = [
      '/admin/dashboard.php', '/admin/users.php', '/admin/academic.php', '/admin/sections.php',
      '/admin/semesters.php', '/admin/advisors.php', '/admin/holds.php', '/admin/exceptions.php',
      '/admin/degree-audit.php', '/admin/master-schedule.php', '/admin/audit.php',
    ];
    for (const url of pages) {
      await page.goto(url);
      await expect(page).toHaveURL(url);
      await expect(page.locator('body')).toBeVisible();
    }
  });

  test('admin users has bulk actions and CSV export', async ({ context }) => {
    const page = await loginFresh(context, 'Admin');
    await page.goto('/admin/users.php');
    await expect(page.locator('#users-bulk-form, #users-bulk-submit')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('#users-export-csv')).toBeVisible();
  });

  test('admin holds has resolution workflow', async ({ context }) => {
    const page = await loginFresh(context, 'Admin');
    await page.goto('/admin/holds.php');
    await expect(page.locator('button[type="button"]').filter({ hasText: 'Resolve' })).toBeVisible({ timeout: 5000 });
  });

  test('admin master schedule has validation', async ({ context }) => {
    const page = await loginFresh(context, 'Admin');
    await page.goto('/admin/master-schedule.php');
    await expect(page.locator('h1')).toContainText('Master Schedule');
    await expect(page.locator('body')).toContainText(/section|scheduled|validation/i);
  });

  test('admin pages have no JS errors', async ({ context }) => {
    const page = await loginFresh(context, 'Admin');
    const errors: string[] = [];
    page.on('pageerror', (err) => errors.push(err.message));
    await page.goto('/admin/dashboard.php');
    await page.goto('/admin/users.php');
    await page.goto('/admin/master-schedule.php');
    expect(errors).toHaveLength(0);
  });
});

// ==================== STATSTAFF PORTAL ====================
test.describe('STATSTAFF PORTAL', () => {
  test('dashboard loads', async ({ context }) => {
    const page = await loginFresh(context, 'StatStaff');
    await page.goto('/statstaff/dashboard.php');
    await expect(page).toHaveURL('/statstaff/dashboard.php');
  });

  test('reports page has new reports', async ({ context }) => {
    const page = await loginFresh(context, 'StatStaff');
    await page.goto('/statstaff/reports.php');
    await expect(page.locator('select[name="report"]')).toContainText('Enrollment trends over time');
    await expect(page.locator('select[name="report"]')).toContainText('Department summary');
  });

  test('read-only enforcement on admin pages', async ({ context }) => {
    const page = await loginFresh(context, 'StatStaff');
    const adminPages = ['/admin/users.php', '/admin/sections.php', '/admin/semesters.php', '/admin/academic.php'];
    for (const url of adminPages) {
      await page.goto(url);
      await expect(page.locator('body')).toContainText(/403|permission|read-only/i);
    }
  });
});

// ==================== CROSS-PORTAL ====================
test.describe('CROSS-PORTAL', () => {
  test('all portals share header and logout', async ({ browser }) => {
    for (const role of ['Student', 'Faculty', 'Admin', 'StatStaff'] as Role[]) {
      const context = await browser.newContext();
      const page = await loginFresh(context, role);
      await expect(page.locator('.topbar')).toBeVisible();
      await expect(page.locator('.brand b')).toContainText('Ashford University');
      await expect(page.locator('a[href*="logout.php"]')).toBeVisible();
      await context.close();
    }
  });

  test('sign-in page links to register and public catalog', async ({ page }) => {
    await page.goto('/index.php');
    await expect(page.locator('a[href*="register.php"]')).toBeVisible();
    await expect(page.locator('a[href*="public/index.php"]')).toBeVisible();
  });
});
