# DevFlow GitHub delivery reconciliation

## Implemented

- Added `github_delivery_jobs` tracking each repository per webhook delivery, with a unique `(delivery_id, repository_key)` index.
- Webhook registers all work transactionally before queue dispatch, allowing the scheduler to recover interruptions between database commit and enqueue.
- Job success/permanent failure is applied exactly once to delivery counters even if an original and retried job both complete.
- Added `devflow:github-reconcile` command. Jobs stale for at least 15 minutes are re-dispatched at most once; after another stale interval, they are marked failed rather than retrying indefinitely. A null/deleted repository is treated as failed when reconciling.
- Scheduler runs reconciliation every ten minutes; worker processes must still run continuously.
- Added Feature Tests for duplicate job completion and stale recovery.

## Limitations / operational guidance

- Existing `github_deliveries` entries created before this migration have no per-repository rows and cannot be automatically recovered. Inspect them manually, or re-sync affected repositories via scheduled/manual sync.
- Duplicate queue execution may still make duplicate outbound GitHub API calls when a worker stalls; activity persistence is idempotent by source key. Exactly-once refers to delivery counters, not external network calls.
- A truly stuck worker can run concurrently with a recovered job. Configure `queue:work --timeout=45` and queue `retry_after` greater than the worker timeout, and keep worker/scheduler supervised. Verify these values with the deployed queue driver.
- GitHub private repository access, installation tokens, webhook routing authorization, PR/issue sync, full AI/CLI/PWA scope, and production staging gates remain outstanding. Do not label this release production-ready.
- Regression tests require a separate disposable test database (`.env.testing`), not the developer or production database.

## Deployment

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart
php artisan schedule:list
```

For verification on a development machine:

```bash
php artisan test --filter=GitHubWebhookTest
php artisan devflow:github-reconcile --stale=30
```
