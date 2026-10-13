# Release preflight update

- Added `devflow:preflight --strict`: checks environment, debug, app key, HTTPS URL, Vite build manifest, session, queue, cache, DB connectivity; optional webhook configuration is reported as a warning.
- Added diagnostics tests and production configuration template.
- Added staging, deployment, backup and rollback checklists in `docs/PRODUCTION-PREFLIGHT.md`.
- No application controller, route, model, migration, view or business logic was modified by this iteration.
- Remaining external validation and full release gates are documented; no production-ready claim is made.
