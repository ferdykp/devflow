# DevFlow — Project Section Performance Update

## Summary

The Project Workspace now loads section-specific data instead of eagerly querying every module for every request.

- Overview: project identity, 5 suggested open tasks, 5 recent activities, and accurate counts.
- Tasks: paginated tasks and milestone options (without eager-loading all milestone tasks).
- Activity: recent 50 activities and summary counts.
- Notes: latest 50 notes.
- Backlog: latest 50 future items.
- Milestones: milestones and their task relationships.
- Scope: scope baseline and reviews with decisions.
- Documents: project PRD documents and versions.
- GitHub: repository connections only.
- Settings: project identity without unrelated module queries.

No route, migration, domain status, policy, or client-side dependency was changed.

## Verification

- PHP lint checks passed for the modified controller and added test.
- PHPUnit **could not run in the package environment**. The PHP CLI is missing `dom`, `mbstring`, and `xmlwriter` extensions. Do not claim passing tests based on lint results.
- Run `php artisan test` with all PHP extensions on your Mac and `npm run build` before staging.
- New tests in `tests/Feature/ProjectSectionQueryTest.php` validate section rendering and that Overview avoids loading unrelated modules.

## Release checklist

1. Back up the database; deploy to staging with the same configured database engine and worker settings as production.
2. Run `composer install --no-dev --optimize-autoloader` in a production release, and build frontend assets with `npm ci && npm run build` during the build step.
3. Run `php artisan test` in a separate testing environment (with dev dependencies installed).
4. Run `php artisan devflow:preflight --strict` in staging and production.
5. Check project Overview, Tasks List/Board, Resume, Notes, Scope, PRD, and GitHub pages using realistic records.
6. Verify queue, scheduler, webhook signing, and external GitHub App/AI permissions separately.
7. Check rollback and database restore procedure before opening production traffic.

This ZIP is a release-readiness iteration; GitHub App and AI provider integrations are not marked production-verified.
