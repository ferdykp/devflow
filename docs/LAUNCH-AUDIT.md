# Audit DevFlow v1.0 — kandidat rilis

Tanggal: 21 Oktober 2026. Keputusan saat ini: **NO-GO untuk production publik**. Temuan di bawah berasal dari source dan pengujian lokal, bukan sertifikasi environment production.

## Cakupan

Inventaris mencakup dependency dan lockfile, route web/API/console, controller, Form Request, middleware, model/relasi, services, jobs, migrasi, Blade, CSS/JS, PWA, konfigurasi, tes PHP/browser/CLI, dan dokumen staging. Proyek memakai Laravel 13.35.0, PHP 8.5, PHPUnit 12.5.38. Tidak ditemukan direktori Policies; otorisasi dilakukan melalui `ProjectAccess` dan query relasi yang dibatasi proyek/pemilik. Tidak ada upload file pengguna pada alur yang diaudit.

| ID | Prioritas | Lokasi | Masalah dan akar penyebab | Dampak | Perbaikan/kriteria keberhasilan |
|---|---|---|---|---|---|
| SEC-01 | P0 | `GitHubAppController::callback`, `GitHubAppClient` | Daftar repository memakai token instalasi App, bukan irisan izin pengguna OAuth | Repository privat organisasi dapat ditawarkan ke pengguna yang tidak berhak | Callback memakai `/user/installations/{id}/repositories` dengan token pengguna; regresi membuktikan scope request dan penolakan ID di luar pilihan. Diperbaiki lokal; verifikasi GitHub nyata BLOCKED |
| FUNC-01 | P1 | `_task_actions.blade.php` | API native `requestSubmit()` berubah menjadi `requestKirim()` saat penerjemahan | Pilihan status di desktop/mobile tidak tersimpan | Pulihkan API native; tes browser mengubah status, refresh, dan memeriksa status persisten. PASS |
| QA-01 | P1 | `playwright.config.ts`, `e2e/fixtures.ts` | Session/cache file dipakai bersama aplikasi; limiter guest login/register berbagi key IP | E2E tidak terisolasi dan berulang gagal 429 | Database SQLite acak per run, session/cache database QA, reset cache QA per skenario; limiter akun+IP login dan limiter register terpisah. Tes tetap membuktikan 419/429. PASS |
| FUNC-03 | P1 | `_task_actions`, `_section_tasks`, `app.js` | Backend mendukung edit, tetapi UI hanya menyediakan status | Judul/deskripsi/prioritas tugas tidak dapat diubah lewat UI | Tambah formulir edit Blade pada tampilan daftar dan PATCH yang sudah ada; query edit divalidasi dan task dibatasi pada proyek. Regresi desktop/mobile memeriksa persistensi dan fokus |
| FUNC-02 | P1 | `_section_prd.blade.php` | Halaman hanya menampilkan nomor/status versi; isi dan revisi tidak tersedia di UI | Dokumen tersimpan tetapi tidak bisa dibaca/direvisi lewat UI | Tampilkan isi Markdown ter-escape, versi baru, persetujuan berkonfirmasi; backend lama tetap dipakai. PASS browser |
| UI-01 | P2 | `app.css` | `font:inherit` di luar layer menimpa ukuran font utility | Label status terpotong dan kontrol tidak konsisten | Batasi ke `font-family:inherit`; build dan screenshot terbaru diverifikasi |
| UI-02 | P2 | `sidebar.blade.php`, `app.js` | Tombol tutup berposisi di luar sidebar tetap tampak saat sidebar ditransformasikan keluar layar | Ikon tutup menimpa hamburger; menu tersembunyi masih dapat menerima fokus | Visibilitas mengikuti `data-open`, sidebar mobile tertutup memakai `inert`, area utama inert saat menu dibuka; regresi viewport dan interaksi |
| UI-03 | P2 | Blade/controller/AI/PWA, `.env.example` | Teks Inggris dan nilai enum internal muncul sebagai label; locale template awal Inggris | Bahasa antarmuka tidak konsisten | Terjemahkan teks tampilan, pertahankan identifier; template locale `id`, tanpa mengubah `.env` pengguna |
| BUILD-01 | P2 | `vite.config.js` | Unduhan Instrument Sans diperlukan saat build meskipun layout memakai Inter/system | Fresh build bergantung jaringan untuk font yang tidak digunakan | Hapus konfigurasi font yang tidak dipakai. `npm ci` dan build lulus, tanpa warning CSS/font |
| BUILD-02 | P2 | `app.css` | Pemindaian otomatis Tailwind mencakup laporan tes yang dihasilkan | Isi CSS berubah mengikuti artefak lokal | Gunakan `source(none)` dan daftar source Blade/JS/pagination eksplisit; sesuai dokumentasi Tailwind v4 |
| SEC-02 | P2 | `SecurityHeaders`, `session.php`, `ProductionPreflight` | Header browser dan pemeriksaan cookie/cipher belum lengkap | Environment kurang aman dapat lolos pemeriksaan parsial | Tambah header minimum, default secure cookie production, validasi key sesuai cipher, locale dan cookie di preflight |
| INT-01 | P2 | `GithubController`, `_section_github` | Tidak ada kontrol memutus repository lokal | Pengguna tidak bisa menghentikan sinkronisasi dari UI | Tambah DELETE terotorisasi, konfirmasi dan regresi isolasi; riwayat aktivitas dipertahankan |
| OPS-01 | P1 release gate | Operasi/CI | Dokumen mengacu CI yang tidak ada; belum ada contoh worker/Nginx/backup teruji | Rilis dan pemulihan sulit direproduksi | Workflow quality dan template operasi ditambahkan, backup/restore MySQL lokal diuji. Infrastruktur nyata tetap BLOCKED |

## Temuan tersisa dan batas audit

- **P1 release gate, BLOCKED:** host staging, domain/TLS, kredensial GitHub/AI dan akses operasi belum tersedia; dikonfirmasi pengguna. Deploy, rollback server, monitoring/alert, backup off-site dan restore bencana belum dibuktikan.
- **P2, NOT TESTED:** belum ada baseline visual yang disetujui. Screenshot adalah kandidat, tidak diterima otomatis sebagai baseline. Audit WCAG 2.2 AA menyeluruh, Safari asli/password manager, browser/OS lain dan pengujian beban belum selesai.
- **P2, keterbatasan skala:** papan tugas dipaginasi 60 item; jumlah kolom berlaku pada halaman aktif. Resume mengembalikan maksimal enam tugas terbuka. GitHub list repository dibatasi sepuluh halaman, installation list seratus; evaluasi organisasi besar sebelum mengaktifkan fitur tersebut di sana.
- **P2, dependensi runtime:** ikon masih berasal dari CDN Font Awesome yang sudah ada. Fungsi form tidak bergantung ikon, tetapi tampilan ikon memerlukan jaringan/CDN. Self-hosting aset berlisensi dapat dikerjakan sebelum kebijakan CSP penuh diberlakukan.
- Model selain User memakai `$guarded=[]`; jalur write saat ini memakai data tervalidasi dan relasi terotorisasi. Tidak dilakukan refactor massal tanpa defect yang terbukti; pengujian menolak payload ownership yang tidak tervalidasi.
- PWA hanya menyediakan fallback offline publik, bukan cache proyek atau dukungan kerja offline. CSS minimal pada fallback sengaja mandiri agar tetap berfungsi tanpa bundle/network.

Lihat laporan QA, keamanan, operasi dan GO/NO-GO untuk bukti dan pekerjaan eksternal yang masih diperlukan.

Referensi pembatasan source: [Tailwind CSS](https://tailwindcss.com/docs/detecting-classes-in-source-files).

Kontrol CRUD mobile diverifikasi pada tampilan daftar. Interaksi native setelah pengguliran horizontal papan pada perangkat sentuh asli masih perlu pemeriksaan staging; emulasi menunjukkan perbedaan koordinat visual/layout viewport. Ini tidak dipakai sebagai bukti PASS untuk seluruh interaksi papan sentuh.
