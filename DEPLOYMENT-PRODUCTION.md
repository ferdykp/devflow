# DevFlow production deployment runbook (not a production-readiness certificate)

## Preconditions
- Dedicated Linux host with supported PHP and extensions, MySQL, Node build toolchain, Composer, TLS/reverse proxy and process manager.
- Dedicated least-privilege database credentials, off-host encrypted backup, monitored storage space.
- Production APP_KEY generated once; never rotate casually because sessions/encrypted data may be lost.
- Configure a REAL email delivery service before enabling account recovery or email notifications.
- GitHub integration in this release supports PUBLIC repositories only; no GitHub App private-repo auth, verified app installation lifecycle, or PR/Issues parity.
- AI Progress Assistant, automatic PRD scope classification, and Commit Assistant CLI are NOT implemented. Do not advertise them as operational.

## Staging gate (must pass before production)
1. Use separate staging and test DBs. Copy `.env.production.example` to a secured deployment-specific `.env`, fill secrets outside version control.
2. `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`
3. `npm ci && npm run build` in CI/build machine. Publish build assets.
4. Run `php artisan test` in CI using isolated testing DB (requires dev dependencies), and perform authenticated browser smoke tests.
5. Back up production database, validate restore to a separate instance, then deploy using maintenance window if needed.
6. `php artisan migrate --force` and `php artisan optimize` after environment configuration.
7. Set `storage` and `bootstrap/cache` writable by application user only. Ensure `.env` cannot be served over HTTP.
8. Serve only the `public/` directory. Enable HTTPS, HTTPS redirects, secure cookies and reverse-proxy trust restricted to known proxies.
9. Run supervised `php artisan queue:work --sleep=3 --tries=3 --timeout=60` and one scheduler `php artisan schedule:run` every minute; monitor failed jobs.
10. Restart queue workers during deploy with `php artisan queue:restart`. Verify `/up`, application login, project create, task update, scope/PRD versioning, webhook handling and scheduler.
11. Set backup retention and execute restore drills; instrument error reporting, HTTP status monitoring and queue depth alerts.

## Rollback
- Keep the previous release directory and assets. Database migrations may be destructive or irreversible; avoid assuming `migrate:rollback` can safely recover production data. Restore tested backups or apply a reviewed forward fix.

## Security checklist
- [ ] APP_DEBUG=false, real HTTPS certificate, SESSION_SECURE_COOKIE=true
- [ ] Secret rotation procedure and credential inventory
- [ ] Explicit GitHub webhook secret and authenticated repo binding
- [ ] Rate limiting at application AND reverse proxy for public endpoints
- [ ] Backup & restore test successful
- [ ] Authorization/access-control tests passed
- [ ] Dependency audit and vulnerability triage completed
- [ ] CI build, unit/feature tests, browser smoke tests all green
- [ ] Load/performance testing with representative data completed
- [ ] External integrations tested with provider credentials in staging
