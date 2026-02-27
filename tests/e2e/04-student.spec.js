import { test, expect, TEST_USERS } from './base';

test.describe('Student Panel', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.student.email);
    await page.fill('input[name="password"]', TEST_USERS.student.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/student\/dashboard/);
  });

  test('should display student dashboard correctly', async ({ page }) => {
    await expect(page).toHaveURL(/student\/dashboard/);
  });

  test('should navigate to memorization methods', async ({ page }) => {
    await page.goto('/student/memorization-methods');
    await expect(page).toHaveURL(/student\/memorization-methods/);
  });

  test('should navigate to sessions', async ({ page }) => {
    await page.goto('/student/sessions');
    await expect(page).toHaveURL(/student\/sessions/);
  });

  test('should navigate to attendance', async ({ page }) => {
    await page.goto('/student/attendance');
    await expect(page).toHaveURL(/student\/attendance/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.goto('/student/reports');
    await expect(page).toHaveURL(/student\/reports/);
  });

  test('should navigate to warnings', async ({ page }) => {
    await page.goto('/student/warnings');
    await expect(page).toHaveURL(/student\/warnings/);
  });

  test('should navigate to profile', async ({ page }) => {
    await page.goto('/student/profile');
    await expect(page).toHaveURL(/student\/profile/);
  });
});
