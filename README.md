# DevFlow

**Kerja fokus. Tumbuh konsisten.** DevFlow adalah workspace untuk developer yang menyatukan pengelolaan proyek, pekerjaan harian, dan pengembangan diri dalam satu aplikasi.

## Tampilan

| Dashboard | Proyek |
| --- | --- |
| ![Dashboard DevFlow](docs/screenshots/dashboard.png) | ![Daftar proyek DevFlow](docs/screenshots/projects.png) |

## Fitur

- **Dashboard & rencana harian** — ringkasan tugas, prioritas, agenda, rutinitas, dan pekerjaan mendatang.
- **Proyek dan kolaborasi** — kelola beberapa workspace, proyek, anggota, serta undangan tim.
- **Manajemen tugas** — status, prioritas, tenggat, checklist, komentar, milestone, dan impor tugas dari dokumen.
- **Perencanaan proyek** — catatan, backlog ide, dokumen PRD dengan versi dan persetujuan, serta tinjauan perubahan scope.
- **Fokus & kalender** — pilih prioritas harian dan atur agenda serta sesi fokus.
- **Tujuan & kebiasaan** — catat target, milestone, rutinitas, sesi belajar, dan progresnya.
- **Smart Coach & review mingguan** — tinjau rekomendasi aktivitas dan refleksi pekerjaan.
- **Integrasi GitHub opsional** — hubungkan repositori dan sinkronkan aktivitas melalui GitHub App/webhook.
- **Akun & akses** — registrasi, login Google opsional, verifikasi email, dan pengaturan profil.
- **PWA** — aplikasi menyediakan web manifest dan halaman fallback saat offline.

## Teknologi

- PHP 8.3+ dan Laravel 13
- Blade dan Tailwind CSS 4
- Vite
- Database SQLite secara default (dapat dikonfigurasi melalui Laravel)

## Menjalankan secara lokal

Prasyarat: PHP 8.3+, Composer, Node.js, dan npm.

```bash
git clone https://github.com/ferdykp/devflow.git
cd devflow
touch database/database.sqlite
composer setup
```

`composer setup` menginstal dependensi, menyiapkan `.env`, membuat application key, menjalankan migrasi, lalu membangun aset frontend.

Jalankan aplikasi dan Vite di dua terminal:

```bash
php artisan serve
```

```bash
npm run dev
```

Buka `http://localhost:8000`. Untuk membuat data contoh lokal:

```bash
php artisan db:seed --class=DevFlowDemoSeeder
```

Seeder demo menyiapkan akun `demo@devflow.local` dengan kata sandi `Devdemo123.`. Gunakan hanya pada lingkungan lokal.

## Integrasi opsional

Login Google, email SMTP, dan GitHub App memerlukan kredensial masing-masing di `.env`. Aplikasi tetap dapat digunakan tanpa mengaktifkan integrasi eksternal. Panduan koneksi GitHub App tersedia di [`docs/GITHUB-APP-SETUP.md`](docs/GITHUB-APP-SETUP.md).

## Lisensi

Lihat [`composer.json`](composer.json) untuk informasi lisensi proyek.
