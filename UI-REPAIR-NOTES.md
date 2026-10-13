# Pembenahan UI DevFlow — iterasi 1

Fokus perbaikan: (1) tab proyek dan konten Resume yang bertabrakan, (2) dialog pencarian cepat yang itemnya menjadi satu baris, (3) konflik kelas grid dan block pada kontainer proyek.

Navigasi proyek dan dialog perintah ditulis ulang menggunakan utility Tailwind v4 langsung di Blade. Struktur controller, route, dan data tidak diubah. CSS lama tidak dipulihkan. Bagian lain dipertahankan dari versi sebelumnya agar tidak memicu regresi tanpa pemeriksaan visual.

Yang sudah diperiksa: sintaks PHP dan JavaScript, integritas ZIP. Yang belum: build Vite, PHPUnit, Playwright dan inspeksi browser.

Jalankan: npm ci && npm run build && php artisan test && npm run test:e2e. Setelah itu periksa seluruh halaman di desktop dan mobile.
