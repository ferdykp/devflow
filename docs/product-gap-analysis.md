# Analisis Kesenjangan Produk DevFlow

**Tanggal audit:** 18 November 2026  
**Status:** Audit source lokal; usulan perubahan belum disetujui kecuali ruang lingkup Tahap 0 yang sudah tercatat.

Dokumen ini membandingkan PRD historis v1.0 di `docs/prd-2.md`, PRD terbaru v2.1 di `docs/prd.md`, dan source code saat ini. `docs/product-architecture-review.md` sudah berisi rancangan IA, relationship matrix, dan tujuh user journey; dokumen ini tidak menduplikasinya.

## Ringkasan eksekutif

DevFlow sudah memiliki fondasi produk yang luas: kerja pribadi, proyek, impor PRD ke tugas, scope review manual, resume berbasis data, GitHub sync, fokus, kalender, tujuan, rutinitas, pembelajaran, catatan, dan kolaborasi owner/member. Celah terpenting bukan kekurangan modul, melainkan konsistensi pengalaman, keterlacakan keputusan, dan kejujuran status fitur.

Tidak ditemukan alasan berbasis audit untuk mengulang Tahap 0. Perubahan Tahap 1–3 tetap proposal. Tidak ada perubahan aplikasi atau skema yang dibuat dalam audit ini.

## Sumber dan batas bukti

- `docs/prd.md` menyatakan Tahap 0 terbatas telah dilaksanakan dan memuat status v2.1; dokumen itu memiliki perubahan lokal pengguna dan tidak ditulis ulang.
- `docs/product-architecture-review.md` tersedia sebagai file untracked yang disediakan dalam workspace; dipertahankan tanpa perubahan.
- Source route, model, migration, controller, service, Blade, JavaScript, tes, dan konfigurasi diperiksa untuk area yang tercantum di bawah.
- Hasil tes di bagian Verifikasi berasal dari run pada checkout ini. Angka historis di PRD tidak dipakai sebagai bukti run saat ini.

## Perbandingan kebutuhan dan source

| Kapabilitas | PRD v1.0 | PRD v2.1 | Source saat audit | Kesenjangan / keputusan |
|---|---|---|---|---|
| Tiga area produk | Konsep workspace developer yang dapat diperluas | Kerja pribadi, delivery proyek/tim, pengembangan personal | Route dan model terpisah untuk task/proyek, goal/routine/learning/note | Existing / Verified pada model; Improve navigasi dan penjelasan scope. |
| Proyek nama-saja | Tujuan penting; capability opsional | Diwajibkan tetap ringan | Create butuh workspace, nama, deskripsi opsional; fitur lain dapat ditambahkan setelahnya | Existing / Needs Improvement: akses create tetap mengasumsikan pengguna memiliki workspace yang dapat dipilih; first-value flow perlu uji. |
| Proyek preset | Basic, Git-based, Task-based, Structured/PRD, Experimental | Tidak diwajibkan sebagai setup | Tidak ditemukan preset proyek; project create hanya nama/deskripsi/workspace | Defer; jangan menambah pilihan sebelum membuktikan kebutuhan setup berulang. |
| Tugas dan bobot | Status, prioritas, due date, weight | Semua, pribadi, proyek perlu jelas | Tugas proyek punya `weight`; task personal/project dibedakan ownership; ada inbox/list/board/filter | Existing / Needs Improvement: bobot dan cakupan belum konsisten terlihat di seluruh flow. Pilihan default inbox masih keputusan produk. |
| PRD dan traceability | Draft, immutable approval, change control, sumber task | Import preview, source reference, versi | `project_documents`, `prd_versions`, `document_task_imports`, `tasks.document_task_import_id`, `source_index`; import mendukung Markdown/TXT | Existing / Verified untuk alur sumber import. Improve link balik yang jelas pada detail task. Approved content di-hash saat approve dan tidak punya route edit, tetapi belum ada pengaman immutable level database/model. |
| Scope Guardian | 6 klasifikasi, saran advisory, keputusan manusia | Scope review dan baseline opsional | Manual review, baseline in/out, actor/time/reason/evidence; AI `ProjectInsights` bersifat opt-in; keputusan tidak mengubah PRD/baseline | Existing / Needs Improvement: enum source yang terlihat hanya `in_scope`, `out_of_scope`, `uncertain`, `not_assessable`, belum semua enam nilai historis. Baseline/review belum mengikat versi PRD. Goal ada pada project; constraints, assumptions, success criteria belum menjadi field baseline. |
| Change Request | Workflow perubahan yang terkait versi baru | Persetujuan request, PRD, dan task harus terpisah | Scope decision dapat menyetujui usulan untuk perencanaan; tidak ada entity/workflow Change Request tersendiri | Proposed; jangan samakan scope approval dengan approval PRD atau pembuatan tugas. |
| Development Context Generator | Copyable context untuk coding assistant | Preview, pilihan bagian, deterministik, AI opsional | Tidak ditemukan generator konteks tugas khusus; `ProjectInsights` menganalisis project/scope, bukan prompt kontekstual task | Proposed, kandidat pembeda Release 2. |
| Resume | Sumber dan freshness jelas; tidak mengarang progress | Evidence-based resume, next step nyata | `ProjectResumeService` menghitung task, aktivitas, catatan, milestone, progress bobot, last sync, PRD; task berikutnya dipilih dari task terbuka | Existing / Verified secara data. Improve tampilan freshness/source links dan kasus tanpa bukti. |
| GitHub | Banyak repo, verified webhook, backfill, retries, reconciliation | Integrasi opsional, bukti bukan status task | Multi-repo model, webhook signature check, unique delivery, queue/reconciliation command, last sync/error | Existing / Verified secara source, integrasi provider nyata dan operasional staging belum diuji pada audit ini. |
| Pertumbuhan personal | Modul sekunder | Domain mandiri: goal, routine, learning, notes, review, coach | Tabel, model, route, views tersedia; banyak relasi eksplisit | Existing / Verified pada domain; Improve cross-links dan jelaskan mana yang opsional. |
| Notifikasi | Tidak menjadi fondasi V1 | Hanya klaim aktif bila delivery nyata | Preferensi dan route pusat notifikasi ada; view feed empty state statis; tidak ditemukan event feed/scheduler delivery | Existing / Needs Improvement: UI sudah menyebut pengiriman bergantung layanan; jangan klaim reminder terkirim. |
| Autosave catatan | Tidak dirinci | Status menyimpan/tersimpan/gagal, recovery | Debounce 800ms, status simpan dan gagal; gagal tidak menghapus nilai form | Existing / Needs Improvement: tidak ada retry/draft recovery otomatis, kontrol submit manual tetap ada. |
| CLI commit | Fitur historis di luar web | staged diff lokal, preview, consent, tanpa push | `tools/devflow-commit.py` memakai staged diff, preview, consent eksplisit untuk AI, konfirmasi commit, pemeriksaan tree | Existing / Verified sebagai utilitas opsional; pertahankan dan audit batas privasi sebelum perubahan. |
| VS Code Extension | Kandidat masa depan | Setelah API/auth jelas; bukan prasyarat | Tidak ditemukan extension/API khusus | Defer. |

## Kesenjangan prioritas

### P0 — keandalan dan kejelasan

- Pertahankan label konteks Tahap 0 yang telah diubah pengguna; jangan menggantinya atau mengulang pekerjaan tanpa regresi yang dapat direproduksi.
- Pastikan status project, task, import, sync, dan simpan catatan selalu berasal dari source yang nyata.
- Pertahankan isolasi akses owner/member dan project-scoped queries; perlu verifikasi launch terpisah.

### P1 — workflow utama

- Perjelas task inbox: apakah default semua task, personal, atau assigned-to-me; tampilkan scope proyek/pribadi saat daftar bercampur.
- Hubungkan task hasil import kembali ke dokumen, versi, section, dan source position yang tersedia.
- Jadikan resume punya freshness, link bukti, hambatan, keputusan, dan ketidakpastian; jangan membuat rekomendasi menjadi keputusan pengguna.
- Tambahkan preview dan bagian pilihan untuk Context Generator sebelum copy/send.
- Lengkapi state autosave dengan retry/recovery hanya setelah workflow konflik dan ownership ditentukan.

### P2 — kapabilitas lanjutan

- Rancang Change Request dan relasi keputusan ke baseline/versi PRD sebagai proposal schema terpisah; backfill harus dirancang sebelum migrasi.
- Notification delivery, PDF/Word extraction, role kustom, extension, dan team-scale analytics membutuhkan bukti penggunaan/validasi dan desain keamanan.

## Keputusan implementasi

| Kelas kerja | Status | Tindakan |
|---|---|---|
| Label area dan navigasi Tahap 0 | Implemented / perlu verifikasi terbaru | Source lokal sudah mengandung perubahan pengguna; jalankan tes yang relevan dan jangan edit ulang tanpa regresi. |
| Perubahan copy/UX lain | Proposed | Rancang acceptance criteria; tidak diimplementasikan. |
| Context Generator, Change Request, schema linking | Proposed | Siapkan spesifikasi dan rencana migrasi; tunggu keputusan produk. |
| Deploy, perubahan provider, atau perubahan data operasional | Deferred | Tidak dilakukan; deployment production memerlukan persetujuan eksplisit dan staging gate. |

## Verifikasi audit ini

- PHPUnit: **128 lulus, 867 assertions**.
- Playwright Chromium: **47 lulus, 9 dilewati** dari 56 kasus desktop/mobile; satu drag touch dan delapan screenshot viewport duplikat pada mobile dilewati sesuai alasan test.
- CLI Python: **4 lulus**; Vite production build: **lulus**.
- Viewport 1024 px, aksesibilitas menyeluruh, staging, GitHub provider nyata, backup/restore pada environment launch, dan rollback: belum diverifikasi oleh audit ini.

## Addendum implementasi lokal — 18 November 2026

Permintaan eksplisit terbaru mengubah status implementasi untuk paket lokal yang aman:

- **Implemented / Tested:** Context Generator deterministik pada detail task, preview sumber pilihan, format developer/assistant/tim, tautan sumber, redaksi secret, dan copy lokal tanpa provider AI.
- **Implemented / Tested:** baseline manual menyimpan constraints, assumptions, success criteria, dan Definition of Done; project tidak diwajibkan mengisi baseline.
- **Implemented / Tested:** versi PRD nullable terhubung pada baseline, scope review, dan keputusan. Klasifikasi historis tersedia; verdict pasti ditolak tanpa acuan; histori lama tidak dibackfill dengan asumsi.
- **Implemented / Tested:** Change Request mencatat actor, keputusan, alasan, source PRD, dan optional scope review. Draf PRD dibuat eksplisit sebagai versi baru; task creation dan approval PRD terpisah.
- **Implemented / Tested:** approved/superseded PRD tidak dapat diedit/dihapus melalui model Eloquent.
- **Implemented / Tested:** feed notifikasi database untuk komentar tugas, peninjauan scope, dan Change Request; read/unread, mark-all-read, dismiss, badge, serta akses user-scoped.
- **Improved / Tested:** autosave catatan pribadi memiliki retry dan mempertahankan nilai saat gagal.
- Suite saat ini: PHPUnit **139 tes / 924 assertions**; E2E baru **2 skenario pada desktop Chromium dan 2 pada mobile Chromium** (Context/Scope/Change Request dan autosave recovery); Vite build dan Pint lulus.

Reminder terjadwal/email, project presets, PDF/Word, custom roles, VS Code Extension, provider langsung, dan production deployment tetap tidak dilakukan. Production gate masih mengikuti `docs/GO-NO-GO.md`.
