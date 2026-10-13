#!/usr/bin/env bash
set -Eeuo pipefail

# Run from the repository directory. Never modifies the production database.
cd "$(dirname "$0")/.."

if [[ ! -f artisan || ! -f composer.json ]]; then
  echo 'ERROR: Not a DevFlow Laravel project.' >&2
  exit 1
fi

if [[ ! -x vendor/bin/phpunit ]]; then
  echo 'ERROR: Composer dependencies missing. Run composer install.' >&2
  exit 1
fi

if [[ ! -d node_modules ]]; then
  echo 'ERROR: Node dependencies missing. Run npm ci.' >&2
  exit 1
fi

echo '==> Prepare Laravel writable directories'
mkdir -p storage/framework/{views,cache,sessions,testing} bootstrap/cache
if [[ ! -w storage/framework/views || ! -w bootstrap/cache ]]; then
  echo 'ERROR: Laravel cache directories are not writable.' >&2
  exit 1
fi

echo '==> Backend tests (configured in phpunit.xml for SQLite :memory:)'
php artisan test

echo '==> Production frontend build'
npm run build
test -s public/build/manifest.json

echo '==> Verify PHP files'
find app routes database tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

echo '==> Completed code verification.'
echo 'NOTE: Staging deployment, browser E2E, backup restore, GitHub App and AI provider still require separate live checks.'
