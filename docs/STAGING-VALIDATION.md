# DevFlow — Staging validation

This update adds GitHub Actions CI (`.github/workflows/quality.yml`), `scripts/staging-verify.sh`, and a portable query-optimization assertion.

## Before deploying

- Use a staging domain with valid HTTPS and a **separate MySQL database**. Do not reuse production data or tokens.
- Back up the target database and verify that the backup can be restored in a separate environment.
- Configure separate GitHub App / AI sandbox credentials as appropriate. Core functionality works without these integrations.
- Review access permissions and confirm `APP_DEBUG=false` and secure cookies for the staging HTTPS domain.
- Install dependencies using locked versions (`composer install`, `npm ci`).
- Execute `bash scripts/staging-verify.sh` in a developer/CI environment; it runs automated tests using SQLite in memory and does not reset the application database.
- Build assets and run `php artisan devflow:preflight --strict` in a staging environment configured like production.
- Run `php artisan migrate --force` on staging *only after* a database backup. Do not use `migrate:fresh`.
- Use managed queue workers and scheduler; verify job processing and failed-job reporting.

## Manual release gates

1. Verify login, register, logout, CSRF handling and direct URL access across two separate accounts.
2. Verify project navigation, Resume, all Project sections, task board/list transitions, and toast notifications on mobile (360px), tablet and desktop.
3. Check a project with no PRD/GitHub/AI, a project with populated data, and a paused/archived project.
4. Run GitHub App with a **test private repository**, exercise webhook push, duplicate delivery and revoked installation permission; confirm activity freshness and worker health.
5. Exercise AI consent and provider error/rate-limit paths without sending secrets in prompts.
6. Confirm restore from backup to an isolated database; test rollback of the application code with the existing migrated schema.
7. Validate monitoring, alerts, TLS termination, security headers, browser accessibility and logging privacy.

## Release decision

CI green is necessary, but not sufficient: do **not** mark production-ready without completed manual gates and actual staging deployment results. CI cannot establish live GitHub access, queue process supervision, uptime, or browser usability on its own.
