import { test, expect } from './fixtures';

// Each scenario uses a freshly registered user, no fixed credentials or real user data.
test('guest access and account creation', async ({ page }) => {
  await page.goto('/dashboard');
  await expect(page).toHaveURL(/\/login/);
  await page.goto('/register');
  await expect(page.getByRole('heading', { name: 'Buat akun baru' })).toBeVisible();
  const email = `e2e-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`;
  await page.getByLabel('Nama lengkap').fill('E2E Developer');
  await page.getByLabel('Alamat email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill('E2eSecurePass123!');
  await page.getByLabel('Ulangi kata sandi').fill('E2eSecurePass123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await expect(page).toHaveURL(/\/dashboard/);
});

test('project lifecycle, task views, resume and focus', async ({ page }) => {
  const nonce = `${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
  await page.goto('/register');
  await page.getByLabel('Nama lengkap').fill('Workspace Tester');
  await page.getByLabel('Alamat email').fill(`workflow-${nonce}@example.test`);
  await page.getByLabel('Kata sandi', { exact: true }).fill('E2eSecurePass123!');
  await page.getByLabel('Ulangi kata sandi').fill('E2eSecurePass123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await expect(page).toHaveURL(/\/dashboard/);

  await page.goto('/projects');
  await page.getByRole('link', { name: 'Ruang kerja baru', exact: true }).click();
  const workspaceName = `E2E Workspace ${nonce}`;
  const workspaceForm = page.locator('form[action$="/workspaces"]');
  await expect(workspaceForm).toBeVisible();
  await workspaceForm.locator('input[name="name"]').fill(workspaceName);
  await expect(workspaceForm.locator('input[name="name"]')).toHaveValue(workspaceName);
  await Promise.all([
    page.waitForURL(/\/projects\/create\?workspace=\d+$/, { waitUntil: 'domcontentloaded' }),
    workspaceForm.getByRole('button', { name: 'Buat ruang kerja' }).click(),
  ]);
  await expect(page.getByText('Ruang kerja berhasil dibuat.')).toBeVisible();
  const workspaceSelect = page.locator('select[name="workspace_id"]');
  // Fail with a focused error instead of timing out on a missing select option.
  await expect(workspaceSelect.locator('option', { hasText: workspaceName })).toHaveCount(1);
  await workspaceSelect.selectOption({ label: workspaceName });
  await page.getByLabel('Nama proyek').fill(`E2E Project ${nonce}`);
  await page.getByRole('button', { name: 'Buat proyek' }).click();
  await expect(page.getByRole('heading', { name: `E2E Project ${nonce}` })).toBeVisible();

  await page.getByRole('navigation', { name: 'Bagian proyek' }).getByRole('link', { name: 'Tugas' }).click();
  await expect(page).toHaveURL(/section=tasks/);
  await page.getByRole('button', { name: 'Tugas baru' }).click();
  await page.locator('#new-task-panel').getByLabel('Nama tugas').fill(`E2E Task ${nonce}`);
  await page.getByRole('button', { name: 'Buat tugas' }).click();
  await expect(page.getByText(`E2E Task ${nonce}`, { exact: true })).toBeVisible();

  await page.getByRole('link', { name: 'Daftar', exact: true }).click();
  await expect(page).toHaveURL(/view=list/);
  await expect(page.getByText(`E2E Task ${nonce}`, { exact: true })).toBeVisible();
  await page.getByRole('link', { name: 'Papan', exact: true }).click();
  await expect(page).toHaveURL(/view=board/);
  await expect(page.getByText(`E2E Task ${nonce}`, { exact: true })).toBeVisible();

  if (page.viewportSize()!.width < 640) await page.getByLabel('Menu proyek lainnya', { exact: true }).click();
  await page.getByRole('navigation', { name: 'Bagian proyek' }).getByRole('link', { name: 'Lanjutkan', exact: true }).click();
  await expect(page).toHaveURL(/\/resume/);
  await expect(page.getByRole('navigation', { name: 'Bagian proyek' })).toBeVisible();
  // Resume deliberately shows the same task in Continue working and Open work.
  await expect(page.getByText(`E2E Task ${nonce}`, { exact: true }).first()).toBeVisible();

  await page.goto('/focus');
  await expect(page.getByText(`E2E Task ${nonce}`, { exact: true }).first()).toBeVisible();
  await page.getByRole('button', { name: 'Fokus hari ini' }).first().click();
  await expect(page.getByText('1/3 dipilih')).toBeVisible();
});

test('invalid login presents an error without authenticating', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Alamat email').fill('nobody@example.test');
  await page.getByLabel('Kata sandi', { exact: true }).fill('not-the-password');
  // Observe the actual POST. A successful authentication rejection is a redirect;
  // a 429 here points to the endpoint's 5/minute rate limit instead of a UI bug.
  const [loginResponse] = await Promise.all([
    page.waitForResponse(response =>
      response.request().method() === 'POST' &&
      new URL(response.url()).pathname === '/login'
    ),
    page.getByRole('button', { name: /Masuk/ }).click(),
  ]);
  expect(loginResponse.status(), 'POST /login should reject invalid credentials via redirect; 429 means rate limit').toBe(302);
  await expect(page).toHaveURL(/\/login(?:\?.*)?$/);
  await expect(page.locator('form[action$="/login"] [role="alert"]').first())
    .toContainText(/kredensial|email|kata sandi|tidak cocok|tidak valid|gagal|credentials|incorrect|match|invalid|failed/i);
});
