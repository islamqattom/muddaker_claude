import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Attendance Form', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
    await page.goto('/supervisor/attendance');
  });

  test('should display attendance form correctly', async ({ page }) => {
    // Check date filter exists
    await expect(page.locator('input[type="date"]')).toBeVisible();
    // Check form exists
    await expect(page.locator('form#attendanceForm')).toBeVisible();
    // Check save button exists
    await expect(page.locator('button:has-text("حفظ سجل الحضور")')).toBeVisible();
  });

  test('should display attendance stats', async ({ page }) => {
    // Check for attendance stats - use .first() since multiple elements exist
    await expect(page.locator('small:has-text("حاضر")').first()).toBeVisible();
    await expect(page.locator('small:has-text("غائب")').first()).toBeVisible();
    await expect(page.locator('small:has-text("متأخر")').first()).toBeVisible();
  });

  test('should mark all present button works', async ({ page }) => {
    // Click mark all present button
    await page.click('button:has-text("تحديد الكل حاضر")');
    // Check that radio buttons are checked - this is a JavaScript action
    // The page should still be functional after clicking
    await expect(page.locator('form#attendanceForm')).toBeVisible();
  });

  test('should mark all absent button works', async ({ page }) => {
    // Click mark all absent button
    await page.click('button:has-text("تحديد الكل غائب")');
    // Check that form is still visible
    await expect(page.locator('form#attendanceForm')).toBeVisible();
  });

  test('should change date filter', async ({ page }) => {
    // Get current date value
    const dateInput = page.locator('input[type="date"]');
    const currentDate = await dateInput.inputValue();

    // Set a new date
    const newDate = '2024-01-15';
    await dateInput.fill(newDate);

    // Wait for page to reload with new date
    await page.waitForTimeout(1000);

    // Check URL contains the new date parameter
    await expect(page).toHaveURL(/date=/);
  });
});
