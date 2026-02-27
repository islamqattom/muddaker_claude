import { test, expect, TEST_USERS } from './base';

test.describe('Supervisor Recitation Session Form', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.supervisor.email);
    await page.fill('input[name="password"]', TEST_USERS.supervisor.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/supervisor\/dashboard/);
    await page.goto('/supervisor/recitation');
  });

  test('should display recitation form correctly', async ({ page }) => {
    // Check main form elements exist
    await expect(page.locator('select[name="student_id"]')).toBeVisible();
    await expect(page.locator('text=اختر الطالب ونوع التسميع')).toBeVisible();
    await expect(page.locator('label:has-text("نوع التسميع")')).toBeVisible();
    await expect(page.locator('label:has-text("حفظ جديد")')).toBeVisible();
    await expect(page.locator('label:has-text("مراجعة")')).toBeVisible();
    await expect(page.locator('label[for="type-mafsil"]')).toBeVisible();
    await expect(page.locator('button:has-text("بدء الجلسة")')).toBeVisible();
  });

  test('should show validation when no student selected', async ({ page }) => {
    // Click start session without selecting student
    await page.click('button:has-text("بدء الجلسة")');
    // Check that we're still on the same page (validation prevented navigation)
    await expect(page).toHaveURL(/supervisor\/recitation/);
  });

  test('should select student and show info', async ({ page }) => {
    // Select first student (index 1 because index 0 is the default empty option)
    const studentSelect = page.locator('select[name="student_id"]');
    await studentSelect.selectOption({ index: 1 });

    // Student info should appear
    await expect(page.locator('#studentInfo')).toBeVisible();
  });

  test('should show type options when type is selected', async ({ page }) => {
    // Select a student first
    await page.locator('select[name="student_id"]').selectOption({ index: 1 });

    // Select a type (new_memorization) - click the label since it's a radio button
    await page.click('label[for="type-new_memorization"]');

    // Options should appear
    await expect(page.locator('#options-new_memorization')).toBeVisible();
  });
});
