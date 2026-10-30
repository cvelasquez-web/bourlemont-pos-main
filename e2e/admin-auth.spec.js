import { test, expect } from '@playwright/test';

test.describe('Flujo de Administrador', () => {
  test('Carlos entra con PIN al Dashboard', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });
    
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('h1')).toContainText('Dashboard de Ventas', { timeout: 15000 });
  });

  test('Carlos entra con contraseña', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#tab-credential').click();
    await page.locator('#admin-user').fill('Carlos Bourlemont');
    await page.locator('#admin-pass').fill('admin');
    await page.locator('#btn-admin-login').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });
  });
});