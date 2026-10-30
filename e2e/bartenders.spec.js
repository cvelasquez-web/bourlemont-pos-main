import { test } from '@playwright/test';
import { login } from './helpers';

test.describe('Flujo de Bartenders', () => {
  const bartenders = [
    { nombre: 'Mateo', pin: '1234' },
    { nombre: 'Sofia', pin: '2468' },
    { nombre: 'Alejandro', pin: '7788' },
  ];

  for (const bartender of bartenders) {
    test(`${bartender.nombre} inicia sesión`, async ({ page }) => {
      await login(page, bartender.pin);
    });
  }
});