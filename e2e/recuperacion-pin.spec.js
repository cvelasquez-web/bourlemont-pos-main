import { test, expect } from '@playwright/test';

test.describe('Recuperación de PIN (Flujo de Administrador)', () => {
  test.beforeEach(async ({ page, context }) => {
    // ✅ 1. Limpiamos cualquier sesión previa para asegurar que estamos en el Login
    await context.clearCookies();
    await page.goto('/');
    await page.evaluate(() => {
        sessionStorage.clear();
        localStorage.clear();
    });
    await page.reload();
  });

  test('El administrador puede entrar con credenciales y no rompe el teclado', async ({ page }) => {
    // 1. Esperar a que el teclado del PIN sea visible
    await expect(page.locator('.pin-key[data-val="1"]')).toBeVisible();

    // 2. Cambiar a la pestaña de credenciales de administrador
    await page.locator('#tab-credential').click();

    // 3. Llenar el formulario de Admin
    await page.locator('#admin-user').fill('Carlos Bourlemont');
    await page.locator('#admin-pass').fill('admin');

    // 4. Hacer clic en Ingresar
    await page.locator('#btn-admin-login').click();

    // 5. Verificar que entramos al POS
    await page.waitForURL('**/pos');
    
    // 6. Verificar que el nombre del usuario en el sidebar sea el de Carlos
    await expect(page.locator('#header-user-name')).toContainText('Carlos Bourlemont');

    // 7. Cerrar sesión usando el botón del candado
    await page.locator('#logout-btn').click();

    // 8. Verificar que regresamos al Login y el teclado del PIN vuelve a estar visible
    await page.waitForURL('http://127.0.0.1:8000/');
    await expect(page.locator('.pin-key[data-val="1"]')).toBeVisible();
  });
});