import { test, expect } from '@playwright/test';

test('frontend respects initial asset and dependency budget', async ({ page }) => {
  const requests = [];
  page.on('request', (request) => requests.push(request.url()));

  await page.goto('/');
  await page.waitForLoadState('networkidle');

  const metrics = await page.evaluate(() => {
    const entries = performance.getEntriesByType('resource');
    const scripts = entries.filter((entry) => entry.initiatorType === 'script');
    const css = entries.filter((entry) => entry.initiatorType === 'link' && /\.css(?:\?|$)/.test(entry.name));
    return {
      scriptTransfer: scripts.reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
      cssTransfer: css.reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
      scriptCount: scripts.length,
      cssCount: css.length,
      domContentLoaded: performance.getEntriesByType('navigation')[0]?.domContentLoadedEventEnd || 0
    };
  });

  expect(metrics.scriptTransfer).toBeLessThan(550_000);
  expect(metrics.cssTransfer).toBeLessThan(650_000);
  expect(metrics.scriptCount).toBeLessThan(25);
  expect(metrics.cssCount).toBeLessThan(30);
  expect(metrics.domContentLoaded).toBeLessThan(5_000);

  const external = requests.filter((url) => {
    const parsed = new URL(url);
    return !['localhost', '127.0.0.1'].includes(parsed.hostname);
  });
  expect(external, `Unexpected third-party requests: ${external.join(', ')}`).toEqual([]);
});
