# DevFlow Blade cache path fix

## Symptom

`InvalidArgumentException: Please provide a valid cache path.` during `php artisan test` / HTTP rendering.

## Root cause

The source archive lacked `storage/framework/views/`. Laravel resolves the default compiled view path with `realpath(storage_path('framework/views'))`; when the directory does not exist the path resolves to `false`, and the Blade compiler fails. Multiple unrelated tests then return 500. This is **not** evidence that all failed features are defective.

## Fix

- Includes tracked `.gitignore` placeholders for `storage/framework/views`, `cache` and `sessions`.
- TestCase creates the directories before application bootstrapping for feature tests.
- `scripts/staging-verify.sh` checks writable cache paths before executing tests.

## Fix an already-extracted local checkout

```bash
mkdir -p storage/framework/{views,cache,sessions,testing} bootstrap/cache
php artisan optimize:clear
php artisan test
npm run build
```

Avoid `chmod -R 777`. Give the runtime user/group appropriate write access to `storage` and `bootstrap/cache`. Check `.env` `VIEW_COMPILED_PATH` if explicitly set (use an existing writable directory), and remove stale config cache with `php artisan config:clear` as needed.

No migrations, seeding or database reset needed.
