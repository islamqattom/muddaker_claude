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
    // Check welcome message exists - parent uses "أهلاً" not "مرحباً"
    await expect(page.locator('h4:has-text("أهلاً")')).toBeVisible();
    await expect(page.locator('text=تابع تقدم أبنائك')).toBeVisible();
  });

  test('should have navigation menu', async ({ page }) => {
    await expect(page.locator('a:has-text("الصفحة الرئيسية")')).toBeVisible();
    await expect(page.locator('a:has-text("جلسات التسميع")')).toBeVisible();
    await expect(page.locator('a:has-text("سجل الحضور")')).toBeVisible();
    await expect(page.locator('a:has-text("الإنذارات")')).toBeVisible();
    await expect(page.locator('a:has-text("التقارير")')).toBeVisible();
  });

  test('should navigate to children sessions', async ({ page }) => {
    await page.click('a:has-text("جلسات التسميع")');
    await expect(page).toHaveURL(/parent\/sessions/);
  });

  test('should navigate to attendance', async ({ page }) => {
    await page.click('a:has-text("سجل الحضور")');
    await expect(page).toHaveURL(/parent\/attendance/);
  });

  test('should navigate to warnings', async ({ page }) => {
    await page.click('a:has-text("الإنذارات")');
    await expect(page).toHaveURL(/parent\/warnings/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.click('a:has-text("التقارير")');
    await expect(page).toHaveURL(/parent\/reports/);
  });
});
