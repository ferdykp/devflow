# DevFlow implementation status

Laravel 13 / Blade / Tailwind 4 / MySQL. No Livewire or Fortify required.

## Implemented
- Auth with session, password show/hide, live password confirmation, flash messages, login throttling.
- Owned workspaces, projects, tasks, milestones, notes, future backlog, daily manual focus (up to three).
- Optional PRD drafts and approval snapshots; optional manual scope boundaries and review-to-backlog decision.
- Deterministic project resume from tasks/notes/GitHub activity. No fabricated percent.
- Public GitHub repository read-only connect and manual commit sync (last 50 commits).
- Responsive monochrome dashboard and project screens, reusable Blade components.

## Not finished / external setup required
- GitHub App OAuth/installation, private repository scopes, signed webhook ingestion, full issue/PR backfill and worker reconciliation.
- AI assistant and AI progress summaries (no provider credentials included).
- Change request workflow, fully automated PRD comparison and requirements extraction.
- Local staged-diff commit CLI, VS Code extension, installable PWA and offline cache.
- Accessibility audits, complete feature test suite, production load testing.

## Setup
1. Copy `.env.example` to `.env`; configure MySQL DB_DATABASE/DB_USERNAME/DB_PASSWORD; run `php artisan key:generate` only for a brand-new install.
2. `composer install && npm ci && php artisan migrate && npm run build`
3. `php artisan serve` (or `composer run dev` in your environment).
4. Register an account; a Personal workspace is created automatically.
5. For an existing account, use Projects > New workspace before creating projects.
6. Configure a separate test DB before running database-mutating tests.
7. Production: APP_DEBUG=false, HTTPS, queue worker, backup, secrets, CSP, logging, proper permissions.

## Important
Never overwrite a production .env or regenerate an existing APP_KEY. GitHub public sync makes outgoing requests to github.com. No passwords, SSH keys, GitHub private tokens, or source diffs are uploaded to AI providers.
