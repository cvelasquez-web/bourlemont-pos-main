import { test, expect } from '@playwright/test';

test.describe('Inventario', () => {
  test('Editar stock', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });

    await page.goto('/inventario', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('h1')).toContainText('Control de Inventario', { timeout: 15000 });
  });
});