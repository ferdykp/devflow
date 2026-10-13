# DevFlow Demo Data

This seeder is optional and idempotent. It creates demo data for `demo@devflow.local` only, not for an existing personal account. It does not touch or delete existing projects.

## Run (local/development only)

```bash
php artisan migrate
php artisan db:seed --class=DevFlowDemoSeeder
```

Sign in: `demo@devflow.local` / `DevFlowDemo2026!`. Change password after preview if you keep this user. Never enable this demo account in production.

`php artisan db:seed` alone does not run demo data. To opt in to the general database seeder, set `DEVFLOW_SEED_DEMO=true` in local `.env` and run `php artisan db:seed`.

Contains three workspaces, five projects (active/paused/archived), tasks with distinct statuses/priority, milestones, notes, future ideas, PRD draft/approved versions, scope baseline/reviews/decisions, example activities and today's focus selections.

Demo activities are explicitly tagged `source=demo`; no GitHub connection or real sync is faked. AI analysis also remains optional and unpopulated.

## Fix for SQLite tests

Both priority sorts now use portable `CASE` instead of MySQL-only `FIELD()`; the SQLite in-memory tests can execute the Resume and Focus queries. Verify with `php artisan test`.

Do **not** use `migrate:fresh --seed` on an existing database, because it permanently deletes all tables and data. Use the explicit seeder command above instead.
