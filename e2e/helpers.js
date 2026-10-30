import { expect } from '@playwright/test';

export async function login(page, pin) {
  const respuestas = [];
  const pendientes = new Set();

  const onResponse = (r) => {
    if (r.request().method() === 'POST') respuestas.push(`${r.status()} ${r.url()}`);
  };
  const onRequest = (r) => pendientes.add(r.url());
  const onDone = (r) => pendientes.delete(r.url());

  page.on('response', onResponse);
  page.on('request', onRequest);
  page.on('requestfinished', onDone);
  page.on('requestfailed', onDone);

  try {
    await page.context().clearCookies();
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => {
      sessionStorage.clear();
      localStorage.clear();
    });
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('.pin-key[data-val="1"]').waitFor({ state: 'visible' });

    for (const digito of pin) {
      await page.locator(`.pin-key[data-val="${digito}"]`).click();
      await page.waitForTimeout(150);
    }

    await expect(page.locator('text=Terminal Punto de Ventas')).toBeVisible({ timeout: 20000 });
  } catch (e) {
    console.log('LOGIN FALLÓ. URL:', page.url());
    console.log('POSTs vistos:', respuestas.join(' | ') || 'ninguno');
    console.log('Peticiones sin terminar:', [...pendientes].join(' | ') || 'ninguna');
    const texto = await page.locator('body').innerText().catch(() => '(sin texto)');
    console.log('Texto en pantalla:', texto.slice(0, 300));
    throw e;
  } finally {
    page.off('response', onResponse);
    page.off('request', onRequest);
    page.off('requestfinished', onDone);
    page.off('requestfailed', onDone);
  }
}