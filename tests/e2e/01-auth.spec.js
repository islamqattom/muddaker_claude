import { test, expect, TEST_USERS } from './base';

test.describe('Authentication', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    // Wait for form to be visible
    await page.waitForSelector('input[name="login"]');
  });

  test('should display login page correctly', async ({ page }) => {
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should login successfully as admin', async ({ page }) => {
    await page.fill('input[name="login"]', TEST_USERS.admin.email);
    await page.fill('input[name="password"]', TEST_USERS.admin.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/admin\/dashboard/, { timeout: 10000 });
  });

  test('should login successfully as supervisor', async ({ page }) => {
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/, { timeout: 10000 });
  });

  test('should login successfully as student', async ({ page }) => {
    await page.fill('input[name="login"]', TEST_USERS.student.email);
    await page.fill('input[name="password"]', TEST_USERS.student.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/student\/dashboard/, { timeout: 10000 });
  });

  test('should login successfully as parent', async ({ page }) => {
    await page.fill('input[name="login"]', TEST_USERS.parent.email);
    await page.fill('input[name="password"]', TEST_USERS.parent.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/parent\/dashboard/, { timeout: 10000 });
  });
});
