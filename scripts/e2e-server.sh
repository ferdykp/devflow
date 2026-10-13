#!/usr/bin/env bash
set -Eeuo pipefail
cd "$(dirname "$0")/.."

# Safety: never use the developer's active .env / MySQL database for browser tests.
if [[ "${DEVFLOW_E2E_ISOLATED:-}" != "1" ]]; then
  echo 'Refusing to start E2E server without isolated-mode marker.' >&2
  exit 1
fi
if [[ -z "${DB_DATABASE:-}" || "${DB_CONNECTION:-}" != 'sqlite' || "${APP_ENV:-}" != 'testing' ]]; then
  echo 'E2E must use a dedicated SQLite database with APP_ENV=testing.' >&2
  exit 1
fi
if [[ "${APP_DEBUG:-}" != 'false' ]]; then
  echo 'E2E requires APP_DEBUG=false.' >&2
  exit 1
fi
if [[ "${DB_DATABASE}" != *devflow-e2e-*.sqlite ]]; then
  echo 'Refusing to migrate a database without the devflow-e2e-*.sqlite name.' >&2
  exit 1
fi
mkdir -p storage/framework/{views,cache,sessions,testing} bootstrap/cache
touch "$DB_DATABASE"
php artisan migrate --force --no-interaction >/dev/null
exec php artisan serve --host=127.0.0.1 --port="${DEVFLOW_E2E_PORT:-8787}" --no-reload
