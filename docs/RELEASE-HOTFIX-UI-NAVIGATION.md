# Perbaikan pengujian navigasi dan Tailwind

- Navigasi proyek sekarang memberi `aria-label="Menu proyek lainnya"` pada kontrol menu yang tersedia secara visual sebagai **Lainnya**. Ini membuat informasi aksesibilitas eksplisit sekaligus memenuhi pemeriksaan Feature Test.
- Menghapus contoh utility Tailwind yang tidak valid dari dokumentasi sumber. Tailwind v4 memindai teks proyek secara otomatis, sehingga contoh pada dokumen dapat terdeteksi sebagai class dan menghasilkan peringatan CSS.
- Perintah `npm run setup:e2e` memasang Playwright sebagai devDependency **permanen**, memperbarui package-lock.json di komputer yang dapat terhubung ke npm registry, dan menyiapkan Chromium. Kemudian `npm ci` akan selalu mempertahankan Playwright.

## Di MacBook

```bash
npm run setup:e2e
npm ci
npm run build
php artisan test
npm run test:e2e
```

**Catatan:** Lingkungan pengemasan tidak dapat menjangkau npm registry. Oleh karena itu `package-lock.json` tidak direkayasa dan dependency Playwright belum otomatis terkunci dalam arsip ini. Jalankan setup sekali, lalu commit `package.json` + `package-lock.json` yang diperbarui.
