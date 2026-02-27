import { test, expect, TEST_USERS } from './base';

test.describe('Student Sessions', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.student.email);
    await page.fill('input[name="password"]', TEST_USERS.student.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/student\/dashboard/);
    await page.goto('/student/sessions');
  });

  test('should display student sessions page', async ({ page }) => {
    await expect(page).toHaveURL(/student\/sessions/);
    await expect(page.locator('h1, h2, h3, h4, .card').first()).toBeVisible();
  });
});
