import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Reports', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
  });

  test('should display reports page', async ({ page }) => {
    await page.goto('/supervisor/reports');
    await expect(page).toHaveURL(/supervisor\/reports/);
    await expect(page.locator('.card, table, h1, h4').first()).toBeVisible();
  });

  test('should navigate to recitation report', async ({ page }) => {
    await page.click('a:has-text("التقارير")');
    await expect(page).toHaveURL(/supervisor\/reports/);
  });
});
