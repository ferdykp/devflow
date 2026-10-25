# Catatan rilis — kandidat DevFlow v1.0

Tanggal: 25 Oktober 2026. Label: **kandidat untuk validasi staging**, belum production-ready.

## Perbaikan dialog dan input — 25 Oktober 2026

- Pusatkan dialog konfirmasi dengan posisi fixed, inset dan margin auto eksplisit. Batasi ukuran pada viewport dan aktifkan scroll internal untuk layar pendek.
- Fokus awal tetap pada Batal; Escape/tombol Batal mengembalikan fokus ke pemicu. Kunci scroll latar selama dialog terbuka dan pulihkan saat tutup/konfirmasi.
- Kirim form terkonfirmasi melalui requestSubmit agar validasi, submitter dan handler submit tetap berjalan. Form yang dibatalkan tidak masuk status sibuk.
- Cegah overflow teks/nama proyek panjang yang mengganggu klik di mobile. Validasi tipe dan panjang pencarian proyek sebelum digunakan pada query.
- Tambahkan regresi dialog Chromium/WebKit desktop/mobile, termasuk nama 150 karakter, scroll, resize, layar pendek dan satu request saat konfirmasi.

## Kelola proyek dan form terpisah — 25 Oktober 2026

- Pisahkan halaman Proyek baru dan Ruang kerja baru dari daftar. Pembuatan ruang kerja dilanjutkan ke form proyek dengan konteks ruang kerja terpilih.
- Tambahkan halaman edit dan akses dari kartu serta header proyek. Pengaturan tambahan tetap tersedia tanpa memenuhi tampilan awal.
- DELETE proyek kini menghapus permanen, khusus pemilik ruang kerja, dengan konfirmasi UI yang jelas. Anggota tetap dapat mengedit; arsip tetap tersedia melalui status. Penghapusan memakai transaksi dan penguncian, serta cascade database yang sudah ada; tidak perlu migrasi.
- Ringkas sidebar desktop ke 80px dan prioritaskan Tugas/Dokumen pada mobile. Verifikasi layout 320, 375, 430, 768, 1280, 1920, 2560 dan 3840px.

## Penyederhanaan antarmuka — 25 Oktober 2026

- Proyek langsung membuka daftar tugas. Input cepat cukup judul dan Enter; fokus kembali ke input setelah penambahan.
- Lingkaran selesai/buka kembali tersedia pada setiap baris. Aksi tambahan ditempatkan di menu tiga titik, dengan dukungan Escape dan klik luar.
- Beranda berfokus pada proyek dan fokus harian. Sidebar, toolbar, panel dan tombol memakai tampilan yang konsisten; navigasi mobile lebih ringkas.
- Unggahan Markdown mendapat penekanan visual dan tetap mempertahankan seluruh konversi, urutan, checklist serta batas 1.000 tugas. Fitur lanjutan tetap dapat diakses.
- Referensi arah visual: [Minimal UI, Dribbble](https://dribbble.com/shots/26182971-Task-Management-Dashboard-Minimal-UI), [Optima, Dribbble](https://dribbble.com/shots/25869018-Optima-Task-Management-Dashboard-My-Task-Page), dan [Taskify, Framer](https://www.framer.com/marketplace/templates/taskify/). Implementasi memakai komponen proyek sendiri tanpa dependensi atau pembelian template.

## To-do otomatis dari Markdown — terverifikasi 24 Oktober 2026

- Unggahan `.md` kini memiliki mode default Langsung buat to-do list; setelah Proses dokumen, hasil langsung tampil pada Daftar tanpa konfirmasi draf tambahan. Mode tinjau tetap tersedia dan klien lama tanpa parameter mode mempertahankan alur tinjau.
- Pahami bagian/tahap, judul tugas, daftar bernomor, subpoin, dan blok kode. Kelompok memakai tahapan proyek yang sudah ada; subpoin menjadi checklist tersimpan. Tujuan, latar belakang, referensi, dan YAML frontmatter tidak menjadi tugas.
- Simpan urutan sumber dan tampilkan tugas berkelompok dengan nomor langkah. Urutan tetap stabil saat status diubah dan tampilan diganti. Tampilan khusus hasil konversi dapat dikembalikan ke Semua tugas.
- Checklist diperbarui melalui POST/PATCH ber-CSRF dengan kontrol akses proyek, penguncian transaksi, dan nilai selesai eksplisit. Checklist dan status tugas terpisah.
- Proses berjalan lokal tanpa provider AI. Urutan mengikuti dokumen, bukan hasil inferensi dependensi. Batas 200 KB/1.000 tugas; sumber dan persetujuan PRD tidak diubah, dan unggahan ulang tidak menggandakan tugas.

- Perbaiki penolakan dokumen di atas 50 tugas: batas kini 1.000, tinjauan besar berisi 25 tugas per halaman dengan konfirmasi seluruh tugas. Hasil tetap berurutan dan tidak terpotong. Kesalahan berkas ditampilkan pada kolom unggahan. Tidak ada migrasi tambahan.

## Fitur tim kecil dan dokumen — 22 Oktober 2026

- Satukan kontrol form, tombol, judul, sidebar berlabel, status, notifikasi, dan navigasi proyek. Ringkasan/Tugas/Dokumen/Lanjutkan menjadi akses utama; bagian tambahan melalui Lainnya.
- Tambahkan ruang kerja bersama dengan peran Pemilik dan Anggota. Semua anggota dapat mengelola konten proyek; hanya pemilik mengundang/mengeluarkan anggota lain atau membatalkan undangan. Anggota dapat keluar sendiri.
- Undangan berupa tautan khusus satu email, token tersimpan sebagai hash, berlaku tujuh hari, bisa dibatalkan/diganti, dan perlu diterima oleh akun dengan email tersebut. Tidak ada email otomatis yang dikirim.
- Tambahkan penanggung jawab dari anggota ruang kerja, detail tugas, komentar, serta filter judul/status/prioritas/tugas saya/belum ditugaskan. Pencabutan keanggotaan mencabut akses dan membersihkan penugasan/fokus terkait.
- Tambahkan alur Dokumen → Siapkan draf → Tinjau/edit/pilih → Buat tugas. Sumber dapat berupa versi PRD tersimpan, teks yang ditempel, atau TXT/Markdown UTF-8 maksimal 200 KB. PDF/Word perlu disalin teksnya; tidak ada ekstraksi PDF/DOCX otomatis.
- Pemecahan lokal menggunakan poin daftar, subpoin sebagai kriteria selesai, judul bagian, atau paragraf. Tidak menggunakan atau mengirim data ke AI. Hasil perlu ditinjau; lebih dari 50 kandidat atau bagian terlalu panjang ditolak dengan panduan perbaikan.
- Dokumen sumber dan versi tetap tersimpan. Konversi tidak mengubah persetujuan PRD/baseline; tugas baru terkait sumbernya dan masuk status Belum dikerjakan.
- Konfirmasi impor memakai transaksi, penguncian dan constraint unik. Pengiriman ulang konfirmasi atau dokumen yang sama pada proyek yang sama tidak menggandakan tugas.

## Stabilisasi sebelumnya — 21 Oktober 2026

- Satukan password visibility Login/Register pada satu pengendali; nilai sandi, ikon, label aksesibilitas dan keyboard teruji.
- Tambahkan panel edit judul, deskripsi, prioritas, tenggat dan bobot dengan endpoint PATCH yang sudah ada.
- Perbaiki status tugas melalui dropdown yang memanggil API JavaScript salah; tambahkan regresi persistensi, konfirmasi hapus, drag/drop dan rollback UI ketika request gagal.
- Batasi pilihan repository GitHub ke izin pengguna OAuth, tambahkan pemutusan relasi repository tanpa menghapus riwayat aktivitas, dan minimalkan data exception pada log job.
- Tambahkan pembacaan isi versi PRD, pembuatan revisi dan konfirmasi persetujuan lewat backend yang sudah ada.
- Perbaiki font input/select, tombol tutup sidebar mobile, fokus menu tersembunyi dan label bahasa Indonesia pada halaman yang diaudit.
- Pisahkan cache/session/database E2E. Limiter login memakai key akun+IP dan batas IP agregat; register dibatasi secara terpisah. CSRF/rate limiting tetap diuji nyata.
- Tambahkan header keamanan minimum, default secure cookie production dan pemeriksaan cookie/cipher/locale pada preflight.
- Perbarui locale template `.env.example`; `.env` existing tidak diubah. PWA fallback diterjemahkan dan cache version diperbarui; cache autentikasi/API tetap dilarang.
- Hapus pengambilan font build yang tidak digunakan layout. Dependency Playwright tetap berasal dari lockfile; setup E2E tidak lagi memasang versi package secara ad hoc.
- Tambahkan CI quality, contoh Nginx/systemd/cron, backup/restore drill dan dokumentasi release gate.

## Kompatibilitas dan database

Migrasi tambahan `2026_10_22_043945_add_small_team_collaboration` menambahkan `user_workspace`, `workspace_invitations`, `document_task_imports`, `task_comments`, serta kolom nullable `assignee_id`, `document_task_import_id`, dan `source_index` pada tugas. Data lama tidak dihapus atau diubah isinya. Jalankan `php artisan migrate --no-interaction` pada deployment setelah backup sesuai prosedur; migrasi sudah diterapkan pada database lokal. Route lama dipertahankan; route kolaborasi dan impor ditambahkan.

Migrasi `2026_10_22_114301_add_order_and_checklist_to_tasks_table` menambahkan kolom nullable `sort_order` dan `checklist` pada tugas. Migrasi ini sudah terdeteksi berstatus Ran pada database lokal.

`down()` menghapus tabel/kolom baru beserta datanya. Setelah fitur digunakan, utamakan perbaikan maju dan backup; jangan menjalankan rollback database secara otomatis. Persetujuan PRD tetap tidak mengubah baseline scope. Pemutusan repository menghapus relasi sinkronisasi lokal saja; tidak menghapus repository di GitHub atau riwayat aktivitas.

Laravel Boost dipasang sesuai instruksi bootstrap repositori pada pekerjaan sebelumnya; composer manifest/lockfile dan konfigurasi agent mencerminkan setup tersebut. Tidak ada framework frontend baru. Perubahan awal pengguna pada demo seeder dipertahankan dan bukan bagian perbaikan ini.

## Batas yang masih berlaku

Papan tugas menggunakan pagination, PWA tidak menyimpan data proyek offline, GitHub/AI nyata belum diverifikasi, baseline screenshot belum disetujui, dan deployment/recovery server belum diuji. Lihat `GO-NO-GO.md` sebelum memberikan akses publik.
