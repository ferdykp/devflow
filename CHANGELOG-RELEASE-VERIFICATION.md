# DevFlow — Release Verification Update

This update builds on `devflow-release-preflight.zip`.

## Changes
- Production preflight validates APP_KEY encryption byte length (16/24/32) rather than merely nonempty text.
- Production APP_URL must parse as HTTPS with a non-local hostname.
- Session, cache, and queue drivers are checked against explicit supported server-side/persistent sets instead of rejecting a few unsafe values.
- Project `show.blade.php` no longer duplicates the project navigation icon/title map. The partial remains responsible for that map.
- New regression test renders each existing project section and the Resume page to catch missing Blade variables after UI edits.
- New tests assert strict preflight rejects invalid encryption keys and localhost URLs.

## Verification in packaging environment
- PHP syntax: 58 files under `app/` and `tests/` passed `php -l`.
- JavaScript: `node --check resources/js/app.js` passed.
- PHPUnit: not executed (vendor directory absent; composer not installed here).
- Vite build: not executed (node_modules directory absent).
- Integration testing: GitHub App, AI provider, and live staging not verified.

## Local checks (use isolated test database)
```
composer install
npm ci
npm run build
php artisan test
php artisan devflow:preflight --strict
```

Use `devflow:preflight --strict` on staging/production, not a normal debug local environment. This command cannot validate actual HTTPS termination, worker supervision, external providers, backups, or browser accessibility.

## Deployment gate
Do not call this release production-ready until staging browser tests, database migration and rollback rehearsal, GitHub App integration, queue worker/scheduler restart, backups/restore and security verification are completed. Avoid running `migrate:fresh` on an existing database.
