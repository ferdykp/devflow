# DevFlow — Step-by-Step Engineering Guide (Development → Production)

**Document:** `stepbystep.md`  
**Version:** 2.0 — implementation guide, 2026-10-10  
**Status:** Proposed execution baseline; follow `prd.md` for feature scope  
**Audience:** Solo developer learning Laravel best practices while building DevFlow  
**Goal:** Build a maintainable, secure, deployable DevFlow with no surprise packages or unapproved features.

> **SCOPE RULE:** This document describes *how* to implement already-approved capabilities, not permission to invent features. Every new library, authentication approach, or feature with material impact requires a recorded decision before implementation. No PRD/GitHub/AI requirement may be imposed on a project.

## 0. Source of truth and locked decisions

Read these documents before starting a feature:

| File | Authority |
|---|---|
| `prd.md` | What to build, acceptance criteria, MVP versus future scope |
| `erd-migration.md` | Logical tables, relationships, constraints and migration rules |
| `design-system.md` | UI tokens, layout, accessibility and components |
| `user-flow.md` | User interaction, navigation, alternate paths and empty states |
| `ui-ux-prompt.md` | AI assistance on UI implementation; never supersedes PRD |
| `env.md` | Configuration reference and secret-handling conventions |
| **This file** | Actual step order, engineering practices, testing, rollout |

### Current project baseline (confirmed from terminal, not an assumption)

- Laravel **13.35.0**, PHP **8.5.0**, Livewire **4.4.7**.
- MySQL **9.4.0** at `127.0.0.1:3306`, database **`db_devflow`**; default migrations have run.
- Existing `routes/web.php` only registers `/`; current project has **no authentication starter kit UI**.
- `composer.json` currently includes Laravel, Livewire, Tinker and Laravel development tools; **Fortify was not part of the initial specification or confirmed installed**.
- Basic tests: two default tests passed. This does **not** prove authentication, workspace authorization or deployment is ready.

**Version alignment:** older documents say Laravel 12/MySQL 8+. For this execution guide, use **Laravel 13** (already installed) and **MySQL 9.4** locally. Production MySQL must support migrations/features used by DevFlow (InnoDB, `utf8mb4`, JSON, proper indexes); test on the chosen production version. Update old document headers in a separate docs-only change. Avoid downgrades just to match old text.

### Technology decisions (MVP)

| Layer | Decision | Why |
|---|---|---|
| Web backend | Laravel 13 modular monolith | One framework, one deployment |
| UI | Blade + Livewire 4 + Alpine (Livewire-bundled) | Reactive PHP-driven UI; no separate SPA |
| CSS | Tailwind CSS v4 + Vite | Design system, responsive UI |
| Database | MySQL, InnoDB, `utf8mb4` | Requested relational database |
| Authentication | **Laravel built-in session guard + Auth facade, custom Blade/Livewire presentation** | No unplanned dependency; learn Laravel fundamentals |
| Queue/cache/session | Database driver initially | Simpler MVP; move to Redis only when necessary |
| GitHub | GitHub App + REST API + verified webhooks | Selected repository permissions |
| AI | Optional adapter, disabled by default | App remains usable without LLM |
| PWA | Manifest + conservative service worker | Installability; no claim of full offline editing |
| Production | Ubuntu, Nginx, PHP-FPM, MySQL, Supervisor, scheduler | Conventional Laravel operations |

**Authentication decision record AUTH-001:** Do **not** install Fortify by default. Fortify is an official, valid optional auth backend, not an error, but adding it changes implementation scope/dependencies. We use Laravel's session authentication services for V1, with dedicated controllers/requests. If the team later chooses Fortify, explicitly approve an architecture change; do not mix duplicate `/login` or `/register` routes. A Laravel starter kit is an alternative *at project creation*, not something to blindly overlay on the current repository.

**Never do for convenience:** `composer require` extra packages without approval, copy a starter kit over existing files, call `migrate:fresh` on shared/production data, commit secrets, turn AI recommendations into features automatically, or grant blanket GitHub permissions.

---

## Phase 0 — Secure the baseline (existing project; DO NOT recreate)

### Step 0.1 — Inventory and Git checkpoint

From the existing `devflow` folder:

```bash
pwd
php -v
php artisan --version
php artisan about
composer show laravel/framework
composer show livewire/livewire
php artisan route:list
php artisan migrate:status
php artisan test
git status
```

Verify the directory is the expected project and create an initial commit once `.env` and other credentials are excluded by `.gitignore`:

```bash
git check-ignore .env
# Initialize Git only if the repository does not already exist:
# git init
# git add . && git commit -m "chore: establish DevFlow Laravel baseline"
```

**Review gate:** `git check-ignore .env` should indicate that `.env` is ignored; no secrets are staged. Current root route and migrations remain intact.

### Step 0.2 — Inspect existing settings, not overwrite them

```bash
cat composer.json
cat routes/web.php
cat bootstrap/app.php
cat config/app.php
cat .env.example
```

Inspect actual app structure: `app/Models/User.php`, `database/migrations`, `resources/views`, `resources/css/app.css`, `resources/js/app.js`, `vite.config.*`, `tests/`.

Do **not** replace `composer.json` with an old Laravel 12 example, or copy credentials from production into `.env.example`.

### Step 0.3 — Verify database is isolated

```bash
php artisan db:show
php artisan migrate:status
php artisan tinker --execute='dump(config("database.connections.mysql.database"));'
```

Expected app database: `db_devflow`. A MySQL admin user can see tables from other databases; `db:show` may display many schemas. Avoid running destructive commands on any `db_*` database used by other projects.

**Best practice:** create a dedicated MySQL user for DevFlow rather than continuing to use MySQL `root`. Example executed in an admin SQL session (use secure generated password):

```sql
CREATE USER 'devflow_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_SECRET';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
ON db_devflow.* TO 'devflow_app'@'localhost';
```

Local MySQL clients using TCP to `127.0.0.1` may resolve accounts differently from `localhost` depending on host configuration: verify login with the exact `.env` host and user. For production, consider separating migration privileges (DDL) from the lower-privilege runtime account after deployment.

### Step 0.4 — Safe local environment

Set your existing `.env` values (do not store actual secrets in Markdown):

```dotenv
APP_NAME=DevFlow
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_devflow
DB_USERNAME=devflow_app
DB_PASSWORD=REPLACE_WITH_LOCAL_SECRET
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

Keep Laravel timestamps in UTC internally; format display dates for the user's timezone in views. Confirm which `config/app.php` timezone is active rather than assuming `APP_TIMEZONE` is recognized automatically.

```bash
php artisan config:clear
php artisan db:show
php artisan test
npm install
npm run build
```

**Done:** local request returns 200, database connection passes, migrations are intact and build succeeds.

---

## Phase 1 — Learn the Laravel request lifecycle; build auth using core Laravel

### Step 1.1 — Understand responsibility boundaries

`Browser → web route → middleware → controller / Livewire → validation → policy → Eloquent → MySQL → Blade response`.

- **Routes:** URL-to-handler mapping, no business logic.
- **Controller:** HTTP orchestration; excellent for login/logout and simple non-Livewire endpoints.
- **Livewire component:** interactive screens and server-bound state, not a container for all domain logic.
- **Form Request / Livewire validation:** input shape and field constraints.
- **Policy:** per-resource access; mandatory for sensitive reads/writes.
- **Model/Eloquent:** persistence and relationships.
- **Service/action:** only when business rules span components/models, transactions, or external APIs.
- **Job:** durable asynchronous work (sync/summarization); never rely on an HTTP request to finish a long sync.

### Step 1.2 — Implement authentication without Fortify

**Scope:** register, login, logout, protected dashboard. Password reset/email verification should be planned and tested **before public production sign-ups**; for a private MVP, keep registration closed after creating the owner account if these are not ready.

Create these classes (names illustrative; implement progressively):

```text
app/Http/Controllers/Auth/
  RegisteredUserController.php
  AuthenticatedSessionController.php
app/Http/Requests/Auth/
  RegisterRequest.php
  LoginRequest.php
resources/views/auth/
  register.blade.php
  login.blade.php
resources/views/layouts/
  guest.blade.php
  app.blade.php
```

Recommended route design:

```php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::view('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');
```

**Implementation rules (do not skip):**
1. Registration: validate name, unique email, strong password + confirmation; hash using `Hash::make()`, never plaintext.
2. Login: `Auth::attempt()` against validated credentials; apply throttling/rate limiting; regenerate session on success.
3. Logout: `Auth::logout()`, invalidate session and regenerate CSRF token.
4. POST forms include `@csrf`; use escaped Blade interpolation `{{ }}`.
5. Return generic failure messages for invalid credentials; do not expose account existence.
6. Enable email verification when email-dependent features go public; reset password via Laravel's password broker and a configured mailer (not a custom token scheme).
7. Restrict guest routes, use `auth` middleware, and later `verified` where appropriate.
8. If registration is intentionally owner-only, disable the public register endpoints in production and provision the account securely.

**Do not** start developing project CRUD until login/logout/protected route tests pass.

### Step 1.3 — Authentication tests (Feature)

Create tests that prove:
- Guest can open login; unauthenticated user is redirected from dashboard.
- Valid user can login; session changes; invalid password rejected.
- Repeated failed attempts are throttled.
- Register rejects invalid/duplicate email and weak passwords.
- Password stored hashed; logout invalidates session.
- Cross-site requests without a valid CSRF token are rejected in real middleware operation (feature tests may automatically bypass CSRF middleware; do not mistake that for a failed protection).
- No public register route remains if you choose owner-only mode.

```bash
php artisan make:test Auth/AuthenticationTest
php artisan test --filter=AuthenticationTest
php artisan route:list --path=login
php artisan route:list --path=dashboard
```

**Done:** Auth flow works end-to-end in browser and tests. Note: these tests should use a test database, not live `db_devflow` data.

---

## Phase 2 — UI foundation (clean, modern, accessible)

### Step 2.1 — Establish layout and components

Implement `guest` and authenticated `app` layouts using Blade. Build minimum shared components:

- App shell: collapsible sidebar, responsive top bar, breadcrumb, main content container.
- UI primitives: button, input, label, textarea, select, card, badge, alert/toast, dialog, pagination, table, skeleton and empty state.
- Global tokens from `design-system.md`: typographic scale, semantic colors, consistent spacings, radii and shadows.
- Avoid duplicate Alpine bundles: use Alpine supplied by Livewire unless custom setup explicitly demands otherwise.

### Step 2.2 — UX rules

- Desktop sidebar, mobile drawer; keyboard-operable navigation.
- Clear page title, brief description, primary action, status and filters.
- Loading/empty/error/success states, consistent button labels, actionable validation messages.
- Accessible form label and focus states, contrast, visible hover/focus, responsive scroll for large tables.
- No fake placeholder counts or fabricated project progress in live UI.
- Prefer Blade for static layouts; use Livewire when server-backed interactions materially need it, Alpine for local UI toggles.
- Avoid using `wire:ignore` indiscriminately; manage charts/JS plugin lifecycles deliberately.

### Step 2.3 — Validation

Check 360px mobile, tablet, desktop widths; keyboard-only navigation and screen-reader labels; ensure no horizontal page overflow or clipped dialogs. Screenshot-review each page before marking done.

**Done:** Login, register (if enabled), dashboard shell share a consistent design system without UI logic duplication.

---

## Phase 3 — Workspaces and basic projects (no PRD/GitHub/AI required)

### Step 3.1 — Model boundaries and migrations

Follow `erd-migration.md`, using MySQL-compatible migrations (`foreignId()->constrained()`, InnoDB, JSON; no PostgreSQL JSONB). Recommended starting tables:

```text
workspaces
workspace_members (if access model requires it)
projects
project_settings
project_notes
```

Do **not** require `prd_version_id`, repository, or milestone on `projects`. `projects` needs only valid identity, owner/workspace and name. Choose slug uniqueness scope deliberately (e.g., unique `(workspace_id, slug)`). Default settings are stored on project creation.

### Step 3.2 — Eloquent relationships and authorization

Implement `User → workspaces`, `Workspace → projects`, `Project → notes/settings`, plus minimal memberships if required by the access design. Define `ProjectPolicy` before exposing project details/edit/delete. Every route and Livewire action must enforce ownership/membership; hiding a button does not authorize an operation.

### Step 3.3 — Project UI

Build: project list, create form, project detail, edit, archive, and project notes. **Project can be created with name only.** Show useful empty states for unavailable Git/PRD/task capabilities.

### Step 3.4 — Tests

- Name-only project creation works.
- Empty PRD/repository state does not break pages.
- Unauthorized user cannot read/edit/delete another user's project (including direct route IDs and Livewire actions).
- Slug collisions, invalid names, archived filters and pagination work.
- SQL foreign-key and uniqueness constraints protect integrity.

**Done:** An authenticated user can manage independent projects without any integration.

---

## Phase 4 — Tasks, optional milestones and Future Backlog basics

### Step 4.1 — Tables

`milestones`, `tasks`, `backlog_items`; task has `project_id` and nullable `milestone_id`. Backlog is a separate domain entity: an idea does **not** count toward active task progress.

### Step 4.2 — Flows

- Create task directly within project; optionally assign a milestone later.
- Statuses: backlog/todo/in_progress/in_review/done (plus blocked/cancelled as explicitly defined).
- Task listing: search, filters, status and priority; choose simple list first, Kanban only if within PRD.
- Future idea capture: title, description, `future` / `research` / `someday` bucket; can save without PRD.

### Step 4.3 — Progress rules

Use completed/total task counts when sensible; weighted milestone percentage only with valid weights. `No estimate` when there is no meaningful denominator. **Never** derive completion from number of Git commits. Database transaction for multi-row updates; update task states via authorized operations.

### Step 4.4 — Tests

Project isolation; task without milestone; progress excludes Future Backlog; `done` status semantics; invalid transition rejected when rules require; deletion/archive preserves integrity.

**Done:** Task-based workflow is complete with neither PRD nor GitHub.

---

## Phase 5 — GitHub App integration and automatic activity

### Step 5.1 — Register a GitHub App (outside Laravel)

- Separate local/staging/prod webhook URLs where practical.
- Ask only for metadata/contents read and required pull request/issue read permissions.
- Select repositories explicitly; never request blanket organization or write permissions by default.
- Private key remains server-side and secret; never commit it, render it, or include it in AI prompts.

### Step 5.2 — Database

`github_installations`, `repositories`, `git_commits`, `git_pull_requests`, `github_issues`, `webhook_deliveries`, `activities`, optional `task_activity_links`. Reference schema in `erd-migration.md`; adjust migrations consistently rather than improvising fields.

### Step 5.3 — Services and jobs

Organize for testability:

```text
app/Services/GitHub/GitHubClient.php
app/Services/GitHub/SyncRepositoryService.php
app/Http/Controllers/Webhooks/GitHubWebhookController.php
app/Jobs/GitHub/SyncRepositoryJob.php
app/Jobs/GitHub/ProcessWebhookDeliveryJob.php
```

Implementation sequence:
1. GitHub App installation handoff; validate installation/account belongs to current user/workspace.
2. Choose repositories to connect; store stable provider IDs.
3. Queue initial historical sync in bounded batches, with pagination and rate-limit handling.
4. Webhook controller checks raw request signature with configured secret, checks permitted event/installation, persists unique delivery ID, responds promptly and dispatches job.
5. Worker normalizes events into project activity timeline; deduplicate by provider event/SHA/repository key.
6. Scheduler reconciles missed webhook events and records last successful sync time/errors.
7. Removal/disconnection stops sync safely but does not silently delete user notes and tasks.

**Never trust** a repository ID, installation ID, webhook payload or GitHub text as authorization or executable instructions.

### Step 5.4 — Tests

Fake external HTTP responses (no real GitHub API in unit tests). Test invalid signature, duplicate delivery, missing permission, rate limit/timeout, pagination, renamed repo, removed installation, unrelated webhook type, and user-project isolation.

**Done:** pushed activity from a selected repository appears once in correct project, and app still works if GitHub is down.

---

## Phase 6 — Adaptive Project Resume

Build a deterministic resume based on **available** project signals: activity, notes, tasks, milestone and PRD (if present). Include `last_synced_at`, source list and `not enough data` state.

- Git only: latest commit/PR/issue and context (no project completion percentage).
- Tasks only: open/in-progress/blocked tasks and last notes.
- PRD only: approved requirements and planning state; no invented implementation.
- Name only: simple project overview, "nothing recorded yet".
- AI off: deterministic template summary remains usable.

Snapshot generation may run asynchronously after meaningful events; avoid excessive jobs and prevent stale snapshots appearing as live facts.

**Tests:** all combinations above, empty states, stale connection, evidence provenance and proper authorization.

---

## Phase 7 — Optional PRD, Scope Guardian and decisions

### Step 7.1 — PRD is opt-in

Implement `project_documents`, `prd_versions`, `requirements`, `scope_baselines`, links between tasks and requirements. A project created months ago can add a PRD without recreating itself or losing history.

- Draft PRD editable.
- Approved PRD snapshot immutable (except status metadata); change through new version.
- No PRD? Scope Guardian may use explicitly approved lightweight scope or current task focus.
- No baseline? classify `not_assessable` / idea; never label a definitive PRD violation.

### Step 7.2 — Scope review & Future Backlog

`scope_reviews`, `scope_decisions`, `change_requests`, `scope_events`; schema and transitions are prescribed in `erd-migration.md`.

For **Move to Future Backlog**:
1. Authorize acting user for the project/review.
2. Validate pending review and same project/baseline ownership.
3. In `DB::transaction`, lock pending review, create single backlog item, decision and immutable audit event; mark review resolved.
4. On retry, avoid duplicates with unique index/response semantics.
5. Leave approved PRD, active tasks, milestones and progress unchanged.
6. If feature already coded, flag *code action needed*, not automatic deletion/revert.

### Step 7.3 — Change control

Future idea → change request → impact review → approval → new PRD draft → approve new baseline → tasks eligible. Approving change request should **not directly mutate** approved PRD content.

### Step 7.4 — Tests

Approved snapshot immutable, version transitions atomic, all project IDs consistent, unauthorized attempts forbidden, concurrent double-submit not duplicated, backlog does not increase completion, no-baseline review does not assert out-of-scope.

---

## Phase 8 — Daily Focus and coding-assistant context

- Suggest up to three tasks using rule-based inputs (deadline, priority, readiness, blocker status, inactivity).
- Paused project shouldn't automatically become high priority; blocked tasks suggest unblock work.
- Allow override, defer, and mark done using authorized task operation.
- Generate **copyable context**: active project/task, optional PRD baseline, acceptance criteria, allowed scope, explicit excluded features, definition of done, and available evidence.
- Make clear generated instructions are context, not direct control over third-party ChatGPT/Cursor/Claude sessions.
- Avoid inventing requirements when a project has no PRD.

**Done:** daily focus and context generation work with or without PRD and GitHub.

---

## Phase 9 — Optional AI, evidence and cost safety

AI is a provider adapter, disabled by default. Build deterministic summaries first. Use an application interface only if more than one provider/implementation genuinely requires it.

- Restrict input: structured activity/task context by default, no source code, tokens, secrets or full private diffs.
- Save model/provider identifier, prompt version, source IDs and generated timestamp.
- Treat output as untrusted proposals; validate output schema, avoid HTML injection, never execute AI-generated commands.
- AI cannot approve PRD, create scope changes silently, mark tasks done without rule/user authorization or perform git push.
- Throttle requests, budget usage, cache repeat summaries and handle timeout/failure by showing deterministic state.

**Done:** entire project, task and resume workflow still passes tests when AI credentials are absent.

---

## Phase 10 — PWA and performance polish

- Manifest, app icons, installability over HTTPS, responsive nav and sensible update prompt.
- Service worker caches static assets/offline fallback; authenticated API pages and private project data should **not** be blindly cached in shared/public caches.
- Explicit offline UI: server actions, Livewire updates and GitHub sync require connection.
- Eager load list relationships, paginate tables, index relevant filters, test slow queries, avoid N+1.
- Accessible keyboard/focus states, motion reduction, helpful form validation and readable table density.

**Done:** works well on desktop/mobile, secure caching, performance smoke test completed.

---

## Phase 11 — Pre-production hardening and release gate

### Step 11.1 — Automated quality gates

```bash
php artisan test
vendor/bin/pint --test
npm run build
php artisan route:list
php artisan migrate:status
```

Add higher-value tests before production: auth/authorization, private project isolation, webhook verification/replay, PRD immutability, scope transaction, queue retry, missing integrations, guest redirects, no-secret exposure. Configure a **separate test database**; test config must never point to production.

### Step 11.2 — Human QA

- Create project with **name only**; add tasks and notes; check dashboard/resume.
- Connect selected repo and exercise sync; disable GitHub to verify manual workflow remains available.
- Add PRD later; approve and version it; defer out-of-scope idea to backlog.
- Verify no false percentages; empty and error states make sense.
- Check UI at mobile/tablet/desktop, account/logout, rate limits, stale sessions, keyboard navigation.
- Verify backup restoration in a separate test environment, not just backup creation.

### Step 11.3 — Secrets and privacy

Rotate any accidentally published keys. Keep `.env`, GitHub private key and database dumps outside Git and web root. Ensure APP_DEBUG=false in production; only server/admins see logs and secrets. Introduce retention/deletion policies for external activities and snapshots.

**Release blocker:** failing permission/security tests, non-HTTPS authentication, unknown DB backup state, unverified webhook signatures or unauthorized access between projects.

---

## Phase 12 — Deploy production on Ubuntu + Nginx + MySQL

Assumptions: Ubuntu VPS with a public domain and HTTPS, PHP version compatible with locked dependencies, Composer, Node for build (or CI-built assets), MySQL, Nginx, and Supervisor. **Replace placeholders** below. Do not copy commands blindly to an active server used by other projects.

### Step 12.1 — Provision the server

- OS security updates, firewall (SSH restricted as practical, HTTP/HTTPS), non-root deployment user, secure SSH access, correct DNS.
- Install compatible PHP CLI/FPM and required extensions (PDO MySQL, mbstring, xml, curl, openssl, tokenizer, fileinfo, etc.), Composer, Nginx, MySQL client/server as appropriate.
- Create dedicated MySQL database and user with least privileges; use a separate privileged migration role if operationally appropriate.
- Select target path, e.g. `/var/www/devflow/current`, and decide release strategy (simple deploy or timestamped releases/symlink).
- Backup destination outside the app server and database; test restoration.

### Step 12.2 — Fetch code, install dependencies

```bash
# Run from checked-out application release directory, NOT as root:
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

If your build runs in CI, ship compiled assets with the release and avoid installing Node on production. Do not regenerate `APP_KEY` during redeployment.

### Step 12.3 — Environment / ownership

Create real production `.env` privately (mode 600 where applicable):

```dotenv
APP_NAME=DevFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devflow.example.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devflow_production
DB_USERNAME=devflow_runtime
DB_PASSWORD=REPLACE_WITH_SECURE_SECRET
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
MAIL_MAILER=REPLACE_WITH_WORKING_MAIL_TRANSPORT
```

Set APP_KEY once with `php artisan key:generate` **only on a new installation**. For existing environment, preserve the key. Store Github App IDs/keys/webhook secret by reference in secure server configuration. Use `storage` and `bootstrap/cache` writable by appropriate runtime identities; never make the entire app world-writable.

### Step 12.4 — Nginx web root and TLS

Use `root /var/www/devflow/current/public;` **never the Laravel repository root**. Ensure PHP requests go to the correct PHP-FPM socket and `try_files $uri $uri/ /index.php?$query_string;`; block hidden sensitive files. Configure TLS certificate (Certbot or another trusted provider). Redirect HTTP to HTTPS, enable secure cookies and set sensible upload/body limits.

**Minimal conceptual Nginx fragment** (complete the SSL certificate, socket, server directives and security checks for your host):

```nginx
server {
    listen 80;
    server_name devflow.example.com;
    root /var/www/devflow/current/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }
    location ~ /\. { deny all; }
}
```

**Do not deploy this HTTP-only snippet as the final public config.** Enable tested HTTPS and verify your actual installed FPM socket. For production use Nginx's standard PHP snippet/config with path-info protections as appropriate.

### Step 12.5 — Migrate, cache, worker, scheduler

```bash
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```

- Queue worker: Supervisor-managed, non-root process; e.g. `php artisan queue:work database --sleep=3 --tries=3 --timeout=90` (tune so job timeout is less than queue retry-after).
- Scheduler: one cron entry per minute, running `php artisan schedule:run` as deploy/runtime user. For multiple servers, guard single-instance scheduled work.
- Restart/reload PHP-FPM and Supervisor workers following your release procedure.
- Publish storage symlink only if public files are a real feature: `php artisan storage:link` is not mandatory otherwise.
- Run any cache rebuild and PHP opcache refresh appropriate to your deployment.

**Example cron:**

```cron
* * * * * cd /var/www/devflow/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Example Supervisor worker configuration** (adapt actual paths and PHP binary):

```ini
[program:devflow-queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/devflow/current/artisan queue:work database --sleep=3 --tries=3 --timeout=90
user=www-data
numprocs=1
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/devflow-queue.log
stopwaitsecs=120
```

### Step 12.6 — Production smoke tests

- HTTPS valid; redirect to HTTPS; cookies secure; login/logout and guarded dashboard work.
- `/up` responds as expected (note: HTTP health route alone does not guarantee database/queue readiness).
- Create name-only project; CRUD/task/notes; unauthorized user cannot access it.
- Queue processes a test job; scheduler runs; GitHub webhook receives verified event on public URL and syncs selected repository.
- PWA installability and mobile layout; inspect console and Laravel logs; no APP_DEBUG stack traces.
- Confirm database backup succeeds and can restore in a safe test environment.

### Step 12.7 — Rollback and recovery

- Tag the release; store artifact/composer.lock; maintain previous deployment available.
- Code rollback is easy; schema rollback is not always safe. Prefer additive/backward-compatible migrations and tested forward fixes over `migrate:rollback` on production data.
- Before major schema changes, take verified backup; have recovery owners/commands documented.
- Monitor exceptions, failed jobs (`php artisan queue:failed`), HTTP errors, database disk and scheduled job failures.

**Production DoD:** secure HTTPS app, reproducible deploy, no pending critical tests, functional DB/queue/scheduler, least-privilege access, backups and restore test, monitoring and documented rollback.

---

## Phase 13 — Post-release maintenance

- Regular dependency/security updates with automated tests and changelog review.
- Scheduled database backup + off-server retention + periodic restore drill.
- Observe performance and use query/index evidence before adding Redis or services.
- Review GitHub App permissions as integrations evolve; rotate tokens/keys via secure process.
- Keep active roadmap separate from idea backlog. Any new module goes through PRD/scope approval, not spontaneous code generation.

---

## Repeatable feature development loop (mandatory)

1. **Read:** find P0/P1 requirement and acceptance criteria in `prd.md`.
2. **Explain:** draw request/data flow and compare simple Laravel options.
3. **Design:** identify migration, relationships, validation, policy, transaction, UI states.
4. **Implement one vertical slice:** migration → model → authorization/validation → Livewire/controller → Blade → tests.
5. **Test:** expected behavior, invalid input, unauthorized access, retry/concurrency when relevant.
6. **Style:** run `vendor/bin/pint`, manually inspect responsive UI.
7. **Commit:** meaningful message, e.g. `feat(projects): create name-only project with owner policy`.
8. **Review:** compare actual change against task and PRD. Move unrelated ideas to Future Backlog.

### Commit message conventions

`type(scope): short imperative summary`

Examples:

```text
chore(setup): configure MySQL-backed local environment
feat(auth): add session login and protected dashboard
feat(projects): support project creation without PRD
fix(scope): prevent duplicate future backlog decisions
test(projects): deny cross-workspace project access
docs(roadmap): align Laravel version with installed baseline
```

Create small, coherent commits. No automatic push. Commit assistant CLI is **future scope**, not prerequisite for a good commit history.

### Final no-surprise dependency rule

For every proposed new package or feature, record:

```text
Decision ID:
Problem being solved:
Already-approved PRD requirement:
Built-in Laravel alternative:
Proposed dependency and version:
Benefit versus complexity/security/maintenance cost:
Explicit approval:
```

**Known example:** `laravel/fortify` is *not* required by this guide. If adopted later, record AUTH-002 and update `prd.md`, `env.md`, and this guide before installation. In the current baseline, native Laravel Auth is the approved implementation approach.

## References

- Laravel 13 authentication: https://laravel.com/docs/13.x/authentication
- Laravel 13 deployment: https://laravel.com/docs/13.x/deployment
- Laravel Fortify (optional, not required): https://laravel.com/docs/13.x/fortify
- Laravel queues: https://laravel.com/docs/13.x/queues
- Laravel scheduler: https://laravel.com/docs/13.x/scheduling
- Livewire docs: https://livewire.laravel.com/docs

**End of guide.**
