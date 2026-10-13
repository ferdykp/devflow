# DevFlow — Browser E2E (desktop and mobile)

This package adds Playwright browser tests for register/login, workspace/project creation,
task Board/List, project Resume, My Focus and guest access.

## First run on macOS

```bash
composer install
npm ci
npm install --no-save --package-lock=false @playwright/test@1.56.1
npx playwright install chromium
npm run build
npm run test:e2e
```

`npm ci` removes extraneous packages, so reinstall `@playwright/test` after subsequent
`npm ci` runs. The runner version is pinned, but not currently added to the application
lockfile because package metadata was unavailable in the build environment. Once you
have registry access, prefer `npm install --save-dev @playwright/test@1.56.1`, commit
both package files, and remove the temporary install step from CI.

## Isolation and security

- `playwright.config.ts` generates a random Laravel APP_KEY and a temporary SQLite
  database under the OS temporary directory for this run.
- `scripts/e2e-server.sh` refuses to migrate unless DB_CONNECTION=sqlite,
  APP_ENV=testing, the isolation flag is set and the SQLite filename matches
  `devflow-e2e-*.sqlite`. **It does not use your MySQL database.**
- The local E2E service listens on `127.0.0.1:8787`; stop anything using that port
  before running. The config refuses to reuse an existing server.
- Browser traces, videos and screenshots from failures may contain synthetic login
  credentials or test data. Keep them private and do not publish them.
- **Do not point these tests at production or live users.**

## Commands

```bash
npm run test:e2e                 # Chromium desktop + mobile emulation
npm run test:e2e:ui              # Interactive Playwright explorer
npm run test:e2e:report          # Report after running tests
php artisan test                # Existing Laravel feature test suite
```

## Coverage limitations

This is an initial critical-path browser suite, not a complete acceptance test.
It does not yet verify real GitHub App authorization, live webhook delivery,
AI-provider operations, a real iOS Safari browser, or deployment/backup-restore.
Browser E2E tests have not been executed in the packaging environment because
its npm registry is unavailable. Inspect the first CI run and adjust selectors
if application copy or markup has changed.
