# DevFlow — Single Navigation Redesign

Replaced the nested project sidebar with a contextual horizontal navigation.

- Global rail: Overview, Projects, My Focus, search and account.
- Project bar: Overview, Tasks, Resume, Activity, Notes.
- More: Future ideas, Milestones, Scope, Documents, GitHub, Settings and AI Insights.
- Preserved current `?section=` routing, forms, models, CSRF and authorization.
- Task board/list now receives full canvas width.
- Feature tests call `withoutVite()` so they do not depend on a precompiled manifest. Production still requires `npm run build`.

## Verification
Run `npm ci && npm run build`, then `php artisan test` on a safe test database. Browser-check desktop and mobile; the More menu must be reachable with keyboard and touch.
