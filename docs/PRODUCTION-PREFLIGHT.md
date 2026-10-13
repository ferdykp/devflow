# DevFlow production release procedure

The code is **not production-ready merely because preflight passes**. This check is a guardrail, not certification.

## 1. Reproducible staging build

- Deploy to a staging host configured like production, using a non-production database and credentials.
- `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`
- `npm ci && npm run build` (run in CI/build host, not necessarily production)
- Transfer built `public/build` assets with the release.
- Ensure `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, strong `APP_KEY`, proper session/cookie configuration, and a dedicated least-privilege MySQL account.
- Set up supervised queue workers and one Laravel scheduler; check failed jobs and delivery reconciliation.
- `php artisan migrate --force` after database backup; *never* use `migrate:fresh` on production.
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- `php artisan devflow:preflight --strict` (exits non-zero on required failure).

## 2. Release verification

Run tests in CI using an **isolated testing database**; run both SQLite tests and MySQL integration tests. Validate at least login/register/logout, workspace isolation, all project sections, tasks board interactions, scope decisions, upload/PRD if present, GitHub callbacks and webhook signatures, background queue and reconciliation, PWA behavior and mobile/keyboard accessibility. Audit Composer and npm dependencies. The CLI tool needs its own tests.

Test real GitHub App installations and repository permission changes in staging; verify provider failure responses and API costs. Do not claim external integration health based only on mocked tests.

## 3. Operations

Configure TLS/proxy headers, HTTP server document root pointing to `public/`, file permissions, health monitoring, log rotation, recurring encrypted/off-site database backups and restore drills. Restrict network/database credentials and GitHub PEM file permissions. Document incident owner and rollback procedure. Never expose `APP_KEY` or tokens in logs or tickets.

## 4. Rollback

Back up MySQL and uploaded assets before deployment. Store a previous deployable application artifact. Roll back application code to the previous release only after checking migration compatibility. Never blindly roll back destructive migrations on user data. If schema changes are incompatible, restore from verified backups following an approved recovery procedure.

## 5. Outstanding scope

Live GitHub App private-repo testing, complete AI provider validation, CLI-to-project authorization/PRD integration, browser tests, performance/load tests, and staging deployment are not automatically covered by this package. The release owner must explicitly approve any exclusions.
