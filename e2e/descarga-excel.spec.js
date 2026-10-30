import { test, expect } from '@playwright/test';

test.describe('Descarga Excel', () => {
  test('Generar Excel', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });

    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#btn-export-excel')).toBeVisible({ timeout: 15000 });
    await page.locator('#btn-export-excel').click();
    await expect(page.locator('#btn-export-excel')).toContainText('Generando', { timeout: 5000 });
  });
});