# Laporan QA — 22 Oktober 2026

## Verifikasi fitur tim dan konversi dokumen — 22 Oktober 2026

| Pemeriksaan | Status | Bukti/batas |
|---|---|---|
| Backend SQLite akhir | PASS | 78 tes, 424 assertions; database memori terisolasi |
| Backend MySQL akhir | PASS | 78 tes, 424 assertions; database QA baru dan dihapus setelah suite selesai |
| Browser seluruh suite | PASS lokal | 30 lulus, 6 dilewati; Chromium desktop dan emulasi mobile. Lima matriks duplikat dan satu drag touch dilewati sesuai cakupan |
| Browser fitur tim akhir | PASS lokal | Empat skenario desktop/mobile: dokumen → pilih/edit/konfirmasi → filter → diskusi; undangan dua akun → pencabutan akses. Diulang setelah label ruang kerja bersama dan tampilan akhir diperjelas |
| Build akhir | PASS | Vite; CSS 72.71 kB/gzip 16.02, JS 9.24 kB/gzip 3.30; tanpa dependency tambahan |
| Format dan diff | PASS | Pint untuk PHP yang berubah; `git diff --check`. Perubahan awal demo seeder pengguna dipertahankan |
| Migrasi lokal | PASS | Migrasi tambahan diterapkan. Jumlah pengguna/ruang kerja/proyek/tugas/versi sumber tetap 3/5/5/18/4 sebelum dan sesudah migrasi |
| Responsif | PASS terbatas | Matriks 375/430/768/1280/1440 px; halaman tinjau, detail/diskusi, dan undangan juga diperiksa lewat screenshot desktop/mobile dan assertion overflow |
| Konversi dengan provider AI | NOT APPLICABLE | Fitur baru memecah teks secara lokal; tidak memakai provider AI |
| Ekstraksi PDF/DOCX otomatis | NOT IMPLEMENTED | UI menjelaskan untuk menempelkan teksnya; unggahan hanya TXT/Markdown |
| Staging/production/integrasi eksternal | BLOCKED | Akses dan kredensial masih belum tersedia; hasil lokal tidak membuka release gate |

Regresi baru memeriksa pilihan sumber, unggahan tidak sah, batas ukuran/jumlah, escaping XSS, IDOR, indeks/penanggung jawab yang dimanipulasi, transaksi batch, konfirmasi ulang, sumber PRD tidak berubah, undangan salah akun/kedaluwarsa/diganti/dibatalkan/terpakai, pencabutan keanggotaan, komentar, dan filter tugas.

Draf yang tidak dipilih menonaktifkan kontrolnya, sehingga isian kosong pada draf tersebut tidak menghalangi tugas yang dipilih. Tombol konfirmasi berada setelah daftar draf agar tidak menutupi kolom isian. Undangan ruang kerja bernama sama dibedakan dengan nama pemilik pada pilihan ruang kerja bersama.

Satu run browser saat source/build masih berubah mencatat POST registrasi terputus (`net::ERR_ABORTED`). Penyebab tidak disimpulkan sebagai rate limiting dan assertion tidak dilemahkan; suite diulang dengan source/build stabil dan lulus. Masalah selector awal (label opsional dan label ruang kerja ambigu) juga diperbaiki sebelum run akhir.

Bukti saat ini: `/tmp/devflow-small-team-evidence/full-suite` untuk seluruh suite, dan `/tmp/devflow-small-team-evidence/final-team` untuk screenshot/laporan tampilan akhir fitur tim. Artefak sementara perlu disalin sebelum dibersihkan sistem. Screenshot belum menjadi baseline visual yang disetujui.

## Riwayat verifikasi stabilisasi — 21 Oktober 2026

| Pemeriksaan | Status | Bukti/batas |
|---|---|---|
| Prasyarat | PASS | PHP 8.5 tersedia; Composer tersedia; Boost terpasang |
| Install JS dari lockfile | PASS | `npm ci` selesai, 146 package |
| Backend SQLite | PASS | `php artisan test`: 59 tes, 265 assertions |
| Backend MySQL | PASS | 59 tes, 265 assertions pada database `devflow_qa_*` baru; database aplikasi existing tidak dipakai |
| Build | PASS | `npm run build`, tanpa warning CSS/font; JS sekitar 8.09 kB, gzip 3.04 kB; CSS sekitar 74.63 kB, gzip 16.71 kB |
| Audit Composer | PASS | `composer audit`: tidak ditemukan advisori |
| Audit npm runtime | PASS | `npm audit --omit=dev`: nol kerentanan yang dilaporkan |
| CLI | PASS | `python3 -m unittest discover -s tests/cli`: empat tes |
| Browser | PASS lokal | Run akhir dengan WebKit: 30 lulus, 6 dilewati sesuai cakupan (5 duplikat matriks dan 1 drag/drop touch). Nol gagal. Chromium desktop/mobile dan password WebKit desktop/mobile |
| Responsif | PASS terbatas | Kandidat screenshot 375, 430, 768, 1280, 1440 px; pemeriksaan overflow dokumen dan JS error |
| Visual regression baseline | NOT TESTED | Belum ada baseline yang disetujui pemilik; screenshot tidak otomatis dianggap baseline |
| Backup/restore lokal | PASS | Dump/gzip/checksum/restore database QA; target berisi tabel ditolak |
| Worker lokal | PASS terbatas | Job sintetis lewat database queue diproses oleh worker nyata; bukan sinkronisasi GitHub nyata |
| Scheduler | PASS registrasi | Dua jadwal source tampil; eksekusi cron berkelanjutan belum diuji |
| CI GitHub Actions | NOT TESTED | Workflow ditambahkan, belum ada run CI remote |
| Staging/TLS/rollback server | BLOCKED | Akses server belum tersedia menurut pengguna |
| GitHub/AI nyata | BLOCKED | Kredensial lingkungan pengujian belum tersedia |
| Aksesibilitas menyeluruh | NOT TESTED | Fokus/label/keyboard tertentu diuji; bukan sertifikasi WCAG AA |

## Menjalankan ulang

```sh
npm ci
npx playwright install chromium
npm run build
php artisan test
npm run test:e2e
```

WebKit opsional, supaya fresh clone yang hanya memasang Chromium tetap menjalankan seluruh browser wajib:

```sh
npx playwright install webkit
DEVFLOW_E2E_WEBKIT=1 npm run test:e2e
```

E2E memakai SQLite acak per run di temp directory, database session/cache sendiri dan cache reset sebelum setiap skenario. Middleware keamanan tidak dinonaktifkan. Skenario eksplisit membuktikan 419 tanpa CSRF, lima penolakan kredensial dan 429 pada percobaan keenam. Pengujian MySQL harus memakai database tes **khusus dan kosong**; `RefreshDatabase` boleh menghapus tabel pada database yang dipilih, sehingga jangan menyalin command tersebut ke environment pengguna/production.

## Screenshot dan baseline

`launch-readiness.spec.ts` menyimpan 80 kandidat screenshot halaman utama di `test-results`. Data sintetis menggunakan nama tampilan tetap, viewport tetap dan animasi dinonaktifkan. Inspeksi screenshot menemukan tombol tutup sidebar yang menimpa hamburger; source dan regresinya diperbaiki. Kandidat harus ditinjau pada OS/browser/font yang akan menjadi standar sebelum disetujui. CDN ikon dan perbedaan platform tetap dapat memengaruhi piksel.

Untuk membandingkan baseline yang sudah ditinjau dan dicatat di repository:

```sh
DEVFLOW_VISUAL_COMPARE=1 npx playwright test e2e/launch-readiness.spec.ts --project=desktop-chromium --grep 'audit viewport'
```

Mode ini gagal bila baseline belum ada. Jangan menerima snapshot baru hanya untuk meloloskan regresi; persetujuan baseline merupakan pekerjaan pemilik/reviewer. Screenshot matriks diuji sekali pada proyek desktop dengan ukuran viewport berbeda; tes interaksi mobile berjalan tersendiri. Drag/drop sengaja tidak dijalankan pada touch emulation karena kontrol status alternatif diuji di mobile.

## Kegagalan yang ditemukan selama pengerjaan

1. Suite lama gagal 429: isolasi cache dan key limiter diperbaiki; tidak ada assertion status yang dilemahkan.
2. Setup tes baru belum memilih workspace: perbaiki setup data agar memakai workspace milik akun; tidak mengubah validasi production.
3. Drag ke kolom di luar viewport tidak menghasilkan drop dalam otomatisasi: tes jalur error memakai dua kolom terlihat dan memeriksa request gagal serta status sesudah refresh.
4. Screenshot menunjukkan font kontrol menimpa utility Tailwind dan ikon tutup mobile bertumpuk: perbaikan source disertai verifikasi ulang.
5. Build setelah `npm ci` memerlukan unduhan font tidak terpakai: konfigurasi font tersebut dihapus.

Hasil iterasi sebelumnya tidak dipakai sebagai pengganti verifikasi akhir source. Release gate tetap pada `GO-NO-GO.md`.

Editor tugas memakai GET proyek `section=tasks&view=list&edit=<id>` dengan query tervalidasi dan relasi scoped, lalu PATCH native. Pembatalan kembali ke daftar. Mobile CRUD diuji melalui daftar; pengetesan kontrol setelah panning horizontal papan menunjukkan perbedaan koordinat emulasi, sehingga papan sentuh asli tetap NOT TESTED. Tidak digunakan klik paksa atau assertion yang mengabaikan hasil simpan.

Bukti run browser terakhir disalin ke `/tmp/devflow-release-evidence` (80 screenshot dan laporan HTML). Arsip bukti: `/tmp/devflow-v1-qa-evidence.zip`. Artefak temp perlu disimpan oleh pemilik sebelum dibersihkan sistem.

Build diulang setelah seluruh screenshot dihasilkan; SHA-256 aset CSS/JS sama dengan build sebelum run. Pembatasan source Tailwind membuat laporan tes tidak mengubah hasil build.
