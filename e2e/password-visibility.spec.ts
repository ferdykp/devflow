import { test, expect } from './fixtures';

for (const route of ['/login', '/register']) {
  test(`${route}: tombol kata sandi mempertahankan nilai dan mendukung keyboard`, async ({ page, browserName }) => {
    await page.goto(route);
    const fields = page.locator('input[autocomplete$="-password"]');
    await expect(page.locator('[data-toggle-password]')).toHaveCount(route === '/login' ? 1 : 2);

    for (const field of await fields.all()) {
      const id = await field.getAttribute('id');
      const button = page.locator(`button[aria-controls="${id}"]`);
      const value = 'SandiUji123!';
      const autocomplete = await field.getAttribute('autocomplete');
      await expect(field).toHaveAttribute('type', 'password');
      await expect(button).toHaveAccessibleName('Tampilkan kata sandi');
      await expect(button).toHaveAttribute('title', 'Tampilkan kata sandi');
      await expect(button).toHaveAttribute('aria-pressed', 'false');
      await expect(button.locator('i')).toHaveCount(1);
      await field.fill(value);

      await button.click();
      await expect(field).toHaveAttribute('type', 'text');
      await expect(field).toHaveValue(value);
      await expect(button).toHaveAccessibleName('Sembunyikan kata sandi');
      await expect(button).toHaveAttribute('title', 'Sembunyikan kata sandi');
      await expect(button).toHaveAttribute('aria-pressed', 'true');
      await expect(button.locator('i')).toHaveClass(/fa-eye-slash/);

      await button.click();
      await expect(field).toHaveAttribute('type', 'password');
      await expect(field).toHaveValue(value);
      await expect(button).toHaveAccessibleName('Tampilkan kata sandi');
      await expect(button).toHaveAttribute('title', 'Tampilkan kata sandi');
      await expect(button).toHaveAttribute('aria-pressed', 'false');
      await expect(button.locator('i')).toHaveClass(/\bfa-eye\b/);
      await expect(button.locator('i')).not.toHaveClass(/fa-eye-slash/);

      await field.focus();
      // WebKit on macOS uses Option+Tab to include buttons in focus navigation.
      await page.keyboard.press(browserName === 'webkit' ? 'Alt+Tab' : 'Tab');
      await expect(button).toBeFocused();
      await page.keyboard.press('Enter');
      await expect(field).toHaveAttribute('type', 'text');
      await page.keyboard.press('Space');
      await expect(field).toHaveAttribute('type', 'password');
      await expect(field).toHaveValue(value);
      await expect(field).toHaveAttribute('autocomplete', autocomplete!);
      await expect(page).toHaveURL(new RegExp(`${route}$`));

      const inputBox = await field.boundingBox();
      const buttonBox = await button.boundingBox();
      expect(inputBox!.x + inputBox!.width).toBeLessThanOrEqual(buttonBox!.x);
      expect(buttonBox!.width).toBeGreaterThanOrEqual(44);
    }

    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
  });
}

test('register: tombol setiap kolom bekerja secara independen dan validasi tetap aktif', async ({ page }) => {
  await page.goto('/register');
  const password = page.getByLabel('Kata sandi', { exact: true });
  const confirmation = page.getByLabel('Ulangi kata sandi');
  await password.fill('SandiUji123!');
  await confirmation.fill('Berbeda123!');
  await page.locator('button[aria-controls="password_confirmation"]').click();
  await expect(confirmation).toHaveAttribute('type', 'text');
  await expect(password).toHaveAttribute('type', 'password');
  await expect(page.getByRole('status')).toHaveText('Kata sandi belum cocok.');
  await confirmation.fill('SandiUji123!');
  await expect(page.getByRole('status')).toHaveText('Kata sandi sudah cocok.');
  await expect(confirmation).toHaveJSProperty('validationMessage', '');
});
