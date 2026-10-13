# DevFlow — Resume & Focus Update

## Delivered
- Project Resume has a dedicated route and Blade page: `/projects/{project}/resume`.
- Read-only resume combines actual open tasks, milestones, recent GitHub/manual activity, and notes.
- Shows source availability, last synchronization and explicit empty states. No fabricated AI summary or progress data.
- Project detail has a **View resume** link.
- Daily Focus excludes paused/archived projects and blocked/done tasks.
- Focus limit (three tasks per user per day) is enforced under a MySQL row lock and transaction.
- Added `tests/Feature/ResumeAndFocusTest.php` for basic optional-source behavior, access isolation and focus eligibility.

## Setup
This iteration adds no migrations and no Composer or npm dependencies. Apply it on top of the previous GitHub Sync build. Existing `.env` should be retained locally.

```sh
composer install
npm ci
php artisan migrate
php artisan test
npm run build
```

**Testing note:** The source archive does not include vendor dependencies or node_modules; full Laravel runtime tests and Vite build could not be executed in the packaging environment. PHP syntax checks passed. Use an isolated test database for tests that use RefreshDatabase.

## Out of scope / next
- GitHub App installations and private repository authorization
- Advanced AI insights, automatic PRD semantic checking, and local commit message CLI
- Production hardening, deployment verification, PWA offline functionality
