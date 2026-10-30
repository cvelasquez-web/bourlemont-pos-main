import { test, expect } from '@playwright/test';

test.describe('Anulación', () => {
  test('Anular', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });

    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await page.locator('#card-comandas-abiertas').click();
    // (Resto de tu lógica de anulación)
  });
});