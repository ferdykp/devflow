import type { Page } from '@playwright/test';
import { expect } from './fixtures';

export async function completeOnboarding(page: Page): Promise<void> {
  if (new URL(page.url()).pathname === '/email/verify') {
    const response = await page.request.get('/__e2e/email-verification-code');
    expect(response.ok(), 'the isolated E2E server should expose its generated OTP').toBeTruthy();
    const { code } = await response.json() as { code: string };
    await page.getByLabel('Kode verifikasi').fill(code);
    await page.getByRole('button', { name: /Verifikasi email/ }).click();
  }

  await expect(page).toHaveURL(/\/welcome$/);
  await page.getByRole('button', { name: 'Lanjutkan pengaturan' }).click();
  await expect(page).toHaveURL(/\/onboarding\/setup$/);
  await page.getByRole('button', { name: 'Simpan dan mulai' }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
}
