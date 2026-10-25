import { test, expect } from './fixtures';
import type { Page } from '@playwright/test';

async function createProject(page: Page) {
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill('Penguji Dialog');
  await page.getByLabel('Alamat email').fill(`dialog-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`);
  await page.getByLabel('Kata sandi', { exact: true }).fill('DialogAman123!');
  await page.getByLabel('Ulangi kata sandi').fill('DialogAman123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await page.goto('/projects/create');
  await page.getByLabel('Nama proyek', { exact: true }).fill('Proyek '+ 'A'.repeat(143));
  await page.getByRole('button', { name: 'Buat proyek', exact: true }).click();
  await expect(page).toHaveURL(/\/projects\/\d+$/);
  return new URL(page.url()).pathname;
}

async function expectCentered(page: Page) {
  const dialog = page.getByRole('dialog', { name: 'Konfirmasi tindakan', exact: true });
  await expect(dialog).toBeVisible();
  await expect.poll(async () => {
    const box = await dialog.boundingBox();
    const viewport = page.viewportSize()!;
    return !!box && Math.abs(box.x + box.width / 2 - viewport.width / 2) < 2
      && Math.abs(box.y + box.height / 2 - viewport.height / 2) < 2
      && box.x >= 15 && box.y >= 15
      && box.x + box.width <= viewport.width - 15 && box.y + box.height <= viewport.height - 15;
  }).toBe(true);
  expect(await dialog.evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true);
}

test('konfirmasi berada di tengah saat scroll dan resize; batal aman serta konfirmasi mengirim sekali', async ({ page }, testInfo) => {
  const errors: string[] = [];
  page.on('pageerror', error => errors.push(error.message));
  const project = await createProject(page);
  await page.goto(`${project}/edit`);
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1)).toBe(true);
  let deletes = 0;
  page.on('request', request => {
    if (new URL(request.url()).pathname === project && request.method() === 'POST' && request.postData()?.includes('_method=DELETE')) deletes++;
  });
  const opener = page.getByRole('button', { name: 'Hapus proyek', exact: true });
  const dialog = page.getByRole('dialog', { name: 'Konfirmasi tindakan', exact: true });
  for (const [index, viewport] of [{ width: 320, height: 568 }, { width: 568, height: 320 }, { width: 1280, height: 800 }, { width: 3840, height: 900 }].entries()) {
    await page.setViewportSize(viewport);
    await opener.click();
    await expectCentered(page);
    await expect(dialog.getByRole('button', { name: 'Batal', exact: true })).toBeFocused();
    if (index === 0) await page.screenshot({ path: testInfo.outputPath('confirmation-mobile.png'), fullPage: false });
    if (index === 2) await page.screenshot({ path: testInfo.outputPath('confirmation-desktop.png'), fullPage: false });
    if (index % 2 === 0) await page.keyboard.press('Escape');
    else await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
    await expect(dialog).toBeHidden();
    await expect(opener).toBeFocused();
    await expect(page.locator('body')).not.toHaveCSS('overflow', 'hidden');
  }
  expect(deletes).toBe(0);
  await opener.click();
  await page.setViewportSize({ width: 375, height: 667 });
  await expectCentered(page);
  await dialog.getByRole('button', { name: 'Lanjutkan', exact: true }).click();
  await expect(page).toHaveURL(/\/projects$/);
  expect(deletes).toBe(1);
  expect(errors).toEqual([]);
});

test('batal konfirmasi tidak mengunci form dan persetujuan tetap dikirim setelah konfirmasi', async ({ page }) => {
  const project = await createProject(page);
  await page.goto(`${project}?section=prd`);
  await page.getByLabel('Judul dokumen', { exact: true }).fill('Dokumen uji konfirmasi');
  await page.getByLabel('Isi PRD', { exact: true }).fill('# Rencana\n- Buat fitur');
  await page.getByRole('button', { name: 'Buat draf', exact: true }).click();
  await page.getByText('Versi 1 · Draf', { exact: true }).click();
  const approve = page.getByRole('button', { name: 'Setujui versi ini', exact: true });
  const dialog = page.getByRole('dialog', { name: 'Konfirmasi tindakan', exact: true });
  await approve.click();
  await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
  await expect(approve).not.toHaveAttribute('aria-disabled', 'true');
  await expect(approve).toBeFocused();
  await approve.click();
  await expectCentered(page);
  await dialog.getByRole('button', { name: 'Lanjutkan', exact: true }).click();
  await expect(page.getByText('Versi 1 · Disetujui', { exact: true })).toBeVisible();
  await expect(page.locator('body')).not.toHaveCSS('overflow', 'hidden');
});
