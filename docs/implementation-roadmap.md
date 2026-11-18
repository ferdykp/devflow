# Roadmap Evolusi Produk DevFlow

**Status:** Usulan roadmap. Label release tidak memberikan persetujuan implementasi.

Roadmap memprioritaskan first value, keamanan perubahan, dan kemampuan kembali bekerja. Dokumen IA dan user journeys yang lebih terperinci ada di `docs/product-architecture-review.md`.

## Gate awal — audit/fondasi yang sedang dilakukan

**Status:** Audit selesai; PHPUnit, Playwright, CLI, dan build telah diuji secara lokal. Ini tidak menyatakan produk siap production.

- Pertahankan Tahap 0 yang telah diterapkan pengguna.
- Tidak ada migrasi, perubahan schema, penghapusan fitur, atau deploy dalam audit ini.
- Catat bug hanya setelah dapat direproduksi pada source terbaru.

## Release 1 — Reliable Product Foundation

**Status:** Proposed; belum disetujui sebagai paket implementasi.

1. Verifikasi Task lifecycle, cakupan inbox Personal/Project/All, assignment, dan route deep-link.
2. Uji proyek nama-saja dari onboarding sampai aksi pertama tanpa setup wajib.
3. Pastikan PRD Markdown/TXT preview, import commit idempotency, source anchors, dan authorization lintas proyek.
4. Tampilkan status task, progress berbobot, milestone, dan Resume hanya dari data sumber; empty state tanpa angka palsu.
5. Audit UI dan aksesibilitas alur prioritas: create, filters, list/board, dialogs, keyboard, touch, error recovery.
6. Lengkapi launch-operational proof pada staging terpisah sebelum gate rilis.

**Exit criteria:** suite terarah lulus; bukti browser pada layar yang diminta; no unintended overflow; authorization failure tests lulus; migration path diuji; no unresolved P0; deployment/rollback/backup dinilai terpisah.

## Release 2 — Developer Intelligence

**Status:** Proposed; perlu keputusan scope dan privacy.

1. Development Context Generator deterministic: preview, parts selection, evidence/source links, copy; AI optional.
2. Scope Guardian: baseline/version anchor, uncertainty, klasifikasi yang konsisten, audit actor/time/reason.
3. Change Request flow yang membedakan scope decision, approval PRD, dan task creation.
4. Evidence-based Resume: recent work, blocked task, decisions, PRD changes, GitHub freshness/error, next step suggestions.
5. Integrasi GitHub operasional: verified webhook, idempotency, backfill, retry/reconciliation, sync timestamps/error visibility.

**Exit criteria:** consent/authorization tests; no secret provider payload; baseline absence returns uncertain/not-assessable; approval immutability tested; remote GitHub integration only in non-production test app; human action remains required.

## Release 3 — Productivity & Growth

**Status:** Proposed; prioritas berdasarkan pemakaian pengguna.

- Kalender: perjelas deadline vs scheduled time block.
- Focus: sesi konsisten refresh, task relation, interruption/recovery.
- Catatan autosave: retry, draft recovery, conflict handling.
- Weekly Review: carry-forward links survive deleted/stale task IDs.
- Goal/Learning/Coach: cross-links and review surface without mandatory project relationship.
- Notifications only after event source, scheduler, queue, timezone, retry, read/unread, delivery status are implemented.

## Release 4 — Developer Ecosystem

**Status:** Deferred proposal.

- Keep optional local commit assistant; validate CLI security and user-facing privacy before broader rollout.
- VS Code Extension after authenticated API, permission model, API lifecycle, and demand validation.
- Developer integration APIs only for concrete workflows; advanced team roles after evidence.

## Dependencies and stop conditions

| Dependency | Gate before starting |
|---|---|
| Scope/PRD anchor migration | Define historical null/unknown behavior; dry-run backfill; prove rollback and no approved PRD mutation. |
| Context Generator | Approve included fields, consent, source links, provider data, retention behavior. |
| Notifications | Decide actual channels and delivery contract; scheduler/queue/monitoring/opt-out tests. |
| PDF/Word import | User demand, secure extractor, size/time limits, parsing quality benchmarks, preview. |
| VS Code Extension | API/auth permissions, threat review, extension distribution/updates, user research. |
| Production deployment | Explicit approval, staging pass, backup/restore evidence, monitoring, rollback. |

## Tidak masuk pekerjaan audit ini

Reorganisasi seluruh sidebar, perubahan domain/schema, notifikasi aktif, PDF/Word parser, custom roles, extension, CLI auto-commit, production deploy. Semua memerlukan keputusan terpisah atau persetujuan eksplisit.

## Status setelah implementasi lokal — 18 November 2026

- **Selesai:** Context Generator deterministic, preview sumber pilihan, source links, secret redaction, tiga format output.
- **Selesai:** manual baseline dan Definition of Done; nullable scope/PRD anchors tanpa backfill spekulatif; enam klasifikasi dan keputusan manusia.
- **Selesai:** Change Request → keputusan → optional PRD draft; persetujuan versi PRD tetap tindakan terpisah; guard immutable approved version.
- **Selesai:** notifikasi database in-app untuk event proyek, read/unread, dismiss, user isolation.
- **Selesai:** retry autosave untuk catatan.
- **Masih deferred/not tested:** scheduled reminders/email, PDF/Word import, project presets, custom roles, VS Code Extension, GitHub provider langsung, staging/production deployment.
