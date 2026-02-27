// Base test configuration with shared utilities
import { test as base, expect } from '@playwright/test';

export const test = base.extend({
  // Custom helper for taking named screenshots
  takeScreenshot: async ({ page }, use) => {
    return async (name) => {
      await page.screenshot({
        path: `tests/e2e/screenshots/${name}-${Date.now()}.png`,
        fullPage: true
      });
    };
  },
});

// Re-export expect for convenience
export { expect };

// Test data constants
export const TEST_USERS = {
  admin: {
    email: 'admin@quran.com',
    password: 'Admin@123',
    name: 'مدير النظام',
  },
  supervisor: {
    email: 'ahmed@quran.com',
    password: 'Super@123',
    name: 'الأستاذ أحمد محمد',
  },
  student: {
    email: 'student1@quran.com',
    password: 'Student@123',
    name: 'عبدالله أحمد الزيد',
  },
  parent: {
    email: 'parent1@quran.com',
    password: 'Parent@123',
    name: 'والد ',
  },
};
