import { test, expect, TEST_USERS } from './base';

test.describe('Student Attendance', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.student.email);
    await page.fill('input[name="password"]', TEST_USERS.student.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/student\/dashboard/);
  });

  test('should navigate to student attendance', async ({ page }) => {
    await page.click('a:has-text("سجل حضوري")');
    await expect(page).toHaveURL(/student\/attendance/);
  });

  test('should navigate to student reports', async ({ page }) => {
    await page.click('a:has-text("تقاريري")');
    await expect(page).toHaveURL(/student\/reports/);
  });

  test('should navigate to student warnings', async ({ page }) => {
    await page.click('a:has-text("الإنذارات")');
    await expect(page).toHaveURL(/student\/warnings/);
  });
});
