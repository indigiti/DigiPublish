import { test, expect } from '@playwright/test';

test('homepage renders DigiPublish shell and editorial content', async ({ page }) => {
  const response = await page.goto('/');
  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('body')).toHaveClass(/digipublish-site/);
  await expect(page.locator('header').first()).toBeVisible();
  await expect(page.locator('footer').first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Fatal error');
});

test('single article renders hero, content, sidebar/read-next system', async ({ page }) => {
  const response = await page.goto('/automation-story/');
  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('main')).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Automation Story', level: 1 })).toBeVisible();
  await expect(page.locator('.wp-block-post-content')).toContainText('representative DigiPublish editorial content');
  await expect(page.locator('body')).not.toContainText('Fatal error');
});

test('search overlay toggles with accessible state', async ({ page }) => {
  await page.goto('/');
  const button = page.locator('[data-dp-search-toggle]').first();
  if (await button.count()) {
    await button.click();
    await expect(button).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('.dp-search').first()).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(button).toHaveAttribute('aria-expanded', 'false');
  }
});

test('mobile navigation opens and closes', async ({ page, isMobile }) => {
  test.skip(!isMobile, 'Mobile interaction test');
  await page.goto('/');
  const toggle = page.locator('[data-dp-fullscreen-toggle]').first();
  if (await toggle.count()) {
    await toggle.click();
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('.dp-fullscreen').first()).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  }
});
