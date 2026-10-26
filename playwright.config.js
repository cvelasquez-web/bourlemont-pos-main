import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  
  // ✅ IMPORTANTE: false para que las pruebas corran una por una y no choquen en la BD
  fullyParallel: false, 
  
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  
  use: {
    /* 1. Aquí le decimos que use tu servidor local */
    baseURL: 'http://127.0.0.1:8000',
    
    /* 2. Quitamos headless para ver el navegador abrirse */
    headless: false, 
    
    /* 3. Toma captura si falla */
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // ✅ NUEVA SECCIÓN: Enciende el servidor y limpia la BD automáticamente
  webServer: {
    command: 'php artisan migrate:fresh --seed && php artisan serve',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000, 
  },
});