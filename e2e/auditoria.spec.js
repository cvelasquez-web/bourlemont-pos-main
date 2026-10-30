import { test, expect } from '@playwright/test';

test.describe('Auditoría', () => {
  test('Editar pago', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="0"]').click();
    await page.locator('.pin-key[data-val="2"]').click();
    await page.locator('.pin-key[data-val="6"]').click();
    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 15000 });

    // Generar una venta
    await page.locator('.product-card').first().click();
    await page.locator('#btn-cobrar').click();
    await page.locator('#confirm-payment-btn').click();
    
    await page.goto('/auditoria', { waitUntil: 'domcontentloaded' });
    const btnEditar = page.locator('.btn-corr').first();
    await expect(btnEditar).toBeVisible({ timeout: 15000 });
    await btnEditar.click();
    await page.locator('#corr-nuevo-metodo').selectOption('TARJETA_VISA');
    await page.locator('#corr-motivo').fill('Prueba');
    await page.locator('#correction-form button[type="submit"]').click();
    await expect(page.locator('#correction-modal')).toBeHidden({ timeout: 15000 });
  });
});