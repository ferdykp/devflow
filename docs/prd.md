# DevFlow — Dokumen Kebutuhan Produk

**Versi:** 2.1 (audit arsitektur; rancangan produk masih ditinjau)\
**Tanggal:** 18 November 2026\
**Status:** Tahap 0 terbatas diterapkan; arsitektur keseluruhan dan Tahap 1–3 belum final/disetujui\
**Produk:** Workspace pengelolaan kerja untuk developer individu dan tim kecil\
**Stack saat audit:** Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4, Vite

> Dokumen ini menggantikan draf berbahasa Inggris 1.0 sebagai gambaran produk terkini. Bagian “sudah tersedia” menjelaskan perilaku yang ditemukan di codebase, bukan jaminan bahwa semua kebutuhan telah terpenuhi. Evaluasi arsitektur yang lebih lengkap ada di [Evaluasi Arsitektur Produk & UX](product-architecture-review.md). Pengguna hanya menyetujui Tahap 0 terbatas; rekomendasi lain tetap usulan.

## 1. Ringkasan produk

DevFlow membantu developer mengetahui pekerjaan yang perlu dilakukan, menyimpan konteks proyek, mengubah dokumen kebutuhan menjadi rencana tugas, dan melanjutkan pekerjaan setelah berpindah konteks. Pengguna dapat bekerja sendiri atau berkolaborasi dalam ruang kerja kecil.

DevFlow harus tetap berguna tanpa PRD, GitHub, AI, milestone, atau rekan tim. Setiap integrasi bersifat tambahan dan tidak boleh mengarang progres atau mengubah keputusan penting tanpa persetujuan pengguna.

### Masalah pengguna

- Konteks kerja tersebar di tugas, dokumen, kalender, GitHub, catatan, dan alat belajar.
- Pengguna sulit mengetahui langkah berikutnya dan melanjutkan pekerjaan setelah jeda.
- Dokumen kebutuhan perlu dipecah menjadi pekerjaan yang dapat dijalankan tanpa kehilangan struktur sumbernya.
- Menu yang banyak dan konteks personal/tim yang kurang tegas membuat pengguna ragu harus mulai dari mana.

### Pernyataan produk

**DevFlow mengubah tujuan dan konteks kerja developer menjadi langkah berikutnya yang jelas, dapat ditinjau, dan mudah dilanjutkan.**

## 2. Pengguna dan pekerjaan utama

### Pengguna utama

1. **Developer individu** yang mengelola beberapa proyek dan perlu berpindah konteks.
2. **Pemilik tim kecil** yang membagi tugas dan mengundang anggota ke ruang kerja.
3. **Anggota tim** yang menerima tugas, memperbarui status, berdiskusi, dan melihat konteks proyek.

### Pekerjaan yang perlu diselesaikan

- Menentukan prioritas dan mulai bekerja hari ini.
- Menambahkan, memfilter, mengurutkan, dan menuntaskan tugas.
- Membuat proyek sederhana, lalu menambahkan dokumen, tugas, anggota, atau GitHub saat dibutuhkan.
- Mengimpor PRD, memeriksa hasil pemecahan, lalu membuat tugas tanpa kehilangan sumber dan urutannya.
- Melanjutkan pekerjaan dengan ringkasan konteks yang menunjukkan data dan aktivitas terakhir.
- Meninjau kemajuan mingguan serta menjaga tujuan, rutinitas, dan pembelajaran.
- Mengundang anggota dan memahami siapa yang dapat mengelola ruang kerja.

## 3. Prinsip produk

1. **Mulai dari pekerjaan pengguna.** Beranda mengarahkan ke tindakan berikutnya, bukan sekadar menampilkan metrik.
2. **Konteks terlihat.** Pengguna harus tahu apakah mereka sedang melihat data pribadi, ruang kerja, atau proyek tertentu.
3. **Opsional secara bertahap.** Proyek tetap dapat digunakan tanpa PRD, GitHub, AI, atau milestone.
4. **Tinjau sebelum dampak besar.** Impor, persetujuan PRD, perubahan anggota, dan penghapusan harus menjelaskan akibatnya.
5. **Data sumber tetap terlacak.** Tugas yang dihasilkan dari dokumen harus dapat ditelusuri ke dokumen dan bagian asalnya.
6. **Status sistem jujur.** Tampilkan keadaan tersimpan, sinkronisasi, kegagalan, serta batas fitur yang belum tersedia.
7. **Responsive untuk semua ukuran.** Navigasi dan konten harus tetap dapat dipahami dari layar kecil hingga ultrawide; lebar konten tidak perlu membesar tanpa batas.

## 4. Audit produk saat ini

### Sudah tersedia di codebase

- Autentikasi, onboarding, pengaturan akun, dan pilihan jadwal kerja.
- Ikhtisar dashboard dan halaman Rencana hari ini.
- Tugas personal dan tugas proyek, tampilan daftar/papan, filter, tampilan tersimpan, komentar, checklist, dan status.
- Proyek, bagian ringkasan, tugas, PRD/dokumen, resume, aktivitas, catatan, backlog, milestone, cakupan, GitHub, dan pengaturan.
- Impor teks/Markdown dari tempel atau unggah TXT/MD; parser menyusun judul bagian menjadi kelompok, butir menjadi tugas, dan subbutir menjadi checklist. Mode otomatis dan tinjau tersedia.
- Kalender, sesi fokus, tujuan, rutinitas, belajar, catatan pribadi, review mingguan, serta Smart Coach.
- Ruang kerja dengan pemilik dan anggota; pemilik membuat undangan, anggota menerima undangan, dan pemilik dapat mengelola anggota.
- Pengaturan integrasi GitHub/AI dan preferensi pengingat.

### Temuan dan masalah produk

| Prioritas | Temuan | Dampak pengguna |
|---|---|---|
| P0 | Label konteks ruang kerja ditentukan dari jenis route. Semua route proyek dibaca sebagai konteks tim, termasuk proyek pada ruang kerja bernama Personal. | Pengguna dapat salah memahami ruang atau kepemilikan yang sedang aktif. |
| P0 | Pengalih ruang kerja hanya menautkan ke dashboard pribadi dan indeks Proyek & tim; tidak memilih ruang kerja tertentu dan tidak menyimpan pilihan ruang aktif. | Label “Pilih ruang kerja” menjanjikan kemampuan yang tidak diberikan oleh menu. |
| P1 | Ada 12 menu primer; Ikhtisar dan Rencana hari ini berdekatan tetapi tujuan keduanya belum dijelaskan pada navigasi. | Sulit memilih titik mulai dan mengenali prioritas. |
| P1 | Tugas personal dan tugas proyek tampil pada inbox tugas yang sama. | Cakupan tugas dapat kurang jelas ketika berpindah antara pribadi dan tim. |
| P1 | Bagian proyek memiliki banyak area; Catatan proyek dan Catatan pribadi memakai nama yang sama. Resume juga muncul sebagai tab dan tombol aksi. | Navigasi proyek terasa padat dan beberapa aksi tampak berulang. |
| P1 | Akses impor PRD muncul setelah pengguna memiliki proyek. Impor mendukung teks/Markdown, bukan PDF/Word. | Pengguna baru tanpa proyek bisa tidak menemukan alur impor dan dapat salah mengira format dokumen didukung. |
| P1 | Pusat notifikasi menampilkan empty state tetap. Preferensi pengingat tersimpan, tetapi tidak ditemukan scheduler atau pengiriman notifikasi pengingat pada route/job/scheduler aplikasi. | Pengguna dapat mengira pengingat aktif padahal pengiriman belum tersedia. |
| P1 | Peran tim hanya pemilik dan anggota; kemampuan pengelolaan berbeda, tetapi belum ada tingkat peran kustom. | Tim perlu mengetahui batas akses sebelum mengundang atau mengelola anggota. |
| P2 | Batas upload, encoding, ukuran, jumlah tugas, mode otomatis/tinjau dan format teks sudah dijelaskan, tetapi sumber dokumen belum ditampilkan sebagai relasi kebutuhan yang dapat ditelusuri dari setiap tugas. | Pengguna masih perlu memeriksa dokumen sumber secara manual untuk memastikan alasan sebuah tugas dibuat. |
| P2 | E2E memotret halaman pada desktop 320–3840 px, tetapi audit viewport itu dilewati pada proyek mobile; pengujian saat ini tidak membuktikan seluruh halaman bebas masalah visual pada semua breakpoint. | Cakupan responsive lintas halaman dan perangkat masih perlu diperkuat. |

Temuan P0/P1 di atas didukung oleh route, controller, Blade view, scheduler, dan pengujian yang ada. Perlu wawancara atau uji tugas dengan pengguna untuk mengukur frekuensi dan tingkat dampak sebenarnya.

## 5. Arsitektur informasi dan keputusan yang ditinjau

Arsitektur penuh **belum ditetapkan**. Audit terbaru memisahkan kerja harian, delivery proyek/tim, dan pengembangan personal; hubungan antardomain harus terlihat saat berguna tanpa menggabungkan semua halaman menjadi satu workspace generik. Sitemap, feature relationship matrix, keputusan keep/change/merge, dan pola layar ada di [dokumen evaluasi arsitektur](product-architecture-review.md).

Beberapa keputusan yang menjadi arah rancangan, bukan persetujuan implementasi:

- Ikhtisar dan Rencana hari ini tetap dibedakan sementara karena menjawab kebutuhan berbeda: melihat keadaan vs menjalankan pekerjaan.
- List, board, dan kalender adalah cara melihat record yang sama bila berbicara tentang tugas; event kalender tidak boleh menduplikasi tugas.
- Catatan proyek dan pribadi tetap terpisah berdasarkan akses dan konteks, tetapi labelnya harus membedakan scope.
- Resume dapat menjadi aksi dari ringkasan; penghapusan tab duplikat masih memerlukan validasi sebelum Tahap berikutnya.
- Impor tetap satu alur di Dokumen & PRD; tugas hasil impor perlu membuka asal dokumen.
- Workspace switcher hanya boleh dipakai jika benar-benar memilih workspace. Kontrol yang ada saat ini adalah pengalih area, bukan pemilih workspace aktif.

## 6. Alur pengguna end-to-end

### A. Pengguna baru dan kerja harian

```text
Daftar / masuk → onboarding singkat → Ikhtisar
→ pilih tindakan: Rencana hari ini | Tugas | Buat proyek | Tujuan
→ kerjakan → tandai status / catat hasil → review mingguan
```

Ikhtisar merangkum keadaan dan menunjuk langkah berikutnya. Rencana hari ini menjadi ruang eksekusi: pilih prioritas, jadwalkan bila perlu, jalankan fokus, refleksikan hasil.

### B. Proyek baru tanpa PRD

```text
Proyek & tim → Buat proyek → nama + tujuan opsional
→ Ringkasan proyek → tambah tugas atau catatan
→ kerjakan dan perbarui status → Lanjutkan untuk memulihkan konteks
```

PRD dan GitHub tidak wajib. Empty state memberikan CTA pertama yang sesuai, misalnya Tambah tugas, Tulis dokumen, atau Undang anggota.

### C. PRD menjadi tugas

```text
Proyek baru/lama → Dokumen & PRD → tempel teks atau unggah TXT/Markdown
→ lihat ringkasan hasil parsing (kelompok, tugas, checklist, yang dilewati, peringatan)
→ tinjau dan edit draf → pilih tugas/penanggung jawab/tanggal bila perlu
→ konfirmasi pembuatan → buka daftar tugas hasil impor
→ setiap tugas menyediakan tautan kembali ke bagian dokumen sumber
```

Ketentuan rancangan:

- Pertahankan dukungan TXT/Markdown dan tempel teks sebagai baseline awal.
- Pilihan **Tinjau dahulu** menjadi pilihan aman untuk dokumen baru; **Buat langsung** tetap tersedia dengan jumlah hasil dan konfirmasi yang jelas.
- Persetujuan PRD dan pembuatan tugas adalah aksi berbeda. Persetujuan membuat versi acuan; tugas yang dihasilkan dapat berasal dari dokumen draft atau versi yang disetujui, dengan status sumber yang jelas.
- Tampilkan pesan jika tidak ada tugas terdeteksi, sumber duplikat, struktur yang tidak dikenali, batas ukuran/encoding terlampaui, atau proses gagal. Beri jalan kembali tanpa kehilangan teks.
- PDF/Word belum menjadi dukungan saat ini. Tambahkan hanya setelah kebutuhan pengguna dan ekstraksi dokumen dinilai; jika dibuat, preview harus memperlihatkan kualitas hasil ekstraksi sebelum commit.

### D. Bekerja dalam tim

```text
Proyek & tim → pilih/buat ruang kerja → buat proyek
→ undang email → bagikan tautan undangan → anggota masuk dengan email yang cocok
→ anggota menerima → tugas dapat ditugaskan/didiskusikan
→ pemilik mengelola anggota atau mencabut undangan
```

Jelaskan pemilik dan anggota sebelum pengiriman undangan. Saat akses dicabut, tampilkan dampak nyata: tugas yang ditugaskan kepada anggota dilepas, dan akses proyek berhenti.

### E. Menyelesaikan tugas dan memulihkan konteks

```text
Tugas / Ringkasan proyek → buka detail → checklist, komentar, status
→ pilih Lanjutkan → tampilkan tugas aktif, catatan dan aktivitas terakhir
→ mulai Sesi fokus → selesaikan / simpan perubahan → kembali ke tugas
```

Informasi resume harus menyebutkan sumber dan waktu data terakhir. GitHub menjadi bukti aktivitas, bukan bukti tugas selesai secara otomatis.

### F. Pengembangan personal dan tinjauan

```text
Tujuan → hubungkan rutinitas/belajar/tugas bila relevan
→ catat progres → Ikhtisar memperlihatkan ringkasan
→ Review mingguan meninjau hasil dan memindahkan pekerjaan
→ Smart Coach memberi saran → pengguna konfirmasi atau abaikan
```

Saran tidak mengubah data secara diam-diam. Tujuan, rutinitas, dan pembelajaran tidak dipaksakan menjadi bagian proyek.

### G. Kalender, fokus, catatan, dan pengaturan

- **Kalender:** lihat agenda dan tenggat dalam hari/minggu/bulan; buat/edit/jadwal ulang; perubahan berulang harus menjelaskan apakah hanya kejadian ini atau seluruh rangkaian.
- **Sesi fokus:** pilih tugas prioritas, mulai/hentikan sesi, simpan durasi, pulihkan status setelah refresh.
- **Catatan pribadi/proyek:** buat dan edit; autosave memberi status Menyimpan/Tersimpan/Gagal, dengan retry atau tombol simpan cadangan.
- **Notifikasi:** hanya menampilkan badge dan pesan pengingat ketika event benar-benar dikirim; pengaturan kanal/waktu harus menyatakan apakah pengiriman tersedia atau belum dikonfigurasi.
- **Integrasi:** tunjukkan keadaan tersambung/tidak tersambung, izin, data yang dibaca, sinkronisasi terakhir, dan tindakan perbaikan.

## 7. Persyaratan fungsional dan prioritas

| ID | Prioritas | Persyaratan | Kriteria penerimaan utama |
|---|---|---|---|
| PR-01 | P0 | Konteks ruang kerja yang benar | Header, sidebar, breadcrumb, dan daftar proyek menunjukkan ruang aktual; tidak menyimpulkan “tim” dari route saja. |
| PR-02 | P0 | Navigasi berbasis pekerjaan | Pengguna memahami beda Ikhtisar dan Rencana hari ini; semua menu punya label, posisi aktif, dan tujuan yang konsisten di desktop/mobile. |
| PR-03 | P0 | Cakupan tugas jelas | Pengguna dapat melihat dan mengubah cakupan tugas pribadi/proyek; filter tersimpan pada URL dan tidak menghilangkan tugas lintas proyek tanpa penjelasan. |
| PR-04 | P0 | Pembuatan proyek progresif | Proyek dapat dibuat dengan nama saja; langkah lanjutan menawarkan PRD, tugas, tim, dan GitHub sebagai opsi. |
| PR-05 | P0 | Impor dokumen yang aman | Tampilkan preview jumlah/struktur tugas; tinjau/edit/pilih sebelum commit; kegagalan dapat dipulihkan tanpa mengetik ulang. |
| PR-06 | P0 | Sumber tugas terlacak | Tugas hasil impor menunjukkan dokumen dan bagian asal; relasi tetap benar setelah judul dokumen direvisi. |
| PR-07 | P0 | Siklus tugas | CRUD, status, checklist, komentar, filter, dan perubahan papan/list memberi feedback sukses/gagal yang dapat dipulihkan. |
| PR-08 | P0 | Alur resume | Ringkasan dapat dibangun dari data yang tersedia, menyatakan data kosong/kedaluwarsa, dan memiliki tautan ke sumber konteks. |
| PR-09 | P0 | Akses ruang kerja | Pemilik/anggota memiliki izin yang dapat dijelaskan; undangan kedaluwarsa, salah email, batal, dan pencabutan memberi hasil yang jelas. |
| PR-10 | P1 | Notifikasi nyata | Feed berisi notifikasi persisten, status dibaca, waktu/konteks dan tujuan; pengiriman terjadwal sesuai preferensi zona waktu atau UI menyatakan layanan belum aktif. |
| PR-11 | P1 | Kalender dan fokus | Perubahan jadwal, kejadian berulang, timer, dan sesi tersimpan dengan konfirmasi/undo serta penanganan konflik. |
| PR-12 | P1 | Catatan autosave dapat dipercaya | Status penyimpanan terlihat; konflik, offline, dan kegagalan menahan teks serta memberi retry. |
| PR-13 | P1 | Tujuan dan pertumbuhan tetap opsional | Tujuan, rutinitas, belajar, review, dan saran tidak menghambat alur kerja proyek; saran membutuhkan konfirmasi. |
| PR-14 | P1 | Sistem peran sederhana | Label pemilik/anggota serta aksi yang tersedia jelas; peran kustom di luar cakupan kecuali divalidasi kebutuhan. |
| PR-15 | P1 | Layout lintas viewport | Halaman dan interaksi utama diuji pada 320/375/430/768/1024/1280/1920/2560/3840 px; tidak ada overflow horizontal tak disengaja dan konten tetap terbaca. |
| PR-16 | P2 | Impor PDF/Word | Hanya dikerjakan bila riset menunjukkan kebutuhan; ekstraksi dapat ditinjau, berkas divalidasi aman, dan gagal tanpa kehilangan sumber. |

## 8. Aksi utama dan pola interaksi

- Satu aksi primer per halaman; aksi sekunder tidak bersaing secara visual.
- Navigasi menggunakan tautan; perubahan data menggunakan tombol/form yang berlabel tindakan.
- Filter, pencarian, tampilan board/list, dan cakupan data tercermin di URL agar dapat dibagikan atau dipulihkan.
- Dialog hanya untuk keputusan singkat/destruktif; form kompleks memakai halaman atau drawer yang dapat dipulihkan.
- Tabel/papan menyediakan empty state yang mengarahkan ke langkah pertama, status loading, hasil kosong karena filter, error, dan retry.
- Drag-and-drop menyediakan alternatif keyboard/menu status; kegagalan menyimpan mengembalikan item dan memberi alasan.
- Kontrol mempunyai label aksesibel, fokus keyboard yang terlihat, target sentuh yang cukup, dan hormat pada reduced motion.

## 9. Non-goals

- Mengubah DevFlow menjadi pengganti penuh Jira/Linear, IDE, atau GitHub.
- Memaksa semua data personal, pertumbuhan, dan proyek menjadi satu workspace universal.
- Mengharuskan PRD, AI, atau integrasi eksternal untuk membuat/mengerjakan proyek.
- Menyimpulkan progres selesai hanya dari commit atau output AI.
- Menambahkan role permission kustom, dukungan PDF/Word, aplikasi native, atau billing dalam tahap awal tanpa bukti kebutuhan.
- Mengubah palet/tema sebagai solusi atas masalah yang sebenarnya berasal dari struktur, konteks, atau alur.

## 10. Prioritas implementasi yang diusulkan

### Tahap 0 — Kualitas baseline dan konteks (P0; disetujui terbatas)

1. Hilangkan label “Ruang kerja tim” yang salah pada daftar agregat proyek.
2. Jelaskan kontrol sidebar sebagai “Pindah area” selama kontrol hanya berpindah ke Personal atau Proyek & tim.
3. Gunakan label domain “Proyek” pada konteks proyek; nama workspace aktual tetap berasal dari data proyek/breadcrumb.
4. Investigasi kegagalan PHP/E2E; perbaiki kode produk hanya bila ada bug yang direproduksi. Sesuaikan tes yang mengharapkan perilaku lama/tidak terlihat pengguna.
5. Tidak mengubah skema data, flow PRD, scope inbox tugas, susunan seluruh menu, atau gaya visual pada tahap ini.

### Tahap 1 — Alur proyek dan PRD (P0)

1. Tambahkan pintu masuk impor yang jelas dari alur proyek baru dan halaman Dokumen & PRD.
2. Perjelas preview, peringatan parsing, tinjau sebelum commit, dan pemulihan error.
3. Pertahankan relasi tugas ke import dan tambahkan navigasi ke dokumen/bagian sumber yang stabil.
4. Validasi alur proyek → dokumen → tugas → fokus → resume dengan pengguna.

### Tahap 2 — Fitur yang menjanjikan fungsi belum nyata (P1)

1. Implementasikan notifikasi persist, delivery terjadwal, read/unread, zona waktu, dan pengaturan kanal end-to-end; atau sembunyikan klaim pengiriman sampai layanan tersedia.
2. Perjelas permissions pemilik/anggota dan keadaan integrasi.
3. Tambahkan status autosave dan recovery yang eksplisit pada catatan.

### Tahap 3 — Validasi dan pengembangan berdasar bukti (P1/P2)

1. Jalankan uji tugas dan responsif lintas semua halaman, perangkat dan breakpoint.
2. Ukur penyelesaian alur PRD/task, waktu menemukan tindakan, kegagalan form, dan pemulihan error.
3. Pertimbangkan PDF/Word atau role kustom setelah wawancara/uji kebutuhan.

## 11. Konsekuensi teknis

- **Navigasi dan konteks:** kemungkinan perubahan Blade components/layout serta cara controller mengirim workspace aktif. Pilihan workspace aktual dapat memerlukan query parameter atau state sesi; URL sebaiknya tetap dapat memulihkan konteks.
- **Inbox tugas:** filter scope personal/proyek perlu tervalidasi, masuk ke URL, dan menjaga akses `ProjectAccess`.
- **PRD import:** UI preview mayoritas dapat memakai struktur draft yang ada; relasi source anchor yang stabil mungkin memerlukan kolom/relasi migrasi untuk section/requirement reference dan indeks. Parser tetap deterministic dan pembuat tugas tetap transaksional/idempotent.
- **Notifikasi:** membutuhkan penyimpanan event/notifikasi, scheduler/job delivery, idempotensi, zona waktu, retry, kanal email/in-app, dan UI status dibaca. Ini pekerjaan backend dan operasi, bukan sekadar perubahan halaman.
- **Akses tim:** perubahan label dapat memakai model pemilik/anggota yang ada; role kustom akan memerlukan skema, policy, dan tes tambahan sehingga tidak disarankan untuk tahap pertama.
- **Responsive dan aksesibilitas:** perubahan template/komponen, interaksi keyboard, dan coverage E2E. Tidak membutuhkan dependency baru bila implementasi mengikuti stack yang terpasang.
- **Data dan keamanan:** jangan menambahkan upload format baru tanpa validasi MIME, batas ukuran, pembersihan metadata, isolasi berkas, dan batas resource parser.

## 12. Validasi dan ukuran keberhasilan

### Skenario uji penerimaan

- Pengguna baru membuat proyek nama-saja tanpa diarahkan wajib mengisi PRD atau integrasi.
- Pengguna menempel/unggah dokumen Markdown, memeriksa hasil, memperbaiki pilihan, commit, dan membuka sumber dari tugas.
- Dokumen kosong, format salah, UTF-8 rusak, duplikat, batas maksimum, serta kegagalan server memberi error yang jelas dan mempertahankan input.
- Pemilik mengundang pengguna; email salah, undangan expired/revoke, penerimaan, pemberian tugas, dan pencabutan akses berperilaku benar.
- Pengguna mengubah tugas di list/papan, memulihkan perubahan gagal, dan tetap dapat menggunakan alternatif keyboard.
- Ikhtisar → Hari ini → fokus → resume bekerja pada personal dan project context.
- Tujuan, rutin, pelajaran, review, dan rekomendasi dapat dikelola tanpa mengubah workflow proyek.
- Pusat notifikasi tidak menampilkan badge atau janji pengiriman untuk event yang belum disimpan/dikirim.
- Semua halaman utama dan state empty/error/loading diperiksa dari ponsel kecil hingga ultrawide.

### Metrik awal yang perlu dikumpulkan

- Persentase pengguna baru yang membuat tugas/proyek dan menyelesaikan satu tugas pertama.
- Tingkat keberhasilan impor: dokumen diproses → draf diperiksa → tugas dibuat.
- Waktu dari buka proyek sampai menemukan langkah berikutnya.
- Tingkat pembatalan/error pada impor, undangan, perubahan status, dan penjadwalan.
- Persentase pengguna yang berhasil melanjutkan proyek setelah kembali dari jeda.

Tetapkan target numerik setelah baseline analytics dan uji pengguna tersedia. Jangan menganggap angka progres sebagai keberhasilan produk jika pengguna masih tidak menemukan aksi berikutnya.

## 13. Risiko dan keputusan yang perlu ditinjau

1. Apakah navigasi yang diusulkan memisahkan kerja harian, proyek/tim, dan pengembangan diri dengan cukup jelas?
2. Untuk impor PRD, apakah format Markdown/TXT cukup untuk tahap berikutnya, atau PDF/Word perlu masuk prioritas setelah riset?
3. Apakah cakupan inbox tugas sebaiknya default ke semua tugas dengan filter eksplisit, atau ke personal dan proyek sebagai tab terpisah?
4. Apakah notifikasi perlu menjadi fitur aktif sekarang, termasuk delivery email dan scheduler, atau untuk sementara hanya preferensi pengingat?
5. Apakah model peran pemilik/anggota sudah cukup untuk target tim kecil?

## 14. Status perubahan

Pengguna menyetujui Tahap 0 secara terbatas. Evaluasi arsitektur, feature relationship matrix, sitemap revisi, alur pengguna, interaction specification, dan backlog tersedia di [docs/product-architecture-review.md](product-architecture-review.md). Dokumen tersebut adalah rekomendasi untuk ditinjau, bukan persetujuan untuk merombak seluruh navigasi atau menjalankan Tahap 1–3.

### Pembaruan v2.1

- Membedakan fakta model data dari hubungan yang belum didukung alur pengguna.
- Menambahkan keputusan fitur yang dipertahankan, diperjelas, digabung sebagai entry point, ditunda, atau perlu divalidasi.
- Menetapkan batas Tahap 0 sesuai persetujuan pengguna dan mempertahankan tahap berikut sebagai keputusan terpisah.
- Menggunakan interaction patterns dari dokumentasi resmi Linear, Notion, ClickUp, Todoist, dan Asana sebagai referensi perilaku; DevFlow tidak menyalin UI atau mengadopsi kompleksitas fiturnya secara mentah.
