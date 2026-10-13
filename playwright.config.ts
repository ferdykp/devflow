import { defineConfig, devices } from '@playwright/test';
import { randomBytes } from 'node:crypto';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

// Test server intentionally NEVER reads the normal application database configuration.
const port = Number(process.env.DEVFLOW_E2E_PORT || 8787);
const baseURL = `http://127.0.0.1:${port}`;
const database = join(tmpdir(), `devflow-e2e-${process.pid}.sqlite`);
const appKey = `base64:${randomBytes(32).toString('base64')}`;

export default defineConfig({
  testDir: './e2e',
  timeout: 30_000,
  expect: { timeout: 10_000 },
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : [['list'], ['html', { open: 'never' }]],
  outputDir: 'test-results',
  use: {
    baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    { name: 'desktop-chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile-chromium', use: { ...devices['iPhone 13'], browserName: 'chromium' } },
  ],
  webServer: {
    command: 'bash scripts/e2e-server.sh',
    url: `${baseURL}/login`,
    reuseExistingServer: false,
    timeout: 120_000,
    env: {
      APP_ENV: 'testing', APP_DEBUG: 'false', APP_KEY: appKey, APP_URL: baseURL,
      DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '',
      SESSION_DRIVER: 'file', CACHE_STORE: 'file', QUEUE_CONNECTION: 'sync',
      MAIL_MAILER: 'array', LOG_CHANNEL: 'stderr',
      DEVFLOW_E2E_ISOLATED: '1', DEVFLOW_E2E_PORT: String(port),
    },
  },
});
