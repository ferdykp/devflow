# Staging quality gates

- Added CI for Laravel tests on isolated SQLite, PHP lint and Vite production build.
- Added non-destructive local verification script (does not migrate/reset your development or production database).
- Updated query-optimization test to recognize SQL identifier quoting on both SQLite and MySQL.
- Added actionable staging manual release checklist; no backend API, route, visual layout, or schema changes.

Status: CI has not yet been executed on GitHub. Live staging/integration testing remains required before release.
