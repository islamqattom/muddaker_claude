import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Panel', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
  });

  test('should display supervisor dashboard correctly', async ({ page }) => {
    await expect(page.locator('h1.page-title')).toContainText('لوحة التحكم');
    await expect(page).toHaveURL(/supervisor\/dashboard/);
  });

  test('should navigate to attendance management', async ({ page }) => {
    await page.click('text=الحضور والغياب');
    await expect(page).toHaveURL(/supervisor\/attendance/);
  });

  test('should navigate to recitation management', async ({ page }) => {
    await page.click('text=جلسة تسميع جديدة');
    await expect(page).toHaveURL(/supervisor\/recitation/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.click('a:has-text("التقارير")');
    await expect(page).toHaveURL(/supervisor\/reports/);
  });

  test('should navigate to warnings', async ({ page }) => {
    await page.click('a:has-text("الإنذارات")');
    await expect(page).toHaveURL(/supervisor\/warnings/);
  });

  test('should navigate to students list', async ({ page }) => {
    await page.click('nav a:has-text("الطلاب")');
    await expect(page).toHaveURL(/supervisor\/students/);
  });
});
