import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('homepage has no serious or critical accessibility violations', async ({ page }) => {
  await page.goto('/');
  await page.waitForLoadState('networkidle');

  const results = await new AxeBuilder({ page }).analyze();
  const blocking = results.violations.filter((violation) =>
    violation.impact === 'serious' || violation.impact === 'critical'
  );

  expect(blocking, JSON.stringify(blocking, null, 2)).toEqual([]);
});
