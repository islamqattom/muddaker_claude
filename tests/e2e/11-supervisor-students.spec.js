import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Students List', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
    await page.goto('/supervisor/students');
  });

  test('should display students list page', async ({ page }) => {
    // Check page title
    await expect(page.locator('h1')).toBeVisible();
    // Should have a table or list
    await expect(page.locator('table, .card').first()).toBeVisible();
  });

  test('should navigate to student detail', async ({ page }) => {
    // Look for a student link and click it
    const studentLink = page.locator('a[href*="/supervisor/students/"]').first();
    if (await studentLink.isVisible()) {
      await studentLink.click();
      // Should navigate to student detail page
      await expect(page).toHaveURL(/supervisor\/students\/\d+/);
    }
  });
});
