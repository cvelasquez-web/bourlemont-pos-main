import { test, expect } from '@playwright/test';
import { login } from './helpers';

async function abrirSesion(browser, baseURL, pin) {
  const context = await browser.newContext({ baseURL });
  const page = await context.newPage();
  await login(page, pin);
  return { context, page };
}

async function leerTotal(browser, baseURL, { excluir = ['Q 0.00'], estricto = false, etiqueta = 'lectura' } = {}) {
  const { context, page } = await abrirSesion(browser, baseURL, '2026');
  try {
    const apis = [];
    page.on('response', (r) => {
      if (r.url().includes('/api/')) apis.push(`${r.status()} ${r.url()}`);
    });

    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#stat-total')).toBeVisible({ timeout: 15000 });

    try {
      await page.waitForFunction(
        (valores) => {
          const t = document.querySelector('#stat-total')?.textContent.trim();
          return t && !valores.includes(t);
        },
        excluir,
        { timeout: 15000 }
      );
    } catch (e) {
      const visible = (await page.locator('#stat-total').textContent()).trim();
      console.log(`[${etiqueta}] Total visible al agotar la espera:`, visible);
      console.log(`[${etiqueta}] APIs:`, apis.join(' | ') || 'ninguna');
      if (estricto) throw e;
    }

    return (await page.locator('#stat-total').textContent()).trim();
  } finally {
    await context.close();
  }
}

test.describe('Dashboard Reflejo', () => {
  test('Verificar total', async ({ browser, baseURL }) => {
    test.setTimeout(120000);

    // 1. Total inicial (admin)
    const totalAntes = await leerTotal(browser, baseURL, { etiqueta: 'antes' });
    console.log('Total antes:', totalAntes);

    // 2. Venta como Mateo
    const mateo = await abrirSesion(browser, baseURL, '1234');
    try {
      const peticiones = [];
      mateo.page.on('response', async (r) => {
        const m = r.request().method();
        if (['POST', 'PUT', 'PATCH'].includes(m)) {
          const cuerpo = (await r.text().catch(() => '')).slice(0, 200);
          peticiones.push(`${m} ${r.status()} ${r.url()} ${cuerpo}`);
        }
      });

      const producto = mateo.page.locator('.product-card').first();
      await expect(producto).toBeVisible({ timeout: 15000 });
      await producto.click();
      await mateo.page.locator('#btn-cobrar').click();
      await mateo.page.locator('#confirm-payment-btn').click();
      await expect(mateo.page.locator('#ticket-modal')).toBeVisible({ timeout: 15000 });

      // Dar tiempo a que el pago termine de guardarse antes de cerrar la sesión
      await mateo.page.waitForLoadState('networkidle', { timeout: 8000 }).catch(() => {});
      await mateo.page.waitForTimeout(2000);

      console.log('Peticiones al cobrar:\n' + (peticiones.join('\n') || 'ninguna'));
    } finally {
      await mateo.context.close();
    }

    // 3. Total final (admin): debe llegar un valor distinto de Q 0.00 y del total anterior
    const totalDespues = await leerTotal(browser, baseURL, {
      excluir: ['Q 0.00', totalAntes],
      estricto: true,
      etiqueta: 'despues',
    });
    console.log('Total después:', totalDespues);
    expect(totalDespues).not.toBe(totalAntes);
  });
});