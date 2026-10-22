# Panduan deployment staging dan production

Status: template siap ditinjau; deployment server **BLOCKED**, karena akses belum tersedia. Tidak ada deployment publik yang dijalankan.

## Persiapan

Gunakan host Ubuntu yang mendukung PHP 8.5/FPM, Composer, MySQL, Nginx, TLS dan systemd. Verifikasi versi package dari repositori OS yang dipilih, jangan memasang paket acak berdasarkan panduan ini. Sediakan staging terpisah dari production, termasuk database, APP_KEY, domain, cookie, GitHub App dan provider AI. Akun aplikasi hanya memerlukan izin operasi data; gunakan kredensial migrasi terpisah jika diperlukan.

Bangun artifact di CI/build host:

```sh
composer install --prefer-dist --no-interaction
npm ci
npx playwright install chromium
npm run build
php artisan test
npm run test:e2e
composer audit
npm audit --omit=dev
```

CI quality melakukan pengujian SQLite dan MySQL 8.4. Workflow ini belum dijalankan oleh GitHub Actions dalam sesi lokal. Build artifact runtime juga perlu `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`. Jangan membawa `.env`, cache config mesin build, database tes, screenshot, log, `node_modules`, atau secret ke artifact publik. Sertakan `public/build` yang baru dibangun.

## Environment di server

Buat `.env` di shared storage privat, bukan di `public`; isi dari secret manager. Contoh tanpa secret:

```dotenv
APP_NAME=DevFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devflow.example.com
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devflow
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=90
LOG_CHANNEL=daily
LOG_LEVEL=warning
DEVFLOW_AI_ENABLED=false
```

APP_KEY dan kredensial database sengaja tidak disertakan. Buat APP_KEY saat instalasi pertama; jangan menggantinya pada setiap deploy. Periksa nama environment `retry_after` terhadap `config/queue.php` source aktual. Queue timeout worker 60 detik berada di bawah retry_after 90 detik; job GitHub sendiri timeout 45 detik.

## Server dan rilis

1. Salin template `scripts/deployment/nginx.conf.example`, `queue.service.example`, `scheduler.cron.example`; sesuaikan path, domain, sertifikat, user dan socket FPM. Template belum lolos `nginx -t` pada server nyata.
2. Struktur yang disarankan: `/srv/devflow/releases/<release-id>`, `/srv/devflow/shared`, symlink `current`. Shared `.env` dan `storage` hanya dapat dibaca/ditulis user yang membutuhkan; `bootstrap/cache` harus writable. Hindari permission 777.
3. Backup database dan data upload bila ada, verifikasi checksum, lalu restore drill pada database terpisah. Tinjau semua migration terhadap artifact sebelumnya. Rilis ini tidak menambah migration.
4. Pasang artifact ke direktori rilis baru. Tautkan shared `.env`/storage sebelum menjalankan Artisan. Tinjau apakah perubahan memerlukan maintenance; jika ya, `php artisan down` pada rilis aktif.
5. Jalankan `php artisan migrate --force --no-interaction` setelah persetujuan migration berisiko, lalu `php artisan config:cache`, `route:cache`, `view:cache` pada rilis baru. Jangan memakai `migrate:fresh`, `db:wipe` atau demo seeder.
6. Jalankan `php artisan devflow:preflight --strict`. Harus lulus pada konfigurasi staging yang menyerupai production. Preflight tidak membuktikan worker, TLS, backup atau integrasi.
7. Pindahkan symlink `current` secara atomik; reload FPM dan restart service worker agar cwd/opcache memakai rilis baru. `queue:restart` dapat digunakan untuk penghentian teratur sesuai supervisor.
8. Jalankan `php artisan up` bila maintenance aktif. Uji `/up`, login/logout, dua akun terpisah, proyek, task list/board, PRD, fokus, dan queue. Verifikasi header/cookie dari URL HTTPS nyata, bukan hanya nilai environment.
9. Pantau error rate, latensi, queue backlog dan log sebelum mengizinkan pengguna umum. Deployment production publik memerlukan persetujuan pemilik.

## Rollback

Pertahankan artifact sebelumnya dan catat hash lockfile. Jika schema kompatibel, hentikan trafik sementara, kembalikan symlink ke artifact sebelumnya, bangun ulang config cache dengan environment server, reload FPM, restart worker, smoke test, lalu buka trafik. Jangan membalik migration secara otomatis. Untuk schema tidak kompatibel, pilih forward fix atau restore yang sudah disimulasikan dan disetujui; restore dapat menghilangkan transaksi setelah backup.

Rollback symlink/FPM/TLS di staging belum diuji. Statusnya **BLOCKED**, bukan PASS. Lihat [operasi](OPERATIONS-GUIDE.md) dan [release gate](GO-NO-GO.md).
