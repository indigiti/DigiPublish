import { test, expect } from '@playwright/test';

test('frontend shell loads canonical runtime and primary interactions work', async ({ page }) => {
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));

  await page.goto('/');
  await expect(page.locator('body')).toHaveClass(/digipublish-shell/);
  await expect(page.locator('.dp-header').first()).toBeVisible();

  const scheme = page.locator('[data-dp-scheme-toggle]:visible').first();
  await scheme.click();
  await expect(page.locator('html')).toHaveClass(/dp-theme-dark/);
  await expect(page.locator('html')).toHaveAttribute('data-dp-scheme', 'dark');
  await expect(scheme).toHaveAttribute('aria-pressed', 'true');

  const searchToggle = page.locator('[data-dp-search-toggle]:visible').first();
  await searchToggle.click();
  await expect(page.locator('.dp-search').first()).toHaveClass(/is-open/);
  await expect(searchToggle).toHaveAttribute('aria-expanded', 'true');

  await page.keyboard.press('Escape');
  await expect(page.locator('.dp-search').first()).not.toHaveClass(/is-open/);

  expect(errors).toEqual([]);
});

test('mobile fullscreen menu has coherent expanded state', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');

  const toggle = page.locator('[data-dp-fullscreen-toggle]:visible').first();
  await expect(toggle).toBeVisible();
  await toggle.click();

  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await expect(page.locator('.dp-fullscreen').first()).toHaveClass(/is-open/);
  await expect(page.locator('body')).toHaveClass(/dp-menu-open/);

  await page.keyboard.press('Escape');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await expect(page.locator('.dp-fullscreen').first()).not.toHaveClass(/is-open/);
});

test('editor loads DigiPublish block registrations and modular editor APIs', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await page.locator('#wp-submit').click();

  await page.goto('/wp-admin/post-new.php');
  await page.waitForFunction(() => window.wp?.blocks?.getBlockType?.('digipublish/post-feed'));

  const state = await page.evaluate(() => ({
    postFeed: !!window.wp.blocks.getBlockType('digipublish/post-feed'),
    queryModule: !!window.DigiPublishEditorQuery,
    designModule: !!window.DigiPublishEditorDesign
  }));

  expect(state).toEqual({ postFeed: true, queryModule: true, designModule: true });
});
