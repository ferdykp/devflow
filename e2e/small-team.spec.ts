import { test, expect } from './fixtures';
import type { Page, TestInfo } from '@playwright/test';

const password = 'TimKecil123!';

async function register(page: Page, name: string, email: string) {
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill(name);
  await page.getByLabel('Alamat email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill(password);
  await page.getByLabel('Ulangi kata sandi').fill(password);
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

async function createProject(page: Page) {
  const ownerEmail = `team-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`;
  await register(page, 'Pemilik Tim', ownerEmail);
  await page.goto('/projects');
  await page.getByRole('link', { name: 'Proyek baru', exact: true }).click();
  await page.getByLabel('Ruang kerja', { exact: true }).selectOption({ label: 'Personal' });
  await page.getByLabel('Nama proyek', { exact: true }).fill('Proyek tim kecil');
  await page.getByRole('button', { name: 'Buat proyek', exact: true }).click();
  await expect(page).toHaveURL(/\/projects\/\d+$/);
  return { project: new URL(page.url()).pathname, ownerEmail };
}

async function capture(page: Page, testInfo: TestInfo, name: string) {
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1)).toBe(true);
  await page.evaluate(async () => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
    await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
  });
  await page.screenshot({ path: testInfo.outputPath(name), fullPage: true, animations: 'disabled' });
}

test('dokumen menjadi tugas terpilih, bisa diberi penanggung jawab, difilter, dan didiskusikan', async ({ page }, testInfo) => {
  const { project } = await createProject(page);
  await page.goto(`${project}/task-imports/create`);
  await page.getByLabel('Judul dokumen (opsional)', { exact: true }).fill('PRD akun');
  await page.getByLabel('Isi dokumen', { exact: true }).fill('## Akun\n- Buat halaman masuk\n  - Email harus valid\n- Buat profil\n- Buat keluar');
  await page.getByLabel('Setelah dokumen dibaca', { exact: true }).selectOption('review');
  await page.getByRole('button', { name: 'Proses dokumen', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'Tinjau draf tugas', exact: true })).toBeVisible();
  const reviewUrl = page.url();
  await expect(page.getByText('Belum ada tugas yang dibuat.', { exact: false })).toBeVisible();
  const first = page.getByRole('region', { name: 'Draf tugas 1', exact: true });
  await first.getByLabel('Judul tugas', { exact: true }).fill('Bangun halaman masuk');
  await first.getByLabel('Prioritas', { exact: true }).selectOption('high');
  await first.getByLabel('Penanggung jawab', { exact: true }).selectOption({ label: 'Pemilik Tim' });
  await first.getByLabel('Tenggat (opsional)', { exact: true }).fill('2027-02-10');
  const second = page.getByRole('region', { name: 'Draf tugas 2', exact: true });
  await second.getByLabel('Judul tugas', { exact: true }).fill('');
  await page.getByRole('checkbox', { name: 'Buat tugas 2', exact: true }).uncheck();
  await expect(second.getByLabel('Judul tugas', { exact: true })).toBeDisabled();
  await capture(page, testInfo, 'review-draf.png');
  await page.getByRole('button', { name: 'Buat tugas terpilih', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'Tugas berhasil dibuat', exact: true })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Buat profil', exact: true })).toHaveCount(0);
  await page.getByRole('link', { name: 'Buka tugas', exact: true }).click();
  await page.getByText('Cari dan filter tugas', { exact: true }).click();
  const filters = page.getByRole('form', { name: 'Filter tugas' });
  await filters.getByLabel('Prioritas', { exact: true }).selectOption('high');
  await filters.getByLabel('Penanggung jawab', { exact: true }).selectOption('me');
  await filters.getByRole('button', { name: 'Terapkan', exact: true }).click();
  await expect(page.locator('.df-task-row')).toHaveCount(1);
  await page.getByRole('link', { name: 'Bangun halaman masuk', exact: true }).click();
  await expect(page.getByText('PRD akun · Versi 1', { exact: true })).toBeVisible();
  await expect(page.getByLabel('Penanggung jawab', { exact: true })).toHaveValue(/\d+/);
  await page.getByLabel('Tulis komentar', { exact: true }).fill('Validasi email selesai, lanjut uji alur masuk.');
  await page.getByRole('button', { name: 'Kirim komentar', exact: true }).click();
  await expect(page.getByText('Validasi email selesai, lanjut uji alur masuk.', { exact: true })).toBeVisible();
  await capture(page, testInfo, 'detail-diskusi.png');
  await page.goto(reviewUrl);
  await expect(page.getByRole('heading', { name: 'Tugas berhasil dibuat', exact: true })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Bangun halaman masuk', exact: true })).toHaveCount(1);
});

test('undangan menghubungkan dua akun dan pencabutan anggota mencabut akses proyek', async ({ page }, testInfo) => {
  const { project, ownerEmail } = await createProject(page);
  const memberEmail = `member-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`;
  await page.goto('/projects');
  await page.getByRole('link', { name: 'Personal', exact: true }).click();
  const teamUrl = page.url();
  await page.getByLabel('Email anggota', { exact: true }).fill(memberEmail);
  await page.getByRole('button', { name: 'Buat undangan', exact: true }).click();
  const invitationUrl = await page.getByLabel('Tautan undangan', { exact: true }).inputValue();
  expect(invitationUrl).toContain('/invitations/');
  await capture(page, testInfo, 'undangan-anggota.png');
  await page.context().clearCookies();
  await register(page, 'Anggota Tim', memberEmail);
  await page.goto(invitationUrl);
  await page.getByRole('button', { name: 'Terima undangan', exact: true }).click();
  await expect(page.getByText('Anggota Tim', { exact: true }).first()).toBeVisible();
  await expect(page.getByRole('button', { name: 'Buat undangan' })).toHaveCount(0);
  await page.goto('/projects');
  await page.getByRole('link', { name: 'Proyek baru', exact: true }).click();
  await expect(page.locator('select[name="workspace_id"] option', { hasText: 'Personal · Pemilik Tim' })).toHaveCount(1);
  await page.goto(project);
  await expect(page.getByRole('heading', { name: 'Proyek tim kecil', exact: true })).toBeVisible();
  await page.context().clearCookies();
  await page.goto('/login');
  await page.getByLabel('Alamat email').fill(ownerEmail);
  await page.getByLabel('Kata sandi', { exact: true }).fill(password);
  await page.getByRole('button', { name: /Masuk/ }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await page.goto(teamUrl);
  await page.getByRole('button', { name: 'Keluarkan anggota', exact: true }).click();
  await page.getByRole('dialog', { name: 'Konfirmasi tindakan' }).getByRole('button', { name: 'Lanjutkan', exact: true }).click();
  await expect(page.getByText('Anggota Tim', { exact: true })).toHaveCount(0);
  await page.context().clearCookies();
  await page.goto('/login');
  await page.getByLabel('Alamat email').fill(memberEmail);
  await page.getByLabel('Kata sandi', { exact: true }).fill(password);
  await page.getByRole('button', { name: /Masuk/ }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
  const response = await page.goto(project);
  expect(response?.status()).toBe(404);
});

test('unggah Markdown langsung membuat to-do berkelompok, urut, dan checklist yang tersimpan', async ({ page }, testInfo) => {
  const { project } = await createProject(page);
  await page.goto(`${project}/task-imports/create`);
  await page.getByLabel('Judul dokumen (opsional)', { exact: true }).fill('Panduan website');
  const markdown = '# PRD Website\n## Tujuan\n- Memudahkan pelanggan\n## Tahap 1: Persiapan\n1. Siapkan proyek\n   - Atur database\n   - Jalankan migrasi\n2. Siapkan CI\n## Tahap 2: Implementasi\n### Buat halaman masuk\n- Validasi email\n- Uji kredensial salah';
  await page.getByLabel('Atau unggah berkas', { exact: true }).setInputFiles({ name: 'panduan.md', mimeType: 'text/markdown', buffer: Buffer.from(markdown) });
  await expect(page.getByLabel('Setelah dokumen dibaca', { exact: true })).toHaveValue('automatic');
  await page.getByRole('button', { name: 'Proses dokumen', exact: true }).click();
  await expect(page).toHaveURL(/section=tasks&view=list&plan=\d+$/);
  const planUrl = page.url();
  const titles = page.locator('.df-task-row .df-task-title');
  await expect(titles).toHaveText(['Siapkan proyek', 'Siapkan CI', 'Buat halaman masuk']);
  await expect(page.getByRole('heading', { name: 'Tahap 1: Persiapan', exact: true })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Tahap 2: Implementasi', exact: true })).toBeVisible();
  await expect(page.getByText('Memudahkan pelanggan', { exact: true })).toHaveCount(0);
  await capture(page, testInfo, 'todo-otomatis.png');
  await page.getByRole('link', { name: 'Siapkan proyek', exact: true }).click();
  const migration = page.getByRole('checkbox', { name: 'Jalankan migrasi', exact: true });
  await expect(migration).toHaveAttribute('aria-checked', 'false');
  await migration.click();
  await expect(migration).toHaveAttribute('aria-checked', 'true');
  await page.reload();
  await expect(migration).toHaveAttribute('aria-checked', 'true');
  await expect(page.getByText('1/2 selesai', { exact: true })).toBeVisible();
  await capture(page, testInfo, 'checklist-tersimpan.png');
  await page.goto(planUrl);
  await page.getByLabel('Pilihan tugas Siapkan proyek', { exact: true }).click();
  await page.getByLabel('Status tugas Siapkan proyek', { exact: true }).selectOption('done');
  await expect(titles).toHaveText(['Siapkan proyek', 'Siapkan CI', 'Buat halaman masuk']);
  await page.getByRole('link', { name: 'Papan', exact: true }).click();
  await expect(page).toHaveURL(/view=board&plan=\d+$/);
  await page.getByRole('link', { name: 'Daftar', exact: true }).click();
  await expect(titles).toHaveText(['Siapkan proyek', 'Siapkan CI', 'Buat halaman masuk']);
  await page.goto(`${project}/task-imports/create`);
  await page.getByLabel('Isi dokumen', { exact: true }).fill(markdown);
  await page.getByRole('button', { name: 'Proses dokumen', exact: true }).click();
  await expect(titles).toHaveCount(3);
});

test('dokumen besar ditinjau per halaman dan seluruh tugas dibuat sesuai urutan', async ({ page }, testInfo) => {
  const { project } = await createProject(page);
  await page.goto(`${project}/task-imports/create`);
  await page.getByLabel('Atau unggah berkas', { exact: true }).setInputFiles({
    name: 'rencana-besar.md', mimeType: 'text/markdown',
    buffer: Buffer.from(Array.from({ length: 75 }, (_, i) => `- Pekerjaan ${i + 1}`).join('\n')),
  });
  await page.getByLabel('Setelah dokumen dibaca', { exact: true }).selectOption('review');
  await page.getByRole('button', { name: 'Proses dokumen', exact: true }).click();
  const reviewUrl = page.url();
  await expect(page.locator('[data-large-draft]')).toHaveCount(25);
  await expect(page.locator('[data-large-draft] h2').first()).toHaveText('Pekerjaan 1');
  await capture(page, testInfo, 'large-document-preview.png');
  await page.goto(`${reviewUrl}?page=2`);
  await expect(page.locator('[data-large-draft] h2').first()).toHaveText('Pekerjaan 26');
  await page.getByRole('button', { name: 'Buat semua 75 tugas', exact: true }).click();
  await expect(page.locator('.df-task-row')).toHaveCount(60);
  const tasksUrl = page.url();
  await page.goto(`${tasksUrl}&page=2`);
  await expect(page.locator('.df-task-row')).toHaveCount(15);
  await expect(page.getByRole('link', { name: 'Pekerjaan 75', exact: true })).toBeVisible();
});


test('to-do cepat cukup judul dan selesai dapat dibatalkan setelah refresh', async ({ page }, testInfo) => {
  const { project } = await createProject(page);
  await expect(page.getByRole('link', { name: 'Daftar', exact: true })).toHaveAttribute('aria-current', 'page');
  const quick = page.getByRole('form', { name: 'Tambah to-do cepat', exact: true });
  await expect(quick).toBeVisible();
  await quick.getByRole('textbox', { name: 'Tambah to-do', exact: true }).fill('Kirim rancangan ke tim');
  await quick.getByRole('textbox').press('Enter');
  await expect(page.locator('.df-task-row')).toHaveCount(1);
  await expect(quick.getByRole('textbox')).toBeFocused();
  await expect(page.getByLabel('Status tugas Kirim rancangan ke tim')).toHaveValue('todo');
  const done = page.getByRole('checkbox', { name: 'Selesai: Kirim rancangan ke tim', exact: true });
  await done.click();
  await expect(done).toBeChecked();
  await page.reload();
  await expect(done).toBeChecked();
  await done.click();
  await expect(done).not.toBeChecked();
  await capture(page, testInfo, 'simple-todo-list.png');
  await page.goto('/dashboard');
  await capture(page, testInfo, 'unified-home.png');
  await page.goto('/projects');
  await capture(page, testInfo, 'unified-projects.png');
  await page.goto(`${project}/task-imports/create`);
  await capture(page, testInfo, 'unified-markdown-import.png');
});

test('halaman pembuatan terpisah, proyek bisa diedit dan hapus perlu konfirmasi', async ({ page }, testInfo) => {
  const { project } = await createProject(page);
  await page.getByRole('form', { name: 'Tambah to-do cepat' }).getByRole('textbox').fill('Tugas yang tetap tersimpan');
  await page.getByRole('form', { name: 'Tambah to-do cepat' }).getByRole('textbox').press('Enter');
  await expect(page.locator('.df-task-row')).toHaveCount(1);
  await page.goto('/projects');
  await expect(page.locator('form[action$="/projects"][method="POST"]')).toHaveCount(0);
  await expect(page.locator('form[action$="/workspaces"][method="POST"]')).toHaveCount(0);
  await capture(page, testInfo, 'projects-only.png');
  await page.getByRole('link', { name: 'Edit proyek Proyek tim kecil', exact: true }).click();
  await expect(page).toHaveURL(/\/edit$/);
  await page.getByLabel('Nama proyek', { exact: true }).fill('Rencana tim diperbarui');
  await page.getByLabel('Deskripsi', { exact: true }).fill('Fokus pada pekerjaan utama tim.');
  await page.getByText('Opsi tambahan', { exact: true }).click();
  await page.getByLabel('Status', { exact: true }).selectOption('paused');
  await page.getByLabel('Target selesai').fill('2026-12-01');
  await page.getByRole('button', { name: 'Simpan pengaturan', exact: true }).click();
  await expect(page.getByLabel('Nama proyek', { exact: true })).toHaveValue('Rencana tim diperbarui');
  await page.reload();
  await page.getByText('Opsi tambahan', { exact: true }).click();
  await expect(page.getByLabel('Status', { exact: true })).toHaveValue('paused');
  await expect(page.getByLabel('Target selesai')).toHaveValue('2026-12-01');
  await capture(page, testInfo, 'project-edit.png');
  await page.getByRole('button', { name: 'Hapus proyek', exact: true }).click();
  const dialog = page.getByRole('dialog', { name: 'Konfirmasi tindakan' });
  await expect(dialog).toContainText('Rencana tim diperbarui');
  await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
  await page.getByRole('link', { name: 'Kembali ke tugas', exact: true }).click();
  await expect(page.getByRole('heading', { name: 'Rencana tim diperbarui', exact: true })).toBeVisible();
  await expect(page.locator('.df-task-row')).toHaveCount(1);
  await page.getByRole('link', { name: 'Edit proyek', exact: true }).click();
  await page.getByRole('button', { name: 'Hapus proyek', exact: true }).click();
  await dialog.getByRole('button', { name: 'Lanjutkan', exact: true }).click();
  await expect(page).toHaveURL(/\/projects$/);
  await expect(page.getByRole('heading', { name: 'Rencana tim diperbarui', exact: true })).toHaveCount(0);
  expect((await page.goto(project))?.status()).toBe(404);
});
