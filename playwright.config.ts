import { defineConfig, devices } from '@playwright/test';
import { randomBytes } from 'node:crypto';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

// Test server intentionally NEVER reads the normal application database configuration.
const port = Number(process.env.DEVFLOW_E2E_PORT || 8787);
const baseURL = `http://127.0.0.1:${port}`;
const database = process.env.DEVFLOW_E2E_DATABASE ||= join(tmpdir(), `devflow-e2e-${randomBytes(8).toString('hex')}.sqlite`);
const cachePrefix = process.env.DEVFLOW_E2E_CACHE_PREFIX ||= `e2e-${randomBytes(8).toString('hex')}-`;
const includeWebkit = process.env.DEVFLOW_E2E_WEBKIT === '1';
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
    ...(includeWebkit ? [{ name: 'desktop-webkit', testMatch: /(?:password-visibility|dialog-safety)\.spec\.ts/, use: { ...devices['Desktop Safari'] } },
    { name: 'mobile-webkit', testMatch: /(?:password-visibility|dialog-safety)\.spec\.ts/, use: { ...devices['iPhone 13'] } }] : []),
  ],
  webServer: {
    command: 'bash scripts/e2e-server.sh',
    url: `${baseURL}/login`,
    reuseExistingServer: false,
    timeout: 120_000,
    env: {
      APP_ENV: 'testing', APP_DEBUG: 'false', APP_LOCALE: 'id', APP_FALLBACK_LOCALE: 'id', APP_KEY: appKey, APP_URL: baseURL,
      DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '',
      SESSION_DRIVER: 'database', SESSION_CONNECTION: 'sqlite', SESSION_COOKIE: 'devflow_e2e_session',
      SESSION_SECURE_COOKIE: 'false', SESSION_DOMAIN: '',
      CACHE_STORE: 'database', DB_CACHE_CONNECTION: 'sqlite', CACHE_PREFIX: cachePrefix,
      QUEUE_CONNECTION: 'sync', APP_CONFIG_CACHE: join(tmpdir(), `devflow-e2e-config-${process.pid}.php`),
      MAIL_MAILER: 'array', LOG_CHANNEL: 'stderr', DEVFLOW_AI_ENABLED: 'false', DEVFLOW_AI_API_KEY: '',
      GITHUB_APP_ID: '', GITHUB_APP_CLIENT_ID: '', GITHUB_APP_CLIENT_SECRET: '', GITHUB_APP_PRIVATE_KEY: '', GITHUB_WEBHOOK_SECRET: '',
      DEVFLOW_E2E_ISOLATED: '1', DEVFLOW_E2E_PORT: String(port),
    },
  },
});
