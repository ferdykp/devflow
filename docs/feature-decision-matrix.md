# Matriks Keputusan Fitur DevFlow

**Tanggal:** 18 November 2026  
**Status:** Rekomendasi produk; Tahap 1–3 belum disetujui.

`Keep / Improve / Integrate / Defer / Retire` adalah rekomendasi arah. Status source menggunakan `Existing / Verified`, `Existing / Needs Improvement`, `Proposed`, `Approved`, `Implemented`, `Tested`, `Deferred`, atau `Rejected` sesuai bukti. Tidak ada fitur besar yang disetujui oleh matriks ini.

| Fitur | Status | Keputusan | Nilai pengguna dan alasan | Konsekuensi teknis / risiko | Prioritas dan status keputusan |
|---|---|---|---|---|---|
| Ikhtisar | Existing / Verified | Keep | Menjawab keadaan kerja dan arah berikut | Ringkasan harus link ke record sumber; hindari angka tanpa denominator | P1; perubahan IA Proposed |
| Hari ini | Existing / Verified | Keep | Membantu memilih dan menjalankan fokus hari ini | Jangan menduplikasi tugas; pertahankan perubahan pada task yang sama | P1; Existing |
| Task inbox | Existing / Needs Improvement | Improve | Menyatukan akses daftar tanpa menyatukan domain datanya | Pilihan scope, filter, URL/deep-link, query ownership | P1; default scope perlu keputusan |
| Proyek nama-saja | Existing / Verified | Keep | First value tanpa PRD/GitHub/AI | Pastikan workspace default/selection tidak menambah setup paksa | P0; Existing |
| Project presets | Deferred | Defer | Bisa mengurangi setup repetitif, tetapi dapat menambah keputusan awal | Template lifecycle dan migrasi opsi; jangan mengubah tipe project | P2; perlu validasi kebutuhan |
| Task weights | Existing / Verified | Improve | Menghasilkan progress berbobot yang lebih representatif | UI menjelaskan bobot dan memastikan denominator; nilai default 1 | P2; Existing |
| Milestone progress | Existing / Verified | Keep | Menunjukkan hasil delivery berbasis tugas | Hanya task terkait milestone; tidak mengarang progres saat kosong | P1; Existing |
| PRD Markdown/TXT import | Existing / Verified | Keep | Memecah brief menjadi task dengan preview, urutan, checklist, trace | Batas file/karakter/format harus jelas; hasil perlu source link balik | P1; Existing |
| PDF/Word import | Deferred | Defer | Mengurangi copy/paste bila frekuensi tinggi | Ekstraksi, resource limit, parser security, preview dan privacy | P2; perlu riset |
| Immutable approved PRD | Existing / Needs Improvement | Improve | Menjaga baseline perubahan | Hash dicatat; belum ada guard database/model; approval PRD berbeda dari change approval | P1; proposal guard/criteria |
| Scope baseline tanpa PRD | Existing / Verified | Keep | Proyek sederhana dapat mendefinisikan batas tanpa dokumen wajib | Source acuan dan perubahan baseline perlu actor/time/version | P1; Existing |
| Scope Guardian | Existing / Needs Improvement | Improve | Mengurangi scope creep dengan alasan dan bukti | Saran advisory; baseline/version anchor; enum historis tidak lengkap | P1; spesifikasi diperlukan |
| Change Requests | Proposed | Integrate | Memisahkan ide, keputusan, revisi PRD, dan task | Perubahan schema/authorization/audit/backfill; jangan menambah alur untuk edit kecil | P2; belum disetujui |
| Development Context Generator | Proposed | Integrate | Menyusun konteks grounded dari task untuk developer/coding assistant | Preview, opt-in bagian, privacy, no secrets, sumber jelas, AI optional | P1 Release 2; belum disetujui |
| Project Resume | Existing / Verified | Improve | Mengurangi waktu kembali ke kerja setelah jeda | Freshness, source link, blocked status dan uncertainty; data stale harus tampak | P1; Existing |
| GitHub multi-repository | Existing / Verified | Keep | Aktivitas beberapa repo menguatkan konteks | Permission, installation scoping, retries, reconciliation, timestamps | P1; provider integration belum Tested |
| GitHub progress auto-completion | Rejected | Reject | Commit bukan bukti task selesai | Tidak boleh memutasi task/PRD/scope otomatis | Rejected |
| AI Git Commit Assistant CLI | Existing / Verified | Keep | Membantu pesan commit tanpa mengirim file lokal default | staged diff only; preview; consent explicit; no stage/push; data sent disclosed | P2; fitur opsional |
| VS Code Extension | Deferred | Defer | Navigasi task/context dapat membantu setelah API jelas | Auth/API/permission surface dan distribusi extension | P3; validasi dahulu |
| Autosave catatan | Existing / Needs Improvement | Improve | Mengurangi kerja simpan manual dan risiko kehilangan teks | Retry, conflict behavior, recovery draft dan status; no data loss on failure | P1; belum disetujui implementasi |
| Notifikasi/feed | Existing / Needs Improvement | Defer | Bisa mengantar ke tindakan yang perlu ditinjau | Event source, read/unread, queue, scheduler, timezone, retries, delivery status | P2; keputusan produk |
| Goal/routine/learning/weekly review/coach | Existing / Verified | Keep | Memenuhi pengembangan personal yang berdiri sendiri | Link opsional; ownership privacy; jangan paksa hubungan ke project | P2; Existing |
| Role kustom | Deferred | Defer | Granular access untuk tim yang lebih kompleks | RBAC migration, policy coverage, UX anggota dan testing | P3; perlu validasi |

## Hasil klasifikasi historis engineering

- **Keep:** task weights, milestone progress, manual scope baseline, scope decisions, PRD version history, evidence-based resume, GitHub activity, commit CLI.
- **Improve:** Scope Guardian, development context, immutable approved PRD enforcement, GitHub sync observability, source link pada task import.
- **Integrate:** Context Generator dalam detail task dan Resume; Change Request sebagai flow lintas backlog, scope decision, dan draft PRD, bukan menu/tabel generik tanpa use case.
- **Defer:** project presets, PDF/Word, notifications, VS Code Extension, custom roles.
- **Retire:** belum ada fitur yang direkomendasikan untuk dihapus. Entry point atau ringkasan duplikat dapat dikonsolidasikan setelah pengujian penggunaan; record sumber dipertahankan.

## Gate persetujuan

Sebelum implementasi P1/P2: tetapkan persona prioritas, inbox default, bentuk Change Request solo vs tim, kebutuhan notification delivery, dan batas data AI. Perubahan database yang mengikat scope decision ke versi PRD harus punya migration/backfill plan dan bukti kompatibilitas sebelum coding.

## Addendum status — 18 November 2026

Instruksi implementasi terbaru telah menyelesaikan paket lokal Context Generator, manual scope baseline + version anchors, Scope Guardian klasifikasi lengkap, Change Request/PRD draft separation, approved PRD immutability, in-app notifications, dan autosave retry. Baris matrix di atas merekam status pada saat audit; fitur lanjutan seperti scheduled reminders/email, presets, PDF/Word, custom roles, VS Code Extension, dan deployment tetap deferred.
