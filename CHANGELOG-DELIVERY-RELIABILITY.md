# DevFlow — GitHub delivery reliability iteration

## Implemented
- GitHub webhook deliveries track `pending_jobs`, `succeeded_jobs`, and `failed_jobs`.
- Delivery transitions to `completed` only once all scheduled repository jobs complete successfully.
- Delivery transitions to `failed` when all jobs have settled and one or more failed permanently.
- Repository deletion before processing resolves that repository's job rather than leaving a pending delivery forever.
- Database insert uses `insertOrIgnore` for concurrency-safe duplicate delivery handling, protected by the existing delivery ID unique index.
- New migration `2026_10_12_000200_track_github_delivery_results.php` applies additively to existing databases.
- New regression tests cover one delivery mapped to multiple projects and permanent job failure.

## Caveats / known limitations
- Webhooks require `GITHUB_WEBHOOK_SECRET`, a publicly reachable HTTPS endpoint, and a running queue worker.
- Dispatch failure between inserting a delivery and enqueuing jobs may leave the delivery `queued`; reconcile or alert on stale deliveries before enabling production.
- Queue processing is at-least-once. Activity imports are deduplicated by project/repository/commit SHA; counters are operational tracking and may require reconciliation if a worker dies after syncing but before recording the result.
- GitHub App (private repositories), PR/issue integration, AI features, commit CLI, PWA, and staging sign-off remain unfinished.
- Runtime tests and Vite build MUST run in a configured staging environment; this package has only passed PHP syntax checks in the editing environment.

## Upgrade (after backing up the database)
```
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan queue:restart
```
Verify queue workers and scheduler, then run feature tests against a separate test DB. Never run `migrate:fresh` against production.
