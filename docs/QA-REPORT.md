# Laporan QA — 24 Oktober 2026

## Verifikasi to-do otomatis dari Markdown — 24 Oktober 2026

| Pemeriksaan | Status | Bukti/batas |
|---|---|---|
| Backend SQLite | PASS | 85 tes, 492 assertions, database memori terisolasi |
| Backend MySQL | PASS | 85 tes, 492 assertions, database QA baru yang dibersihkan setelah suite selesai |
| Browser desktop/mobile | PASS lokal | 32 lulus, 6 dilewati sesuai cakupan. Lima matriks duplikat dan satu drag touch dilewati; kontrol status alternatif diuji di mobile |
| Build | PASS | Vite; CSS 73.03 kB/gzip 16.06, JS 9.24 kB/gzip 3.30 |
| Migrasi | PASS lokal | `2026_10_22_114301_add_order_and_checklist_to_tasks_table` terverifikasi berstatus Ran; kolom `sort_order` dan `checklist` tersedia |
| Perilaku otomatis | PASS | Unggahan `.md` langsung menjadi tugas berkelompok dan berurutan; checklist tersimpan setelah refresh; sumber tidak berubah; unggahan ulang tidak menggandakan tugas |
| Alur tinjau | PASS | Mode review tetap tersedia; perubahan kelompok/checklist tersimpan, pilihan tugas mengikuti urutan sumber |
| Batas akses | PASS terbatas | Isolasi plan antarproyek, draf belum dikomit, dan perubahan checklist tanpa hak akses ditolak |
| Source diff | PASS | `git diff --check`; source fitur pada commit `6821712`, pemeriksaan akhir memperbarui dokumentasi |
| Infrastruktur eksternal | BLOCKED | Staging dan kredensial integrasi belum tersedia; hasil lokal tidak menyatakan production-ready |

Pemecahan berbasis struktur Markdown, bukan inferensi dependensi menggunakan AI. Judul bagian/tahap menjadi kelompok, tugas mempertahankan urutan dokumen, subpoin menjadi checklist. Tujuan/latar belakang/referensi dan frontmatter tetap sebagai konteks. Batas saat verifikasi awal ini 200 KB dan 50 tugas; perbaikan batas terbaru dicatat di bawah. Key JSON pada MySQL tidak dijadikan dasar urutan; tes memeriksa urutan item dan nilai selesai secara eksplisit.

Bukti run lengkap disimpan di `/tmp/devflow-structured-evidence`, termasuk screenshot dan laporan browser. Bukti sementara perlu disalin sebelum dibersihkan sistem. Screenshot belum menjadi baseline yang disetujui.

## Riwayat fitur tim dan konversi dokumen — 22 Oktober 2026

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

## Perbaikan batas dokumen besar — 24 Oktober 2026

- Suite backend SQLite: 88 tes / 527 assertions lulus, termasuk impor 1.000 tugas, penolakan 1.001 tugas, konfirmasi semua halaman, pengiriman ulang tanpa duplikasi, dan penandaan kesalahan berkas.
- Build produksi dan Pint lulus. Tidak ada perubahan skema. Verifikasi MySQL pada tabel di atas mendahului perbaikan ini.
- Browser terarah: 8/8 tes Chromium desktop/mobile lulus, termasuk dokumen 75 tugas, tinjauan halaman kedua dan hasil halaman kedua. Bukti tersimpan sementara di `/tmp/devflow-large-document-evidence`.

## Redesign tim kecil — 25 Oktober 2026

- Backend SQLite: 88 tes / 530 assertions lulus; perubahan mencakup halaman awal proyek langsung ke tugas dan pemuatan Ringkasan eksplisit. MySQL tidak diulang untuk redesign ini.
- Browser Chromium desktop/mobile: dari 42 kasus, 32 lulus pada run utama, empat kasus terdampak diperbaiki dan lulus pada rerun, enam dilewati sesuai cakupan. Overflow navigasi pada 375/430px diperbaiki; dua skenario impor disesuaikan untuk membuka menu tugas sebelum mengubah status.
- Alur baru terverifikasi: judul saja + Enter, fokus kembali ke input, selesai/buka kembali setelah refresh. Impor otomatis Markdown, urutan, checklist, tinjauan dokumen besar, penanggung jawab, komentar, undangan, CSRF, menu, dan drag desktop tetap diuji.
- Build Vite dan Pint lulus; CSS 70.41 kB/gzip 15.41, JS 9.31 kB/gzip 3.32. Tidak ada dependency atau perubahan skema baru.
- Screenshot desktop/mobile diperiksa, serta pemeriksaan overflow pada 375, 430, 768, 1280, 1440px. Tidak menyatakan audit aksesibilitas menyeluruh atau kesiapan produksi publik.
- Bukti sementara: `/tmp/devflow-redesign-evidence`, berisi run utama, rerun, hasil backend dan screenshot. Snapshot belum dijadikan baseline yang disetujui.

## Kelola proyek dan responsivitas — 25 Oktober 2026

- SQLite: seluruh 94 tes / 588 assertions lulus. MySQL: 6 tes pengelolaan proyek / 58 assertions lulus pada database QA acak yang kemudian dihapus, mencakup cascade penghapusan dan isolasi proyek lain. Database aplikasi tidak digunakan untuk pengujian destruktif.
- Browser: 41 kasus unik lulus dan 9 dilewati sesuai cakupan. Run utama: 39 lulus, dua skenario undangan masih memakai lokasi form lama. Setelah navigasinya diperbarui, rerun empat kasus lulus: kedua skenario undangan serta audit ulang 320/3840px dengan nama screenshot form yang terpisah. Delapan matriks viewport mobile duplikat dan satu drag touch dilewati.
- Alur baru: pembuatan ruang kerja → proyek terpilih, form terpisah, edit tersimpan setelah reload, batal hapus mempertahankan tugas, dan konfirmasi hapus menghilangkan proyek. Akses tamu, lintas ruang kerja, anggota, pemilik dan anggota yang dicabut diuji di backend.
- Tidak ada overflow halaman pada 320, 375, 430, 768, 1280, 1920, 2560, 3840px. Tinggi 568px digunakan pada mobile kecil. Screenshot proyek baru, edit mobile, index 320px dan tugas monitor besar diperiksa secara visual.
- Build dan Pint lulus. CSS 71.12 kB/gzip 15.55; JS 9.33 kB/gzip 3.33. Tidak menambah dependency atau mengubah skema.
- Bukti sementara tersedia di `/tmp/devflow-project-management-evidence`: run lengkap, rerun, hasil SQLite/MySQL, dan screenshot. Hasil ini tidak menggantikan verifikasi staging atau baseline visual yang disetujui.

## Dialog konfirmasi dan audit terkait — 25 Oktober 2026

- Akar posisi kiri atas: margin bawaan dialog hilang oleh CSS reset tanpa aturan pemusatan eksplisit. Diperbaiki melalui fixed/inset-0/m-auto dan batas viewport.
- Temuan tambahan: submit langsung melewati validasi/handler, form konfirmasi berpotensi masuk status sibuk sebelum pengguna menyetujui, overflow nama panjang pada mobile mengganggu hit target, dan query pencarian bertipe array memicu error. Semua diperbaiki beserta tes regresi terkait.
- Backend SQLite: 95 tes / 598 assertions lulus. MySQL tidak diulang untuk perbaikan ini; penghapusan MySQL terverifikasi pada bagian sebelumnya.
- Browser run akhir: 55 lulus, 9 dilewati dari 64 kasus. Chromium menjalankan regresi aplikasi lengkap; WebKit desktop/mobile menjalankan tes dialog dan kata sandi. Delapan matriks viewport mobile duplikat dan satu drag touch dilewati sesuai cakupan.
- Delapan tes khusus dialog juga lulus terpisah pada empat kombinasi browser/perangkat. Pengukuran pusat dan batas dialog mencakup 320×568, 568×320, 1280×800, 3840×900 serta resize saat terbuka ke 375×667. Pembatalan tidak mengirim penghapusan, fokus/scroll dipulihkan, konfirmasi menghasilkan satu request.
- Build dan Pint lulus. CSS 71.25 kB/gzip 15.58; JS 9.96 kB/gzip 3.57. Tidak ada dependency atau migrasi baru. Log browser terbaru hanya menunjukkan koneksi Vite; error backend terakhir bertanggal sebelumnya merupakan pembatasan koneksi sandbox, bukan temuan baru.
- Bukti dan screenshot di `/tmp/devflow-dialog-evidence`, termasuk run khusus, run lengkap dan hasil backend. Screenshot desktop/mobile WebKit diperiksa. Verifikasi ini mencakup alur yang diuji, bukan klaim seluruh kemungkinan bug telah hilang.
