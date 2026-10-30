import { test, expect } from '@playwright/test';

test.describe('Validación', () => {
  test('PIN corto', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });

    await page.goto('/usuarios', { waitUntil: 'domcontentloaded' });
    await page.locator('#btn-nuevo-usuario').click();
    await page.locator('#nuevo-nombre').fill('Error Test');
    await page.locator('#nuevo-pin').fill('12');
    await page.getByRole('button', { name: 'Guardar Colaborador' }).click();
    await expect(page.locator('#modal-nuevo-usuario')).toBeVisible();
  });
});