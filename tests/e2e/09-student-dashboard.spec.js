import { test, expect, TEST_USERS } from './base';

test.describe('Student Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="login"]', TEST_USERS.student.email);
    await page.fill('input[name="password"]', TEST_USERS.student.password);
    await page.click('button[type="submit"]');
    await page.waitForURL(/student\/dashboard/);
  });

  test('should display student dashboard correctly', async ({ page }) => {
    // Check welcome message
    await expect(page.locator('h4:has-text("مرحباً")')).toBeVisible();
    // Check stats cards - use specific selectors to avoid strict mode
    await expect(page.locator('small:has-text("حضور هذا الشهر")')).toBeVisible();
    await expect(page.locator('small:has-text("غياب هذا الشهر")')).toBeVisible();
    await expect(page.locator('small:has-text("جلسات التسميع")').first()).toBeVisible();
    await expect(page.locator('small:has-text("صفحات جديدة")')).toBeVisible();
  });

  test('should display recent sessions section', async ({ page }) => {
    await expect(page.locator('text=آخر جلسات التسميع')).toBeVisible();
  });

  test('should have navigation menu', async ({ page }) => {
    await expect(page.locator('a:has-text("لوحتي الشخصية")')).toBeVisible();
    await expect(page.locator('.nav-link:has-text("جلسات التسميع")')).toBeVisible();
    await expect(page.locator('a:has-text("سجل حضوري")')).toBeVisible();
    await expect(page.locator('a:has-text("تقاريري")')).toBeVisible();
    await expect(page.locator('a:has-text("الإنذارات")')).toBeVisible();
    // Use first() to avoid strict mode violation since "طرق الحفظ" appears in both nav and card
    await expect(page.locator('.nav-link:has-text("طرق الحفظ")')).toBeVisible();
  });

  test('should navigate to memorization methods', async ({ page }) => {
    await page.click('a:has-text("طرق الحفظ")');
    await expect(page).toHaveURL(/student\/memorization-methods/);
  });
});
