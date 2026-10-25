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
3. Buka proyek → Dokumen → Buat tugas dari dokumen. Unggah `.md` melalui kolom Atau unggah berkas (atau tempel teks), lalu pilih Langsung buat to-do list pada Setelah dokumen dibaca dan tekan Proses dokumen. Mode otomatis sudah dipilih secara default. TXT/Markdown UTF-8 maksimal 200 KB didukung. Versi PRD tersimpan tetap dapat dikonversi melalui Buat tugas dari versi ini dengan alur tinjau. Untuk PDF/Word, salin teksnya terlebih dahulu.
4. Dalam mode otomatis, DevFlow langsung membuka daftar tugas dari dokumen tersebut. Judul bagian/tahap menjadi kelompok pekerjaan, langkah utama menjadi tugas sesuai urutan sumber, dan subpoin menjadi checklist. Tujuan, latar belakang, dan referensi tetap menjadi konteks. Mode Tinjau draf terlebih dahulu tersedia untuk memilih/mengedit judul, kelompok, deskripsi, checklist, prioritas, penanggung jawab dan tenggat sebelum membuat tugas. Pemecahan bersifat lokal berdasarkan struktur dokumen, bukan analisis AI atau inferensi dependensi. Maksimal 1.000 tugas per dokumen, tanpa pemotongan diam-diam. Untuk lebih dari 50 tugas, tinjauan menampilkan 25 tugas per halaman dan tombol Buat semua membuat seluruh halaman sekaligus; detail dapat diubah setelah tugas dibuat.
5. Buka judul tugas pada Papan/Daftar untuk mencentang checklist, berdiskusi, dan mengubah pengaturan. Checklist tersimpan setelah refresh dan tidak mengubah status tugas secara otomatis. Urutan dokumen tetap sama setelah perubahan status dan perpindahan tampilan Daftar/Papan. Tombol Semua tugas keluar dari tampilan khusus dokumen. Cari dan filter tugas menyediakan pencarian, status, prioritas, Ditugaskan ke saya dan Belum ditentukan.
6. Pengiriman ulang dokumen identik dalam proyek yang sama membuka hasil sebelumnya. Konfirmasi yang sudah selesai tidak menciptakan tugas tambahan. Ubah sumber dokumen bila memang ada kebutuhan baru; konversi tidak mengubah persetujuan PRD atau baseline.
7. Pemilik dapat membatalkan undangan tertunda atau mengeluarkan anggota. Pencabutan akses langsung membersihkan penugasan dan Fokus terkait; undangan lama yang sudah digunakan tidak dapat memulihkan keanggotaan. Komentar terdahulu tetap tersedia sebagai konteks proyek.

Migrasi tambahan pada rilis ini harus dijalankan sebelum source baru dipakai. Pada lokal sudah diterapkan. Rollback migration menghapus data kolaborasi/impor yang baru; setelah fitur dipakai, gunakan backup dan perbaikan maju sesuai prosedur deployment, bukan rollback database otomatis.


Contoh struktur Markdown untuk to-do otomatis:

```markdown
# Website toko
## Tahap 1: Persiapan
1. Siapkan proyek
   - Atur database
   - Jalankan migrasi
2. Siapkan CI
## Tahap 2: Implementasi
### Buat halaman masuk
- Validasi email
- Uji kredensial salah
```

Contoh tersebut menghasilkan tiga tugas dalam dua kelompok. Dua subpoin pada tugas pertama dan dua poin di bawah judul tugas terakhir menjadi checklist yang bisa dicentang.

## Alur sederhana untuk tim kecil — 25 Oktober 2026

1. Buka Proyek, isi nama proyek dan pilih ruang kerja. PRD dan GitHub tidak wajib.
2. Proyek langsung membuka Tugas dalam tampilan Daftar. Ketik judul di Tambah to-do lalu tekan Enter atau Tambah. Setelah dibuat, fokus kembali ke input agar bisa melanjutkan mengetik.
3. Klik lingkaran di samping judul untuk menyelesaikan tugas; klik lagi untuk mengembalikannya ke Belum dikerjakan. Pengaturan status lain, ubah, diskusi dan hapus tersedia melalui menu tiga titik.
4. Gunakan Tugas baru jika perlu menentukan detail sejak awal, atau buka judul tugas untuk mengubah penanggung jawab, tenggat, kelompok dan checklist.
5. Untuk dokumen .md, pilih Buat tugas dari dokumen di bawah input cepat atau buka tab Dokumen. Pilih pembuatan otomatis atau tinjauan draf. Batas tetap 200 KB dan 1.000 tugas.
6. Tampilan Papan, Ringkasan dan Lanjutkan tetap tersedia. Fitur tambahan ada di Lainnya. Anggota tim dikelola dari ruang kerja.

Siap digunakan untuk uji coba lokal. Penggunaan produksi online tetap mengikuti gate staging, integrasi dan pemulihan pada GO-NO-GO.

## Kelola proyek dan ruang kerja — 25 Oktober 2026

- Daftar Proyek hanya menampilkan pencarian, ruang kerja dan kartu proyek. Pilih Proyek baru untuk membuka form terpisah. Pilih Ruang kerja baru untuk membuat ruang kerja di halaman tersendiri; setelah berhasil, form proyek terbuka dengan ruang kerja tersebut terpilih.
- Edit proyek melalui ikon pensil pada kartu di daftar atau tombol Edit proyek di halaman proyek. Nama dan deskripsi langsung terlihat; tujuan, status, prioritas serta target selesai ada dalam Opsi tambahan.
- Hapus proyek tersedia di bawah halaman edit, hanya untuk pemilik ruang kerja. Dialog menyebut nama proyek dan penghapusan permanen tugas, dokumen serta diskusi. Batal mempertahankan proyek. Pilih status Diarsipkan jika ingin menyimpan data tanpa melanjutkan pekerjaan.
- Sidebar desktop memakai rail 80px dengan label aksesibel dan tooltip. Pada mobile, tombol navigasi membuka menu berlabel. Tugas dan Dokumen menjadi tab utama mobile; Ringkasan dan Lanjutkan dapat dibuka dari menu lainnya.
- Layout diverifikasi pada lebar 320–3840px. Form dibatasi lebarnya agar mudah dibaca; daftar proyek memakai grid responsif dan area tugas dapat memakai ruang lebih luas pada monitor besar.
