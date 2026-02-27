import { test, expect, TEST_USERS } from './base';

test.describe('Admin Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.admin.email);
    await page.fill('input[name="password"]', TEST_USERS.admin.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/admin\/dashboard/);
  });

  test('should display admin dashboard correctly', async ({ page }) => {
    // Check page is accessible
    await expect(page).toHaveURL(/admin\/dashboard/);
    // Check welcome message
    await expect(page.locator('h1, h2, h3, h4').first()).toBeVisible();
  });

  test('should have admin navigation menu', async ({ page }) => {
    // Check for main admin menu items
    await expect(page.locator('a:has-text("الطلاب")').first()).toBeVisible();
    await expect(page.locator('a:has-text("المشرفون")').first()).toBeVisible();
    await expect(page.locator('a:has-text("أولياء الأمور")').first()).toBeVisible();
  });

  test('should navigate to students management', async ({ page }) => {
    await page.click('a:has-text("الطلاب")');
    await expect(page).toHaveURL(/admin\/students/);
  });

  test('should navigate to supervisors management', async ({ page }) => {
    await page.click('a:has-text("المشرفون")');
    await expect(page).toHaveURL(/admin\/supervisors/);
  });

  test('should navigate to parents management', async ({ page }) => {
    await page.click('a:has-text("أولياء الأمور")');
    await expect(page).toHaveURL(/admin\/parents/);
  });

  test('should navigate to levels management', async ({ page }) => {
    await page.click('a:has-text("المستويات")');
    await expect(page).toHaveURL(/admin\/levels/);
  });

  test('should navigate to reports', async ({ page }) => {
    await page.click('a:has-text("التقارير")');
    await expect(page).toHaveURL(/admin\/reports/);
  });
});
