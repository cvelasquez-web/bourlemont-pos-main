import { test, expect } from '@playwright/test';
import { login } from './helpers';

test.describe('Gestión de Usuarios', () => {
  test('Crear bartender', async ({ page }) => {
    test.setTimeout(90000);

    // Login como Admin
    await login(page, '2026');

    // Ir a usuarios
    await page.goto('/usuarios', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('h1')).toContainText('Gestión de Colaboradores', { timeout: 15000 });

    // Abrir modal
    await page.locator('#btn-nuevo-usuario').click();
    await expect(page.locator('#modal-nuevo-usuario')).toBeVisible();

    // Llenar los campos con datos únicos
    const nombreUnico = `Bartender ${Date.now()}`;
    const pinUnico = String(Math.floor(1000 + Math.random() * 9000));

    await page.locator('#nuevo-nombre').fill(nombreUnico);
    await page.locator('#nuevo-rol').selectOption('BARTENDER');
    await page.locator('#nuevo-pin').fill(pinUnico);

    // Guardar y capturar la respuesta del servidor
    const [respuesta] = await Promise.all([
      page.waitForResponse(r => r.request().method() === 'POST' && r.url().includes('/usuarios')),
      page.getByRole('button', { name: 'Guardar Colaborador' }).click(),
    ]);
    console.log('Guardar usuario:', respuesta.status(), await respuesta.text());
    expect(respuesta.ok()).toBeTruthy();

    // Verificar que se cierra
    await expect(page.locator('#modal-nuevo-usuario')).toBeHidden({ timeout: 15000 });
  });
});