# Operasi, backup dan pemulihan

Panduan ini memisahkan prosedur yang sudah diuji lokal dari layanan yang masih perlu dipasang di server. Tidak ada layanan produksi yang dikonfigurasi dari sesi ini.

## Backup dan restore

Target awal yang diusulkan: **RPO 15 menit, RTO 60 menit**. Ini target, bukan SLA terverifikasi. Pemilik layanan harus menyetujuinya setelah mengukur durasi dump, transfer dan restore dengan ukuran data realistis.

Buat file kredensial di luar repository/public dengan permission 0600, misalnya `/etc/devflow/backup.cnf`:

```ini
[client]
host=127.0.0.1
port=3306
user=devflow_backup
password=ISI_DARI_SECRET_MANAGER
```

Jangan mengirim file itu melalui chat atau memasukkan password sebagai argumen command line. Gunakan akun backup least privilege sesuai kebutuhan `mysqldump` pada versi MySQL server. Skrip mendump tabel/triggers aplikasi; tidak menyertakan user/grant server atau stored routines/event server yang tidak digunakan DevFlow.

```sh
bash scripts/backup-mysql.sh /etc/devflow/backup.cnf devflow /var/backups/devflow
```

Skrip memakai snapshot transaksi, pipefail, file sementara berizin privat, uji gzip dan SHA-256 sebelum hasil dipublikasikan. Database harus memakai tabel transaksional dan tidak boleh ada DDL bersamaan dengan dump. Verifikasi juga kapasitas disk dan umur backup terbaru.

**Wajib sebelum production:** enkripsi hasil menggunakan sistem pengelolaan kunci yang dipilih pemilik, unggah ke storage/akun terpisah, verifikasi checksum di sisi tujuan, dan gunakan retensi contoh 7 hari backup interval + 30 backup harian. Jangan menghapus backup lama sampai salinan terbaru terverifikasi. Enkripsi, transfer off-site dan kebijakan retensi otomatis belum diimplementasikan karena layanan/kunci tujuan belum tersedia.

Untuk restore drill, operator membuat database **baru dan kosong** bernama `devflow_restore_<unik>` dengan charset yang sama. Jangan menggunakan database aplikasi:

```sh
bash scripts/restore-drill-mysql.sh /etc/devflow/restore.cnf devflow_restore_DRILL /secure/backup.sql.gz
```

Skrip memeriksa checksum, gzip, prefix target dan jumlah tabel sebelum impor. Ia menolak database berisi tabel, tidak membuat/menghapus database, dan tidak menyentuh database production. Uji akses aplikasi dengan environment khusus restore, jumlah data, relasi penting, hash konten PRD, login QA, dan task lifecycle sebelum menganggap recovery berhasil. Simpan catatan waktu mulai/selesai, hash backup, versi MySQL, ukuran dan hasil pemeriksaan. Cutover ke hasil restore memerlukan persetujuan karena transaksi sesudah backup dapat hilang.

**Bukti lokal:** dump dan restore MySQL ke database terpisah berhasil; drill awal memulihkan satu akun sintetis dan tujuh migration. Versi skrip dengan checksum diuji lagi, termasuk penolakan target yang tidak kosong. Ini bukan uji pemulihan infrastruktur production atau backup off-site.

## Queue dan scheduler

Gunakan template systemd worker di `scripts/deployment/queue.service.example`; sesuaikan user/path/PHP. Database queue `retry_after=90` harus lebih panjang dari worker timeout 60 dan job timeout 45. Setelah deploy restart worker. Periksa:

```sh
php artisan queue:failed
php artisan schedule:list
php artisan devflow:github-reconcile --stale=30
```

Jangan menjalankan retry semua job secara membabi buta. Cari penyebab provider/credential terlebih dahulu, lalu retry job/delivery yang ditinjau; beberapa delivery yang sudah terminal failed memerlukan rekonsiliasi operasional tersendiri. Payload/job bersifat idempoten untuk aktivitas commit dan status hasil delivery.

Scheduler harus berjalan setiap menit tepat pada satu konfigurasi yang dikelola. Jadwal source: sinkronisasi repository setiap jam, rekonsiliasi delivery setiap sepuluh menit, dengan proteksi overlap. `schedule:list` lokal membuktikan pendaftaran, bukan cron server yang berjalan.

**Bukti lokal worker:** job sintetis tanpa repository eksternal didispatch ke database MySQL QA dan diproses oleh `queue:work --once`; pending dan failed jobs sesudahnya nol. Retry jaringan nyata, restart systemd dan recovery host masih BLOCKED.

## Monitoring dan alert

Sebelum rilis tentukan penanggung jawab insiden dan saluran alert. Jadwal minimum yang disarankan:

- Probe HTTPS `/up` setiap menit dari luar host; alert setelah tiga kegagalan berturut-turut.
- Error 5xx dan latensi p95; tentukan ambang berdasarkan baseline staging, bukan perkiraan.
- Queue tertua melebihi lima menit, failed jobs bertambah, delivery stuck dan proses worker mati.
- Scheduler heartbeat hilang lebih dari tiga menit; pastikan heartbeat benar-benar diterbitkan oleh operasi yang berhasil.
- Disk melewati 80%, koneksi/slow query MySQL, backup lebih tua dari RPO dan kegagalan upload/checksum.
- TLS kedaluwarsa dalam 14 hari dan pemakaian provider eksternal bila diaktifkan.

Logging production gunakan kanal harian dengan level warning/error yang sesuai, retensi dan rotasi. Jangan log password, Authorization, token, PEM, dump database atau isi PRD. Service worker/cache tidak menjadi backup data. Tidak ada vendor monitoring/alert berbayar yang dipasang; verifikasi pengiriman alert nyata masih BLOCKED.

## Troubleshooting

- Asset lama/hilang: pastikan manifest cocok dengan artifact, `public/hot` tidak ikut terdeploy, dan reload worker/FPM setelah pergantian rilis.
- 419: periksa domain/HTTPS cookie, session storage, APP_KEY dan header proxy; jangan menonaktifkan CSRF.
- 429: periksa limiter akun/IP dan kapasitas upstream; tunggu Retry-After. Tes memiliki cache QA sendiri dan tidak membutuhkan pengecualian production.
- GitHub kosong: periksa izin pengguna dan instalasi, worker, log aman dan last_synced_at; jangan menampilkan keberhasilan integrasi tanpa verifikasi.
- AI tidak aktif: core workflow tetap berfungsi; aktifkan hanya dengan persetujuan transfer data dan provider yang disetujui.
- Error database: verifikasi jaringan/izin akun dan recovery plan, bukan menjalankan migrasi destruktif.


## Mengoperasikan fitur tim dan tugas dari dokumen

1. Di Proyek, klik nama ruang kerja untuk membuka Anggota. Pemilik mengisi email, membuat undangan, lalu menyalin dan membagikan tautan. DevFlow tidak mengirim email otomatis; tautan berlaku tujuh hari dan hanya dapat diterima akun dengan email yang diundang.
2. Calon anggota membuka tautan, masuk/mendaftar dengan email tersebut, lalu memilih Terima undangan. Pemilik dan anggota dapat mengelola konten seluruh proyek dalam ruang kerja; hanya pemilik mengelola anggota lain.
3. Buka proyek → Dokumen → Buat tugas dari dokumen. Tempel teks atau unggah TXT/Markdown UTF-8 maksimal 200 KB. Alternatifnya, buka versi PRD tersimpan dan pilih Buat tugas dari versi ini. Untuk PDF/Word, salin teksnya terlebih dahulu.
4. Tinjau draf: pilih tugas yang diperlukan, ubah judul/deskripsi, prioritas, penanggung jawab dan tenggat, kemudian tekan Buat tugas terpilih. Pemecahan bersifat lokal berdasarkan struktur dokumen, bukan analisis AI; review pengguna diperlukan. Maksimal 50 kandidat per dokumen, tanpa pemotongan diam-diam.
5. Buka tugas dari halaman hasil atau judul tugas pada Papan/Daftar. Gunakan detail tugas untuk diskusi dan pengaturan. Cari dan filter tugas menyediakan pencarian, status, prioritas, Ditugaskan ke saya dan Belum ditentukan.
6. Pengiriman ulang dokumen identik dalam proyek yang sama membuka hasil sebelumnya. Konfirmasi yang sudah selesai tidak menciptakan tugas tambahan. Ubah sumber dokumen bila memang ada kebutuhan baru; konversi tidak mengubah persetujuan PRD atau baseline.
7. Pemilik dapat membatalkan undangan tertunda atau mengeluarkan anggota. Pencabutan akses langsung membersihkan penugasan dan Fokus terkait; undangan lama yang sudah digunakan tidak dapat memulihkan keanggotaan. Komentar terdahulu tetap tersedia sebagai konteks proyek.

Migrasi tambahan pada rilis ini harus dijalankan sebelum source baru dipakai. Pada lokal sudah diterapkan. Rollback migration menghapus data kolaborasi/impor yang baru; setelah fitur dipakai, gunakan backup dan perbaikan maju sesuai prosedur deployment, bukan rollback database otomatis.
