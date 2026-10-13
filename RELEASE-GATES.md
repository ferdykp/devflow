# DevFlow release gates (must all pass before calling production-ready)

- [ ] `composer install`, `php artisan test`, and `npm ci && npm run build` succeed.
- [ ] MySQL migrations succeed in staging and rollback/backup recovery is rehearsed.
- [ ] Auth flows, authorization across workspaces, CSRF and rate limits checked in staging.
- [ ] Webhooks checked with real GitHub deliveries; invalid signatures and replayed IDs rejected.
- [ ] Queue and scheduler supervised; stale/failed deliveries monitored and reconciled.
- [ ] Required GitHub App/private repository and approved AI/CLI/PWA functions completed or formally excluded from release scope.
- [ ] HTTPS, environment secrets, filesystem permissions, logging, SMTP, backups, metrics and alerting verified.
- [ ] Manual responsive browser testing, accessibility, performance, and dependency/security audit completed.
- [ ] Rollback plan and production deployment smoke test signed off.
