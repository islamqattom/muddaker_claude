import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Student Reports', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
    await page.goto('/supervisor/students');
  });

  test('should display the student report filter page', async ({ page }) => {
    // Check page title
    await expect(page.locator('h1')).toBeVisible();
    // Student selector filter should be present
    await expect(page.locator('select[name="student_id"]')).toBeVisible();
    await expect(page.locator('select[name="period"]')).toBeVisible();
  });

  test('should show report for a selected student', async ({ page }) => {
    const options = page.locator('select[name="student_id"] option');
    const count = await options.count();
    if (count > 1) {
      const value = await options.nth(1).getAttribute('value');
      await page.selectOption('select[name="student_id"]', value);
      // Written report section should render for the selected student
      await expect(page).toHaveURL(/student_id=/);
      await expect(page.locator('.rpt-written-report')).toBeVisible();
    } else {
      // No students: empty state should be shown
      await expect(page.locator('.rpt-empty')).toBeVisible();
    }
  });
});
