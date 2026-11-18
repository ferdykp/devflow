import { test, expect } from './fixtures';
import { completeOnboarding } from './onboarding';

test('task context, manual scope, Change Request and PRD draft stay linked and separate', async ({ page }) => {
  const nonce = `${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill('Developer Intelligence Tester');
  await page.getByLabel('Alamat email').fill(`intelligence-${nonce}@example.test`);
  await page.getByLabel('Kata sandi', { exact: true }).fill('E2eSecurePass123!');
  await page.getByLabel('Ulangi kata sandi').fill('E2eSecurePass123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await completeOnboarding(page);

  await page.goto('/projects');
  await page.getByRole('link', { name: 'Proyek baru', exact: true }).click();
  await page.getByLabel('Ruang kerja', { exact: true }).selectOption({ label: 'Personal' });
  await page.getByLabel('Nama proyek', { exact: true }).fill(`Project ${nonce}`);
  await page.getByRole('button', { name: 'Buat proyek', exact: true }).click();
  const projectUrl = new URL(page.url()).pathname;

  await page.goto(`${projectUrl}?section=tasks`);
  await page.getByLabel('Tambah to-do', { exact: true }).fill('Implement report export');
  await page.getByRole('button', { name: 'Tambah', exact: true }).click();
  await page.getByRole('link', { name: 'Implement report export', exact: true }).click();
  await page.getByRole('link', { name: 'Siapkan konteks', exact: true }).click();
  await expect(page.getByLabel('Preview konteks pengembangan')).toHaveValue(/Implement report export/);
  await expect(page.getByLabel('Preview konteks pengembangan')).toHaveValue(/Definition of Done proyek yang eksplisit belum tercatat/);
  await page.locator('[data-context-format]').selectOption('assistant');
  await expect(page.getByLabel('Preview konteks pengembangan')).toHaveValue(/Jangan mengklaim telah melihat file lokal/);

  await page.goto(`${projectUrl}?section=scope`);
  await page.getByLabel('Termasuk cakupan', { exact: true }).fill('Reports and CSV exports');
  await page.getByLabel('Di luar cakupan', { exact: true }).fill('Billing');
  await page.getByLabel('Definition of Done', { exact: true }).fill('Tests pass and export respects access rules.');
  await page.getByRole('button', { name: 'Simpan baseline', exact: true }).click();
  await page.getByLabel('Judul usulan', { exact: true }).fill('Add CSV export');
  await page.getByRole('button', { name: 'Ajukan peninjauan', exact: true }).click();
  await page.getByLabel('Klasifikasi').selectOption('in_scope');
  await page.getByLabel('Bukti atau sumber').fill('Current manual baseline');
  await page.getByLabel('Alasan keputusan').fill('It is part of report delivery.');
  await page.getByRole('button', { name: 'Setujui untuk perencanaan', exact: true }).click();

  await page.getByLabel('Judul Change Request', { exact: true }).fill('Add CSV export changes');
  await page.getByLabel('Dari peninjauan yang disetujui (opsional)').selectOption({ label: 'Add CSV export' });
  await page.getByRole('button', { name: 'Catat Change Request', exact: true }).click();
  const changeRequest = page.locator('article').filter({ hasText: 'Add CSV export changes' });
  await changeRequest.getByPlaceholder('Alasan keputusan').fill('Approved for this project.');
  await changeRequest.getByRole('button', { name: 'Simpan keputusan', exact: true }).click();
  await changeRequest.getByPlaceholder('# Revisi PRD').fill('# Revised PRD\n\nCSV export is included.');
  await changeRequest.getByRole('button', { name: 'Buat draf PRD terpisah', exact: true }).click();
  await expect(changeRequest.getByText(/Draf PRD v\d+ dibuat · status draft/)).toBeVisible();
  await expect(changeRequest.getByText(/Persetujuan PRD belum dilakukan/)).toBeVisible();
});

test('failed note autosave keeps text and offers tab draft recovery', async ({ page }) => {
  const nonce = `${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill('Notes Recovery Tester');
  await page.getByLabel('Alamat email').fill(`recovery-${nonce}@example.test`);
  await page.getByLabel('Kata sandi', { exact: true }).fill('E2eSecurePass123!');
  await page.getByLabel('Ulangi kata sandi').fill('E2eSecurePass123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await completeOnboarding(page);

  await page.goto('/notes');
  await page.getByRole('button', { name: 'Catatan baru', exact: true }).click();
  await page.getByLabel('Judul', { exact: true }).fill(`Recovery ${nonce}`);
  await page.getByRole('button', { name: 'Simpan catatan', exact: true }).click();
  await page.getByRole('link', { name: `Recovery ${nonce}`, exact: true }).click();

  await page.route('**/notes/*', async (route) => {
    if (route.request().method() === 'POST') {
      await route.fulfill({ status: 500, contentType: 'application/json', body: '{"message":"Temporary failure"}' });
      return;
    }
    await route.continue();
  });
  const noteText = page.getByLabel('Isi catatan (Markdown didukung)', { exact: true });
  await noteText.fill('Draft terselamatkan setelah koneksi gagal.');
  await expect(page.getByText(/Draf disimpan untuk pemulihan pada tab ini/)).toBeVisible();
  await page.reload();
  await expect(page.getByText('Ada draf lokal dari tab ini yang belum tersimpan.')).toBeVisible();

  await page.unrouteAll();
  await page.getByRole('button', { name: 'Pulihkan draf', exact: true }).click();
  await expect(noteText).toHaveValue('Draft terselamatkan setelah koneksi gagal.');
  await expect(page.locator('[data-note-save-status]')).toHaveText(/^Tersimpan \d{2}[.:]\d{2}$/);
  await expect(page.locator('[data-note-recovery]')).toBeHidden();
  await page.reload();
  await expect(noteText).toHaveValue('Draft terselamatkan setelah koneksi gagal.');
});
