import { test, expect } from './fixtures';
import { completeOnboarding } from './onboarding';

test('ruang kerja, tema, sidebar ringkas, dan posisi navigasi tetap konsisten di desktop dan mobile', async ({ page }) => {
  await page.goto('/register');
  const email = `adaptive-${Date.now()}-${Math.random().toString(36).slice(2)}@example.test`;
  await page.getByLabel('Nama lengkap').fill('Adaptive Tester');
  await page.getByLabel('Alamat email').fill(email);
  await page.getByLabel('Kata sandi', { exact: true }).fill('AdaptiveSecure123!');
  await page.getByLabel('Ulangi kata sandi').fill('AdaptiveSecure123!');
  await page.getByRole('button', { name: /Buat akun/ }).click();
  await completeOnboarding(page);

  const isMobile = (page.viewportSize()?.width ?? 1280) < 851;
  if (!isMobile) {
    const sidebar = page.locator('[data-sidebar]');
    const expandedWidth = (await sidebar.boundingBox())!.width;
    await page.getByRole('button', { name: 'Kecilkan sidebar' }).click();
    await expect(page.locator('html')).toHaveAttribute('data-sidebar-collapsed', '');
    await expect(page.getByRole('link', { name: 'Rencana hari ini', exact: true })).toBeVisible();
    await expect(sidebar.locator('.df-sidebar-label-short').first()).toBeVisible();
    await expect.poll(async () => (await sidebar.boundingBox())!.width).toBeLessThan(expandedWidth);
    const compactWorkspaceSwitcher = sidebar.locator('[data-sidebar-workspace-switcher]');
    await compactWorkspaceSwitcher.locator('summary').click();
    await expect(compactWorkspaceSwitcher.getByRole('link', { name: 'Proyek & tim', exact: true })).toBeVisible();
    await compactWorkspaceSwitcher.locator('summary').click();
    await page.goto('/projects');
    await expect(page.locator('[data-sidebar]')).toHaveAttribute('data-collapsed', '');
    await page.getByRole('button', { name: 'Perlebar sidebar' }).click();
    await expect(page.locator('html')).not.toHaveAttribute('data-sidebar-collapsed', '');
  }
  if (isMobile) {
    await page.getByRole('button', { name: 'Buka menu lainnya' }).click();
  }
  const workspaceSwitcher = page.locator('[data-sidebar] details').first();
  await workspaceSwitcher.locator('summary').click();
  await workspaceSwitcher.getByRole('link', { name: 'Proyek & tim', exact: true }).click();
  await expect(page).toHaveURL(/\/projects$/);

  if (isMobile) {
    await page.getByRole('button', { name: 'Buka menu lainnya' }).click();
  }
  const navigation = page.getByRole('navigation', { name: 'Navigasi utama', exact: true });
  const navigationMetrics = await navigation.evaluate((element) => {
    const hasOverflow = element.scrollHeight > element.clientHeight;
    if (hasOverflow) element.scrollTop = element.scrollHeight;
    return { hasOverflow, scrollTop: element.scrollTop };
  });
  if (navigationMetrics.hasOverflow) {
    await page.getByRole('link', { name: 'Smart Coach', exact: true }).click();
    await expect(page).toHaveURL(/\/smart-coach$/);
    await expect.poll(() => navigation.evaluate((element) => element.scrollTop))
      .toBeGreaterThan(navigationMetrics.scrollTop - 2);
  }

  if (isMobile) {
    if (!await page.locator('[data-sidebar]').evaluate((element) => element.hasAttribute('data-open'))) {
      await page.getByRole('button', { name: 'Buka menu lainnya' }).click();
    }
  }
  await page.getByRole('button', { name: 'Aktifkan tema gelap', exact: true }).last().click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await expect.poll(() => page.evaluate(() => localStorage.getItem('devflow.theme'))).toBe('dark');

  await page.goto('/calendar');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  if (isMobile) {
    await page.getByRole('button', { name: 'Buka menu lainnya' }).click();
  }
  await page.getByRole('button', { name: 'Aktifkan tema terang', exact: true }).last().click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
});
