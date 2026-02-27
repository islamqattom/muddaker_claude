import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Warnings', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
    await page.goto('/supervisor/warnings');
  });

  test('should display warnings list page', async ({ page }) => {
    // Check page is accessible
    await expect(page).toHaveURL(/supervisor\/warnings/);
    // Should have some content or message
    await expect(page.locator('.card, table, h1, h4, h6').first()).toBeVisible();
  });
});
