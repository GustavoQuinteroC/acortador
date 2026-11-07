import { test, expect } from '@playwright/test';

test.beforeEach(async ({ page }) => {
  page.on('pageerror', (e) => { throw e; });
  page.on('console', (m) => { if (m.type() === 'error') throw new Error(m.text()); });
});

test('la portada y la cuenta demo funcionan sin errores', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByRole('heading', { level: 1 })).toHaveText('Acortador de enlaces con QR y estadísticas');

  await page.getByRole('link', { name: 'Entrar al panel' }).click();
  await page.getByLabel('Correo').fill('demo@gustavoquintero.dev');
  await page.getByLabel('Contraseña').fill('demo-acortador');
  await page.getByRole('button', { name: 'Entrar' }).click();

  await expect(page.getByText('Demo de solo lectura')).toBeVisible();
  await expect(page.locator('canvas[data-grafica]')).toBeVisible();

  await page.goto('/panel/enlaces');
  await page.getByRole('button', { name: 'QR de /demo-cv' }).click();
  await expect(page.locator('#modal-qr-imagen')).toHaveJSProperty('complete', true);
  await expect(page.locator('#modal-qr-titulo')).toHaveText('QR de /demo-cv');
});

test('un enlace demo redirige a su destino', async ({ request }) => {
  const respuesta = await request.get('/demo-cv', { maxRedirects: 0 });
  expect(respuesta.status()).toBe(302);
  expect(respuesta.headers().location).toBe('https://gustavoquintero.dev');
});
