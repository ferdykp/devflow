# Arsitektur Teknis DevFlow — Audit dan Arah

**Tanggal:** 18 November 2026  
**Status:** Gambaran source saat ini dan usulan evolusi; tidak ada refactor besar yang disetujui.

## Stack terverifikasi

- Laravel Framework 13.35.0; PHP CLI 8.5.0.
- Blade, Tailwind CSS 4.3.3, Vite 8.0+, Vanilla JavaScript.
- MySQL pada aplikasi terkonfigurasi; E2E menggunakan SQLite sementara terisolasi.
- PHPUnit 12.5.38; Playwright 1.56.1.
- Laravel Boost 2.10.3.

Tidak ada kebutuhan rewrite framework atau tambah dependency dari audit ini.

## Batas domain saat ini

1. **Kerja pribadi:** personal task, Hari ini, fokus, kalender.
2. **Delivery:** workspace/project/task/PRD/import/milestone/scope/backlog/activity/GitHub/resume.
3. **Pengembangan personal:** goal/routine/learning/note/weekly review/coach.

Model domain tetap terpisah. Relasi eksplisit memakai foreign key seperti task → project/import/milestone, event → task/routine/goal/lesson, dan goal → task/routine/learning. Tidak ditemukan kebutuhan membuat polymorphic “content/item” generik.

## Arsitektur request dan aturan akses

- Route web mengarah ke controller dan Blade; business logic kompleks tertentu diekstrak ke service (`ProjectResumeService`, `DocumentTaskParser`, `GitHubActivitySync`, `ProjectInsights`).
- Banyak kontrol akses proyek dilakukan melalui `ProjectAccess::find()` dan relasi query yang dibatasi proyek; workspace membedakan owner dan member.
- Webhook GitHub memverifikasi HMAC SHA-256, membatasi payload, melakukan dedup delivery, menyimpan kerja per repository, dan menyediakan command reconciliation.
- Aksi task/scope kritis memakai transaksi; assignment dan milestone divalidasi terhadap participant/project.
- Otorisasi saat ini bertumpu pada scoped query/support/controller; jangan menambah Policy baru secara mekanis tanpa memetakan semua route dan pola aplikasi.

## Relationship data penting

```text
Workspace → Project → Task
                    ├→ ProjectDocument → PRDVersion → DocumentTaskImport → Task source index
                    ├→ Milestone → Task
                    ├→ ScopeBaseline / ScopeReview → ScopeDecision / ScopeEvent
                    ├→ BacklogItem
                    ├→ GithubRepository → Activity / delivery jobs
                    └→ ProjectNote

User → PersonalTask → Goal / LearningLesson
                    ├→ FocusItem
                    └→ CalendarEvent
Goal → Routine / LearningPath / PersonalNote / CalendarEvent
LearningPath → Module → Lesson → Session / Assessment / Material
WeeklyReview → carry-forward task IDs (JSON snapshot)
```

Schema aktual sudah cukup untuk relasi inti yang disebut v2.1. Tidak ada migrasi yang direkomendasikan dalam audit ini. Weekly review menyimpan beberapa daftar ID sebagai JSON snapshot, sehingga link/ID usang perlu recovery UI jika workflow itu ditingkatkan.

## Integrasi dan privasi

- GitHub sync merupakan source remote dan eventually consistent. Tampilkan last sync, error, retry, dan reconciliation; jangan auto-complete task.
- AI project insight hanya dimulai melalui tindakan pengguna dan bersifat advisory; payload membatasi data project, task, aktivitas, dan excerpt PRD. Context Generator belum ada.
- CLI commit assistant berjalan lokal, memakai staged diff, tidak stage atau push, dan mengirim diff hanya setelah `--ai --send-diff` eksplisit.
- Tidak ada file import PDF/Word; import dibatasi teks/Markdown UTF-8, maksimum 200.000 karakter, ukuran file 200 KB, dan parsing preview/review.

## Risiko arsitektur dan pekerjaan yang perlu desain

| Area | Temuan source | Arah aman |
|---|---|---|
| Approved PRD | hash/actor/time disimpan pada approval; revisi membuat versi baru; tidak ada endpoint revisi approved | Tetapkan immutable invariant di model/database pada proposal terpisah; audit legacy versions sebelum backfill. |
| Scope source | Review/baseline scoped project, belum scoped pada version anchor; enum berbeda dari PRD historis | Rancang nullable version/source reference dan backfill berbukti; jangan mengarang anchor. |
| Notifications | Preferensi tersimpan, feed empty state; belum ada pipeline delivery yang ditemukan | Jangan menjanjikan reminder terkirim; desain event, scheduler, timezone, retry, read state sebelum implementasi. |
| Autosave | status menyimpan/tersimpan/gagal; kegagalan tidak membersihkan input | Tambahkan retry/draft recovery hanya setelah conflict/ownership semantics jelas. |
| Migration history | Ada dua migration routine/check-in dengan migration kemudian guarded/no-op karena tabel sudah dibuat lebih awal | Pertahankan idempotent history; migrasi baru harus diuji fresh install dan upgrade path, jangan melakukan squash pada data produksi. |

## Kriteria perubahan teknis berikutnya

- Gunakan tabel domain eksplisit hanya saat relasi menambah manfaat yang nyata.
- Tulis Form Request/validasi mengikuti pola lokal; selalu scoped ke project/workspace yang dapat diakses.
- Gunakan transaksi untuk perubahan lintas record dan idempotency untuk webhook/import.
- Sertakan audit actor/time untuk keputusan; jangan ubah approval atau progress lewat integrasi otomatis.
- Migration memiliki rencana upgrade/backfill, rollback dan bukti pada database tes khusus.
- Beban GitHub/AI dijalankan dengan timeout/retry yang terukur; secret tidak masuk log/payload tak perlu.

## Addendum source lokal — 18 November 2026

- Migration menambah nullable `prd_version_id` pada `scope_baselines`, `scope_reviews`, dan `scope_decisions`; referensi menghapus menjadi NULL. Histori lama dibiarkan unknown dan tidak dibackfill.
- Baseline manual menyimpan `constraints`, `assumptions`, `success_criteria`, dan `definition_of_done`.
- `change_requests` mengikat project, optional scope review, source/draft versions, requester/decider, status, reason, dan timestamp keputusan. Task creation tetap langkah terpisah.
- `PrdVersion` menolak perubahan content/hash/identity dan delete setelah approved/superseded, sementara transisi status ke superseded tetap sah.
- Laravel database notifications menyimpan event, title/message, internal path, dan read state. Event sources: komentar tugas, scope review, Change Request. Queue/scheduler/email reminder tidak diimplementasikan.
- Context Generator mengambil data sesuai akses project, menampilkan bagian pilihan dan redacted preview, serta tidak membaca file lokal atau mengirim data ke provider.
- Personal note autosave sekarang menyediakan retry; teks tetap berada pada form saat request gagal.
