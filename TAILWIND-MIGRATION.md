# Migrasi DevFlow ke Tailwind CSS v4

- Seluruh tampilan komponen di `resources/views/` memakai utility Tailwind CSS v4 secara langsung, termasuk arbitrary properties dan variants untuk menjaga tampilan yang sudah ada.
- `resources/css/app.css` hanya menyimpan import dan sumber Tailwind, token tema, reset global, aturan aksesibilitas, serta keyframes animasi.
- Nama kelas `df-*` yang masih ada pada markup dipertahankan sebagai penanda semantik dan kompatibilitas JavaScript (termasuk status drag-and-drop), **bukan** sebagai selector stylesheet komponen.
- Struktur database, route, controller dan proses bisnis tidak diubah.
- Sesudah memasang, jalankan `npm ci`, `npm run build`, `php artisan test`, dan `npm run test:e2e`.
- Build dan browser tests belum bisa dijalankan dalam lingkungan pengemasan karena dependensi npm tidak tersedia di cache offline. Tinjau ulang visual pada mobile dan desktop sebelum deployment.
- Migrasi mempertahankan nilai desain sebelumnya memakai Tailwind arbitrary properties. Penyederhanaan lebih lanjut ke kelas standar (mis. `p-4`, `rounded-xl`, dan `text-sm`) dapat dilakukan secara bertahap, setelah regresi visual disetujui.
