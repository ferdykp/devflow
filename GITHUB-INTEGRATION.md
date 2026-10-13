# DevFlow GitHub Sync — implementation notes

## Supported scope

This release extends **read-only PUBLIC repository** syncing. GitHub App installation tokens and private repository access are **not implemented**. No source code or credentials are sent to AI services.

- Link via a publicly accessible `owner/repository` in Project Detail.
- Manual Sync now queues a background job, rather than blocking the browser.
- Optional GitHub `push` webhook is authenticated with HMAC SHA-256 and deduplicated using `X-GitHub-Delivery`.
- Hourly reconciliation queues syncs for repositories that have not synced in the configured window.
- Commits are stored idempotently using `(project_id, source, source_key)`.

## Setup

1. Keep your existing private `.env` outside shared ZIPs. Add `GITHUB_WEBHOOK_SECRET=<long-random-secret>`; this secret must equal your GitHub webhook secret.
2. On the public GitHub repository, open **Settings → Webhooks → Add webhook**. Set **Payload URL** to `https://YOUR_DOMAIN/api/webhooks/github`, content type to `application/json`, and choose **Just the push event**. For local development use a trusted HTTPS tunnel or skip webhooks and rely on manual/scheduled sync.
3. Set `QUEUE_CONNECTION=database`; run `php artisan migrate`, `php artisan queue:work --tries=3` under Supervisor in production.
4. Run `php artisan schedule:work` locally. In production set a cron job every minute to invoke `php artisan schedule:run`.
5. Test with `php artisan test --filter=GitHubWebhookTest`. Set up a dedicated test DB if you change the default in-memory test environment.
6. You can manually trigger `php artisan devflow:github-sync --stale=60`.

## Important limitations

- GitHub sends a webhook per repository; configure it for each public repo being tracked. Authentication only verifies the shared secret, not GitHub IP addresses.
- No GitHub App OAuth, installation lifecycle, issues, PRs, full history pagination, or private repository syncing yet.
- REST API unauthenticated calls are subject to low rate limits. Failed jobs retry, and `last_error` is saved to the repository.
- Activity timing covers pushed commits only, not unsaved or unpushed local work.
- Review account-level GitHub permissions before extending this to private repos.
- Don't use queued sync without an active worker; the browser will report **queued**, not **completed**.

## Engineering notes

- The webhook controller checks signature using the **raw** request body, prior to any database mutation.
- Duplicate deliveries receive `202` and are not queued a second time.
- Unrecognized or private repo IDs are ignored, even with a valid signature.
- The job uses GithubRepository's immutable GitHub numeric ID for API lookups, which mitigates repository-name URL manipulation.
- Laravel's scheduler and queue are intentionally separated from the HTTP request cycle.
