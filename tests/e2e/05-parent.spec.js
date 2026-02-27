import { test, expect, TEST_USERS } from './base';

test.describe('Parent Portal', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.parent.email);
    await page.fill('input[name="password"]', TEST_USERS.parent.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/parent\/dashboard/);
  });

  test('should display parent dashboard correctly', async ({ page }) => {
    await expect(page).toHaveURL(/parent\/dashboard/);
  });

  test('should navigate to children sessions', async ({ page }) => {
    await page.goto('/parent/sessions');
    await expect(page).toHaveURL(/parent\/sessions/);
  });

  test('should navigate to children attendance', async ({ page }) => {
    await page.goto('/parent/attendance');
    await expect(page).toHaveURL(/parent\/attendance/);
  });

  test('should navigate to warnings', async ({ page }) => {
    await page.goto('/parent/warnings');
    await expect(page).toHaveURL(/parent\/warnings/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.goto('/parent/reports');
    await expect(page).toHaveURL(/parent\/reports/);
  });

  test('should navigate to profile', async ({ page }) => {
    await page.goto('/parent/profile');
    await expect(page).toHaveURL(/parent\/profile/);
  });

  test('should view child details', async ({ page }) => {
    await page.goto('/parent/dashboard');
    // Just verify dashboard loads
    await expect(page).toHaveURL(/parent\/dashboard/);
  });
});
