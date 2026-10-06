import { test, expect } from '@playwright/test';

async function login(page) {
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await Promise.all([
    page.waitForURL(/wp-admin/),
    page.locator('#wp-submit').click()
  ]);
}

test('Site Editor loads with DigiPublish active', async ({ page }) => {
  await login(page);
  const response = await page.goto('/wp-admin/site-editor.php');
  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('body')).not.toContainText('There has been a critical error');
  await expect(page.locator('body')).not.toContainText('Fatal error');
});

test('post editor loads DigiPublish block registrations without invalid-block errors', async ({ page }) => {
  await login(page);
  const response = await page.goto('/wp-admin/post-new.php');
  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('body')).not.toContainText('There has been a critical error');
  await expect(page.locator('body')).not.toContainText('This block contains unexpected or invalid content');
});
