# DevFlow — Antarmuka Bahasa Indonesia

Seluruh menu inti, judul, formulir, pesan sukses, navigasi, halaman autentikasi, serta status dan prioritas tugas ditampilkan dalam bahasa Indonesia. Kode status di database, nama route, field API, dan integrasi GitHub **tidak berubah**.

## Sesudah pembaruan

1. Di file `.env` aktif, pastikan `APP_LOCALE=id` dan `APP_FALLBACK_LOCALE=id`. Variabel `.env` yang sudah ada dapat menimpa default `config/app.php`.
2. Jalankan `php artisan optimize:clear`, `php artisan test`, `npm run build`, dan `npm run test:e2e`.
3. Periksa halaman di browser, termasuk toast, modal dan tampilan ponsel.

Catatan: judul proyek/tugas dan data demo yang sudah tersimpan tidak diterjemahkan otomatis karena merupakan data pengguna. Istilah teknis GitHub, PRD, dan AI tetap digunakan.
