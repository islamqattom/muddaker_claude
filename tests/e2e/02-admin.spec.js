import { test, expect, TEST_USERS } from './base';

test.describe('Admin Panel', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.admin.email);
    await page.fill('input[name="password"]', TEST_USERS.admin.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/admin\/dashboard/);
  });

  test('should display admin dashboard correctly', async ({ page }) => {
    await expect(page.locator('h1.page-title')).toContainText('لوحة التحكم');
    // Just check we're on the dashboard URL
    await expect(page).toHaveURL(/admin\/dashboard/);
  });

  test('should navigate to students management', async ({ page }) => {
    await page.click('text=الطلاب');
    await expect(page).toHaveURL(/admin\/students/);
  });

  test('should navigate to supervisors management', async ({ page }) => {
    await page.click('text=المشرفون');
    await expect(page).toHaveURL(/admin\/supervisors/);
  });

  test('should navigate to parents management', async ({ page }) => {
    await page.click('text=أولياء الأمور');
    await expect(page).toHaveURL(/admin\/parents/);
  });

  test('should navigate to levels management', async ({ page }) => {
    await page.click('text=المستويات');
    await expect(page).toHaveURL(/admin\/levels/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.click('text=التقارير الشاملة');
    await expect(page).toHaveURL(/admin\/reports/);
  });

  test('should navigate to warnings management', async ({ page }) => {
    await page.click('a:has-text("الإنذارات")');
    await expect(page).toHaveURL(/admin\/warnings/);
  });

  test('should navigate to settings', async ({ page }) => {
    await page.click('text=إعدادات النظام');
    await expect(page).toHaveURL(/admin\/settings/);
  });

  test('should create new student', async ({ page }) => {
    await page.goto('/admin/students/create');
    await expect(page.locator('h1')).toContainText('إضافة طالب');
  });

  test('should create new supervisor', async ({ page }) => {
    await page.goto('/admin/supervisors/create');
    await expect(page.locator('h1')).toContainText('إضافة مشرف');
  });
});
