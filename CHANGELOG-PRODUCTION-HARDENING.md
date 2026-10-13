# Production hardening increment

This is an INCREMENTAL security/reliability patch, NOT a production-ready release.

- GitHub webhook now checks content type and payload size (1 MiB), after verifying HMAC.
- GitHub delivery status is no longer falsely marked processed when a webhook schedules sync jobs for multiple project associations. `queued` describes intake; per-repository `last_synced_at` supplies completion information. Reliable aggregate delivery completion requires a separate per-repository receipt model.
- Added regression tests for webhook content-type and payload limit, and updated the delivery state test.
- Added sample production environment and deployment/rollback checklist.

Unverified gates: Composer/PHPUnit (no Composer/vendor available), Vite build, MySQL migrations, integration provider credentials, load tests, backup restore, security review.
