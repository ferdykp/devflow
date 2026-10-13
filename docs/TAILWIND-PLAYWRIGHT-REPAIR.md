# Perbaikan Tailwind v4 dan Playwright

## Tailwind
Selector turunan seperti selector descendant yang tidak memakai pemisah spasi menghasilkan selector CSS yang tidak valid (`:is(.class)a`). Ubah menjadi variant descendant Tailwind yang valid untuk menambahkan spasi keturunan. Template Blade dalam paket ini sudah diperbarui. Tidak perlu perubahan route atau database.

## Playwright
Paket Playwright sebelumnya dipasang dengan `--no-save`, sehingga `npm ci` menghapusnya. Jalankan **sekali** di komputer yang punya akses ke npm registry:

```bash
npm install -D @playwright/test@1.56.1
npx playwright install chromium
```

Perintah tersebut memperbarui `package.json` **dan** `package-lock.json` secara konsisten. Commit kedua file agar `npm ci` dan CI bisa memasangnya di masa depan. Jangan lagi menggunakan `--no-save` untuk dependency test.

## Verifikasi

```bash
npm ci
npm run build
php artisan test
npm run test:e2e
```

Jika `npm run build` masih menampilkan peringatan CSS, simpan pesan terbaru untuk ditelusuri. Peringatan fontaine bersifat terpisah dan opsional.

Tidak ada migrasi database baru.
