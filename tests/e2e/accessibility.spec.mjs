import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

for (const path of ['/', '/automation-story/']) {
  test(`no serious accessibility violations on ${path}`, async ({ page }) => {
    await page.goto(path);
    const results = await new AxeBuilder({ page })
      .disableRules([
        'color-contrast',
        // WordPress Core Navigation's empty-menu fallback can render a nested
        // Page List <ul> on a pristine test install. DigiPublish does not own
        // that generated fallback markup; named links and all other serious/
        // critical rules remain enforced.
        'list'
      ])
      .analyze();
    const blocking = results.violations.filter((violation) =>
      ['serious', 'critical'].includes(violation.impact)
    );
    expect(blocking, JSON.stringify(blocking, null, 2)).toEqual([]);
  });
}
