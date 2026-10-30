import { test, expect } from '@playwright/test';

test('Login Admin con Contraseña', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  
  await page.locator('#tab-credential').click();
  await page.locator('#admin-user').fill('Carlos Bourlemont');
  await page.locator('#admin-pass').fill('admin');
  await page.locator('#btn-admin-login').click();
  
  // Esperar a que entre al POS verificando el nombre
  await expect(page.locator('#header-user-name')).toContainText('Carlos Bourlemont', { timeout: 15000 });
});