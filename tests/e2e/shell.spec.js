import { test, expect } from '@playwright/test';

test('frontend shell loads canonical runtime and primary interactions work', async ({ page }) => {
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));

  await page.goto('/');
  await expect(page.locator('body')).toHaveClass(/digipublish-shell/);

  const header = page.locator('.dp-header').first();
  await expect(header).toBeVisible();
  await expect(header).toHaveAttribute('data-wp-interactive', 'digipublish/site');
  await expect(header).toHaveAttribute('data-wp-on-document--keydown', 'callbacks.handleKeydown');

  const scheme = page.locator('[data-dp-scheme-toggle]:visible').first();
  await expect(scheme).toHaveAttribute('data-wp-on--click', 'actions.toggleScheme');
  await scheme.click();
  await expect(page.locator('html')).toHaveClass(/dp-theme-dark/);
  await expect(page.locator('html')).toHaveAttribute('data-dp-scheme', 'dark');
  await expect(scheme).toHaveAttribute('aria-pressed', 'true');

  const searchToggle = page.locator('[data-dp-search-toggle]:visible').first();
  const searchPanel = page.locator('.dp-search').first();
  await expect(searchToggle).toHaveAttribute('data-wp-on--click', 'actions.toggleSearch');
  await expect(searchPanel).toHaveAttribute('data-wp-interactive', 'digipublish/site');
  await searchToggle.click();
  await expect(searchPanel).toHaveClass(/is-open/);
  await expect(searchToggle).toHaveAttribute('aria-expanded', 'true');

  await page.keyboard.press('Escape');
  await expect(searchPanel).not.toHaveClass(/is-open/);

  await page.evaluate(() => window.scrollTo(0, 240));
  await expect(header).toHaveClass(/is-sticky/);

  expect(errors).toEqual([]);
});

test('mobile fullscreen menu has coherent expanded state', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');

  const toggle = page.locator('[data-dp-fullscreen-toggle]:visible').first();
  const overlay = page.locator('.dp-fullscreen').first();

  await expect(toggle).toBeVisible();
  await expect(toggle).toHaveAttribute('data-wp-on--click', 'actions.toggleMenu');
  await expect(overlay).toHaveAttribute('data-wp-interactive', 'digipublish/site');

  await toggle.click();
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await expect(overlay).toHaveClass(/is-open/);
  await expect(page.locator('body')).toHaveClass(/dp-menu-open/);

  await page.keyboard.press('Escape');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await expect(overlay).not.toHaveClass(/is-open/);
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

test('Post Feed carousel is owned by the WordPress Interactivity API', async ({ page }) => {
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));

  await page.goto('/digipublish-carousel-test/');
  const carousel = page.locator('.tp-post-feed--carousel').first();
  await expect(carousel).toBeVisible();
  await expect(carousel).toHaveAttribute('data-wp-interactive', 'digipublish/post-feed');

  const counter = carousel.locator('[data-dp-carousel-current]');
  const next = carousel.locator('[data-dp-carousel-next]');
  const prev = carousel.locator('[data-dp-carousel-prev]');
  const dots = carousel.locator('[data-dp-carousel-dot]');

  await expect(counter).toHaveText('1');
  await expect(dots.nth(0)).toHaveAttribute('aria-selected', 'true');

  await next.click();
  await expect(counter).toHaveText('2');
  await expect(dots.nth(1)).toHaveClass(/is-active/);
  await expect(dots.nth(1)).toHaveAttribute('aria-selected', 'true');

  await prev.click();
  await expect(counter).toHaveText('1');

  await dots.nth(2).click();
  await expect(counter).toHaveText('3');
  await expect(dots.nth(2)).toHaveAttribute('aria-selected', 'true');

  expect(errors).toEqual([]);
});


test('Post Feed Load More uses the WordPress Interactivity API', async ({ page }) => {
  await page.goto('/digipublish-pagination-test/');

  const feed = page.locator('.tp-post-feed[data-wp-interactive="digipublish/post-feed"]').first();
  await expect(feed).toBeVisible();
  await expect(feed.locator('.tp-card')).toHaveCount(3);

  const button = feed.locator('[data-dp-load-more]');
  await expect(button).toHaveAttribute('data-wp-on--click', 'actions.loadNext');
  await button.click();

  await expect(feed.locator('.tp-card')).toHaveCount(6);
  await expect(feed).toHaveAttribute('aria-busy', 'false');
  await expect(feed.locator('[data-dp-load-status]')).toHaveText('');
});


test('Auto Load Next is owned by the WordPress Interactivity API', async ({ page }) => {
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));

  await page.goto('/digipublish-auto-next-a/');
  const runtime = page.locator('.dp-nextpost-runtime[data-wp-interactive="digipublish/site"]').first();
  const sentinel = runtime.locator('[data-dp-nextpost-sentinel]');
  const status = runtime.locator('[data-dp-nextpost-status]');

  await expect(runtime).toBeVisible();
  await expect(sentinel).toHaveAttribute('data-wp-init', 'callbacks.initLoadNext');

  await sentinel.scrollIntoViewIfNeeded();
  await expect(runtime.locator('[data-dp-nextpost-section]')).toHaveCount(1);
  await expect(runtime.locator('[data-post-id]')).toHaveCount(1);
  await expect(status).toHaveText('');

  const loaded = runtime.locator('[data-dp-nextpost-section]').first();
  await loaded.scrollIntoViewIfNeeded();
  await expect(page).toHaveURL(/digipublish-auto-next-b/);
  await expect(page).toHaveTitle(/DigiPublish Auto Next B/);

  await sentinel.scrollIntoViewIfNeeded();
  await expect(runtime.locator('[data-dp-nextpost-section]')).toHaveCount(2);

  expect(errors).toEqual([]);
});
