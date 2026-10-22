import { test as base, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { basename } from 'node:path';

// Each test starts with empty rate-limit counters in the dedicated E2E database.
// Security middleware remains enabled throughout the scenario.
export const test = base.extend<{ isolatedCache: void }>({
  isolatedCache: [async ({}, use) => {
    const database = process.env.DEVFLOW_E2E_DATABASE;
    const prefix = process.env.DEVFLOW_E2E_CACHE_PREFIX;
    if (!database || !/^devflow-e2e-[a-f0-9]+\.sqlite$/.test(basename(database)) || !prefix?.startsWith('e2e-')) {
      throw new Error('Refusing to clear cache outside the isolated E2E environment.');
    }
    execFileSync('php', ['artisan', 'cache:clear', '--no-interaction'], {
      env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: database,
        DB_URL: '', CACHE_STORE: 'database', DB_CACHE_CONNECTION: 'sqlite', CACHE_PREFIX: prefix,
        APP_CONFIG_CACHE: `${database}.config.php` },
      stdio: 'pipe',
    });
    await use();
  }, { auto: true }],
});
export { expect };
