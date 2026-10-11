# DevFlow — Environment & Configuration Guide

**Baseline:** Laravel 12 + Livewire 4 + MySQL 8.0+; local/prod separation. Never commit actual `.env`.

## 1. Prerequisites
PHP 8.2+ (check Laravel/package constraints), Composer 2, Node.js LTS, npm, MySQL 8+, Git. Server: Nginx + PHP-FPM + Supervisor/cron. Prefer dedicated MySQL account with least privilege. Livewire 4 bundles Alpine; **do not initialize Alpine a second time**.

## 2. Example `.env.example` (safe placeholders)
```dotenv
APP_NAME=DevFlow
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devflow
DB_USERNAME=devflow_user
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME="${APP_NAME}"

GITHUB_APP_ID=
GITHUB_APP_SLUG=
GITHUB_APP_PRIVATE_KEY_PATH=
GITHUB_WEBHOOK_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_SYNC_LOOKBACK_DAYS=30

AI_ENABLED=false
AI_PROVIDER=none
AI_API_KEY=
AI_MODEL=
AI_SEND_SOURCE_CODE=false

DEVFLOW_FEATURE_PWA=false
DEVFLOW_RECONCILE_MINUTES=60
```
Use `APP_TIMEZONE` only after explicitly mapping it in `config/app.php` if your Laravel skeleton does not read that variable. GitHub App credentials must be read through `config/services.php` rather than raw `env()` calls across services.

## 3. Environment responsibilities
| Environment | Debug | DB | Cookies | Mail | Queue |
|---|---|---|---|---|---|
| Local | true | isolated MySQL | HTTP localhost acceptable | log | database worker |
| Staging | false | separate MySQL | HTTPS/Secure | sandbox | supervised worker |
| Production | false | production DB | HTTPS/Secure | configured SMTP | supervised worker |

For production set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.example`, `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning` (or operationally appropriate), real SMTP. Generate `APP_KEY` once and back it up securely; rotating keys affects encrypted cookies/data.

## 4. Setup commands
```bash
composer create-project laravel/laravel devflow "^12.0"
cd devflow
composer require livewire/livewire:^4.0
npm install
cp .env.example .env
php artisan key:generate
# Create MySQL database/user separately; set credentials in .env
php artisan migrate
npm run dev
php artisan serve
# In separate terminal for async jobs:
php artisan queue:work --tries=3 --backoff=10
```
If Laravel project scaffold already contains starter-kit migrations, **do not duplicate** the default users/password_reset_tokens/sessions/cache/jobs/failed_jobs tables. Check actual generated migrations first.

## 5. MySQL initialization example (administrator only)
```sql
CREATE DATABASE devflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devflow_user'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_SECRET';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
ON devflow.* TO 'devflow_user'@'localhost';
```
For higher assurance, use separate deployment migration credentials and restricted runtime permissions. Do not grant `GRANT OPTION` or global privileges. Adjust host to your actual connection routing.

## 6. GitHub App setup
- Register GitHub App and store App ID, private key outside public webroot, webhook secret, installation info securely.
- Request **read-only** repo metadata, contents (only if commits/metadata API requires it), issues, pull requests, and webhook events as needed; request minimum required account/org permissions. Validate exact required permissions during implementation.
- Accept webhook only with valid `X-Hub-Signature-256` HMAC and record `X-GitHub-Delivery` with unique index.
- Answer webhook fast, dispatch queue; retry/transient failure and periodic reconciliation.
- Support installation removal/permission changes; retain local history under defined retention policy.

## 7. Queue and scheduler
```cron
* * * * * cd /var/www/devflow && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
Run long-lived `php artisan queue:work` with Supervisor/systemd. After deploy: `php artisan queue:restart`; inspect `php artisan queue:failed`. Use queue `database` initially, avoid slow LLM/network work in HTTP request. Keep sync & AI queues separately configurable later.

## 8. Production operations and safety
- Nginx document root points to `/var/www/devflow/public`, never repo root.
- `php artisan config:cache`, `route:cache` (only where compatible), `view:cache`, `npm run build`; deploy atomic-ish with maintenance plan.
- Restrict writable directories to `storage` and `bootstrap/cache`.
- Automated encrypted backups: MySQL, uploaded PRDs, GitHub private key; test restore.
- Secure headers/CSP tailored to Livewire, HTTPS, CSRF, rate limits, access logs without tokens.
- Never log full GitHub payloads/source diffs or secret values by default.
- Do not cache sensitive authenticated HTML with the PWA service worker.

## 9. Validate
```bash
php artisan about
php artisan migrate:status
php artisan queue:failed
php artisan test
npm run build
```
Smoke test: name-only project; create task without milestone; GitHub sync and webhook replay; optional PRD; Move to Backlog atomic action; disable AI and confirm core features still work.

## 10. Reference links
- https://laravel.com/docs/12.x/installation
- https://laravel.com/docs/12.x/database
- https://livewire.laravel.com/docs/4.x/installation
