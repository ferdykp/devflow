import { test, expect } from './fixtures';
import { completeOnboarding } from './onboarding';
import type { Page, TestInfo } from '@playwright/test';

async function capture(page: Page, testInfo: TestInfo, name: string) {
  await page.screenshot({ path: testInfo.outputPath(name), fullPage: true, animations: 'disabled' });
  // Compare only after the release owner has reviewed and committed a baseline.
  // Missing baselines fail; this path never accepts them automatically.
  if (process.env.DEVFLOW_VISUAL_COMPARE === '1') {
    await expect(page).toHaveScreenshot(name, { fullPage: true, animations: 'disabled' });
  }
}

async function createProject(page: Page) {
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill('Penguji DevFlow');
  await page.getByLabel('Alamat email').fill(`qa-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`);
  await page.getByLabel('Kata sandi', { exact: true }).fill('SandiUji123!');
  await page.getByLabel('Ulangi kata sandi').fill('SandiUji123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await completeOnboarding(page);
  await page.goto('/projects');
  await page.getByRole('link', { name: 'Proyek baru', exact: true }).click();
  await page.locator('select[name="workspace_id"]').selectOption({ label: 'Personal' });
  await page.getByLabel('Nama proyek', { exact: true }).fill('Proyek pengujian');
  await page.getByRole('button', { name: 'Buat proyek', exact: true }).click();
  await expect(page).toHaveURL(/\/projects\/\d+$/);
  return new URL(page.url()).pathname;
}

async function createTask(page: Page, project: string, title = 'Tugas pengujian') {
  await page.goto(`${project}?section=tasks`);
  await page.getByRole('button', { name: 'Tugas baru', exact: true }).click();
  await page.locator('#new-task-panel').getByLabel('Nama tugas').fill(title);
  await page.getByRole('button', { name: 'Buat tugas', exact: true }).click();
  await expect(page.getByText(title, { exact: true })).toBeVisible();
}

test('navigasi utama jelas dan menu tambahan dapat ditutup dengan keyboard atau klik luar', async ({ page }) => {
  await createProject(page);
  const navigation = page.getByRole('navigation', { name: 'Bagian proyek', exact: true });
  for (const label of (page.viewportSize()!.width < 640 ? ['Tugas', 'Dokumen'] : ['Ringkasan', 'Tugas', 'Dokumen', 'Lanjutkan'])) {
    await expect(navigation.getByRole('link', { name: label, exact: true })).toBeInViewport();
  }
  const trigger = page.getByLabel('Menu proyek lainnya', { exact: true });
  const panel = page.locator('[data-project-menu-panel]');
  await trigger.click();
  await expect(panel.getByRole('link', { name: 'Catatan', exact: true })).toBeVisible();
  const githubLink = panel.getByRole('link', { name: 'GitHub', exact: true });
  await expect(githubLink).toBeVisible();
  await expect.poll(async () => {
    const bounds = await panel.boundingBox();
    return !!bounds && bounds.x >= 0 && bounds.y >= 0 && bounds.y + bounds.height <= page.viewportSize()!.height;
  }).toBe(true);
  await githubLink.click();
  await expect(page).toHaveURL(/section=github/);
  await expect(page.getByRole('heading', { name: 'GitHub', exact: true })).toBeVisible();

  await page.goto(`${new URL(page.url()).pathname}?section=tasks`);
  const tasksTrigger = page.locator('[data-project-menu] summary');
  await tasksTrigger.click();
  await page.keyboard.press('Escape');
  await expect(panel).toBeHidden();
  await expect(tasksTrigger).toBeFocused();
  await tasksTrigger.click();
  await page.getByRole('banner').click({ position: { x: 180, y: 32 } });
  await expect(panel).toBeHidden();
});

test('status alternatif tersimpan, konfirmasi batal aman, dan penghapusan bekerja', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', error => errors.push(error.message));
  const project = await createProject(page);
  await createTask(page, project);
  await expect(page.getByRole('link', { name: 'Papan', exact: true })).toHaveAttribute('aria-current', 'page');
  await page.getByRole('link', { name: 'Daftar', exact: true }).click();
  await page.getByLabel('Pilihan tugas Tugas pengujian', { exact: true }).click();
  await page.getByLabel('Status tugas Tugas pengujian', { exact: true }).scrollIntoViewIfNeeded();
  await page.getByLabel('Status tugas Tugas pengujian', { exact: true }).selectOption('in_review');
  await expect(page.getByLabel('Status tugas Tugas pengujian')).toHaveValue('in_review');
  await page.reload();
  await expect(page.getByLabel('Status tugas Tugas pengujian')).toHaveValue('in_review');
  await page.getByLabel('Pilihan tugas Tugas pengujian', { exact: true }).click();
  await page.getByRole('link', { name: 'Ubah Tugas pengujian', exact: true }).click();
  const editor = page.getByRole('region', { name: 'Ubah tugas', exact: true });
  await expect(editor).toBeVisible();
  await expect(editor.getByLabel('Nama tugas')).toHaveValue('Tugas pengujian');
  await editor.getByLabel('Deskripsi').fill('Deskripsi diperbarui');
  await editor.getByLabel('Prioritas').selectOption('high');
  await editor.getByRole('button', { name: 'Simpan perubahan' }).click();
  await expect(page.locator('.df-task-list').getByText('Deskripsi diperbarui', { exact: true })).toBeVisible();
  await page.reload();
  await page.getByLabel('Pilihan tugas Tugas pengujian', { exact: true }).click();
  await page.getByRole('link', { name: 'Ubah Tugas pengujian', exact: true }).click();
  await expect(editor.getByLabel('Prioritas')).toHaveValue('high');
  await editor.getByRole('link', { name: 'Batal', exact: true }).click();
  await expect(page).toHaveURL(/section=tasks&view=list$/);
  await expect(editor).toHaveCount(0);
  await page.getByLabel('Pilihan tugas Tugas pengujian', { exact: true }).click();
  await page.getByRole('button', { name: 'Hapus Tugas pengujian', exact: true }).click();
  const dialog = page.getByRole('dialog', { name: 'Konfirmasi tindakan' });
  await expect(dialog).toBeVisible();
  await dialog.getByRole('button', { name: 'Batal', exact: true }).click();
  await expect(page.getByText('Tugas pengujian', { exact: true })).toBeVisible();
  await page.getByRole('button', { name: 'Hapus Tugas pengujian', exact: true }).click();
  await dialog.getByRole('button', { name: 'Ya, lanjutkan', exact: true }).click();
  await expect(page.getByText('Tugas pengujian', { exact: true })).toHaveCount(0);
  expect(errors).toEqual([]);
});

test('drag and drop tersimpan dan kegagalan request mengembalikan kartu', async ({ page, isMobile }) => {
  test.skip(isMobile, 'Perangkat sentuh memakai kontrol status alternatif.');
  const project = await createProject(page);
  await createTask(page, project);
  for (const title of ['Tugas kedua', 'Tugas ketiga', 'Tugas keempat', 'Tugas kelima', 'Tugas keenam']) {
    await createTask(page, project, title);
  }
  await page.getByRole('link', { name: 'Papan', exact: true }).click();
  const notStartedCards = page.locator('[data-task-column="not_started"] [data-task-card]');
  await expect(notStartedCards).toHaveCount(6);
  const initialOrder = await notStartedCards.locator('[data-task-status-title]').allTextContents();
  const reordered = page.waitForResponse(response => response.request().method() === 'POST'
    && new URL(response.url()).pathname === '/task-board/reorder' && response.status() === 200);
  await notStartedCards.first().getByRole('button', { name: /Pindahkan .* ke bawah/ }).click();
  await reordered;
  await expect.poll(() => notStartedCards.locator('[data-task-status-title]').allTextContents())
    .toEqual([initialOrder[1], initialOrder[0], ...initialOrder.slice(2)]);
  const taskToMove = notStartedCards.last();
  const movedTaskTitle = (await taskToMove.locator('[data-task-status-title]').textContent())!.trim();
  const taskToMoveId = await taskToMove.getAttribute('data-task-id');
  const taskToMoveCard = page.locator(`[data-task-card][data-task-id="${taskToMoveId}"]`);
  let boardNavigations = 0;
  page.on('request', request => {
    if (request.isNavigationRequest() && request.frame() === page.mainFrame()) boardNavigations++;
  });
  const moveRequest = page.waitForRequest(request => request.method() === 'POST'
    && new URL(request.url()).pathname === '/task-board/reorder');
  const successfulMove = page.waitForResponse(response => response.request().method() === 'POST'
    && new URL(response.url()).pathname === '/task-board/reorder'
    && response.status() === 200);
  await taskToMoveCard.evaluate(card => {
    const target = document.querySelector('[data-task-dropzone="in_progress"]');
    if (!target) throw new Error('In-progress column was not found.');
    const dataTransfer = new DataTransfer();
    card.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer }));
    target.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer }));
    target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer }));
  });
  await successfulMove;
  const movePayload = (await moveRequest).postDataJSON();
  expect(movePayload.active_task_id).toBe(Number(taskToMoveId));
  expect(movePayload.status).toBe('in_progress');
  await expect(page.locator('[data-confirm-dialog]')).toBeHidden();
  await expect(page.locator('[data-task-column="in_progress"] [data-task-card]')).toHaveCount(1);
  await expect(page.getByLabel(`Status tugas ${movedTaskTitle}`)).toHaveValue('in_progress');
  expect(boardNavigations).toBe(0);
  await page.route('**/task-board/reorder', route => route.request().method() === 'POST'
    ? route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ message: 'Layanan tidak tersedia' }) })
    : route.continue());
  const failedMoveResponse = page.waitForResponse(response =>
    response.status() === 503 && new URL(response.url()).pathname === '/task-board/reorder');
  await page.locator('[data-task-column="in_progress"] [data-task-card]').evaluate(card => {
    const target = document.querySelector('[data-task-dropzone="todo"]');
    if (!target) throw new Error('Todo column was not found.');
    const dataTransfer = new DataTransfer();
    card.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer }));
    target.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer }));
    target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer }));
  });
  await failedMoveResponse;
  await expect(page.locator('[data-task-move-error]')).toBeVisible();
  await expect(page.locator('[data-task-column="in_progress"] [data-task-card]')).toHaveCount(1);
  await page.clock.install();
  await page.clock.fastForward(10_000);
  await expect(page.locator('[data-task-move-error]')).toBeVisible();
  await page.locator('[data-task-move-error]').getByRole('button', { name: 'Tutup notifikasi' }).click();
  await page.clock.fastForward(300);
  await expect(page.locator('[data-task-move-error]')).toHaveCount(0);
  await page.reload();
  await expect(page.getByLabel(`Status tugas ${movedTaskTitle}`)).toHaveValue('in_progress');
});

test('pencarian, menu mobile, PRD dan pengaturan bekerja', async ({ page, isMobile }) => {
  const project = await createProject(page);
  if (isMobile) {
    await page.getByRole('button', { name: 'Buka navigasi', exact: true }).click();
    await expect(page.locator('[data-sidebar]')).toHaveAttribute('data-open', '');
    await page.getByRole('button', { name: 'Tutup navigasi' }).click();
    await expect(page.getByRole('button', { name: 'Buka navigasi' })).toBeFocused();
  }
  await page.keyboard.press('Control+k');
  const palette = page.getByRole('dialog', { name: 'Pencarian cepat' });
  await expect(palette).toBeVisible();
  await page.getByLabel('Cari halaman').fill('Fokus');
  await page.keyboard.press('Enter');
  await expect(page).toHaveURL(/\/focus$/);
  await page.goto(`${project}?section=prd`);
  await page.getByLabel('Judul dokumen').fill('Kebutuhan pengujian');
  await page.getByLabel('Isi PRD', { exact: true }).fill('# Kebutuhan\nData harus tersimpan.');
  await page.getByRole('button', { name: 'Buat draf', exact: true }).click();
  await page.getByText('Versi 1 · Draf', { exact: true }).click();
  await expect(page.getByText('Data harus tersimpan.', { exact: false }).first()).toBeVisible();
  await page.getByRole('button', { name: 'Setujui versi ini' }).click();
  await page.getByRole('dialog', { name: 'Konfirmasi tindakan' }).getByRole('button', { name: 'Setujui versi ini' }).click();
  await expect(page.getByText('Versi 1 · Disetujui', { exact: true })).toBeVisible();
  await page.getByLabel('Isi versi baru').fill('# Kebutuhan baru');
  await page.getByRole('button', { name: 'Buat versi baru', exact: true }).click();
  await expect(page.getByText('Versi 2 · Draf', { exact: true })).toBeVisible();
  await page.goto(`${project}?section=settings`);
  await page.getByLabel('Nama proyek', { exact: true }).fill('Proyek diperbarui');
  await page.getByRole('button', { name: 'Simpan pengaturan' }).click();
  await expect(page.locator('[data-confirm-dialog]')).toBeHidden();
  await expect(page.getByRole('heading', { name: 'Proyek diperbarui', exact: true })).toBeVisible();
});

test('CSRF dan pembatasan login tetap aktif di browser', async ({ page }) => {
  await page.goto('/login');
  const csrf = await page.request.post('/login', { form: { email: 'csrf@example.test', password: 'wrong' } });
  expect(csrf.status()).toBe(419);
  const token = await page.locator('input[name="_token"]').inputValue();
  for (let attempt = 0; attempt < 5; attempt++) {
    const response = await page.request.post('/login', {
      form: { _token: token, email: 'throttle@example.test', password: 'wrong' }, maxRedirects: 0,
    });
    expect(response.status()).toBe(302);
  }
  const blocked = await page.request.post('/login', {
    form: { _token: token, email: 'throttle@example.test', password: 'wrong' }, maxRedirects: 0,
  });
  expect(blocked.status()).toBe(429);
});

for (const width of [320, 375, 430, 768, 1280, 1920, 2560, 3840]) {
  test(`audit viewport ${width}px dan tangkapan kandidat visual`, async ({ page, isMobile }, testInfo) => {
    test.skip(isMobile, 'Matriks viewport dijalankan sekali pada proyek desktop.');
    test.setTimeout(120_000);
    await page.setViewportSize({ width, height: width <= 430 ? 568 : 900 });
    const errors: string[] = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const route of ['/login', '/register']) {
      await page.goto(route);
      await capture(page, testInfo, `${route.slice(1)}-${width}.png`);
    }
    const project = await createProject(page);
    await createTask(page, project);
    for (const section of ['overview', 'tasks', 'activity', 'notes', 'milestones', 'prd', 'scope', 'backlog', 'github', 'settings']) {
      await page.goto(`${project}?section=${section}`);
      await expect(page.getByRole('navigation', { name: 'Bagian proyek' })).toBeVisible();
      if (width <= 850) await expect(page.locator('[data-sidebar-close]')).not.toBeVisible();
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1), section).toBe(true);
      await capture(page, testInfo, `${section}-${width}.png`);
    }
    for (const route of ['/dashboard', '/projects', '/projects/create', '/workspaces/create', '/focus', `${project}/edit`, `${project}/resume`]) {
      await page.goto(route);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1), route).toBe(true);
      const screen = route === '/projects/create' ? 'project-create' : route === '/workspaces/create' ? 'workspace-create' : route.split('/').pop();
      await capture(page, testInfo, `${screen}-${width}.png`);
    }
    expect(errors).toEqual([]);
  });
}
