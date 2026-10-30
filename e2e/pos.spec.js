import { test, expect } from '@playwright/test';

test.describe('Flujo del POS', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="1"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="3"]').click();
    await page.locator('.pin-key[data-val="4"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });
  });

  test('Cobrar un producto', async ({ page }) => {
    const producto = page.locator('.product-card').first();
    await expect(producto).toBeVisible({ timeout: 15000 });
    await producto.click();
    await page.locator('#btn-cobrar').click();
    await page.locator('#confirm-payment-btn').click();
    await expect(page.locator('#ticket-content-box')).toBeVisible();
  });
});