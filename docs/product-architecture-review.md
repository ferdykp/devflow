# Evaluasi Arsitektur Produk & UX DevFlow

**Versi:** 1.0  
**Tanggal:** 18 November 2026  
**Status:** Rekomendasi untuk ditinjau; bukan persetujuan untuk Tahap 1–3  
**Batas perubahan yang disetujui:** Tahap 0 terbatas — ketepatan konteks/navigasi dan regresi yang dapat diklasifikasikan dari audit.

> **Pembaruan 18 November 2026:** Instruksi implementasi eksplisit setelah audit memperluas cakupan lokal. Context Generator, scope anchor/baseline, Change Request/PRD immutability, notification feed in-app, dan autosave retry sudah diterapkan serta diuji. Isi dokumen ini tetap menjadi rancangan IA dan user journey; status source terbaru dirangkum di `docs/product-gap-analysis.md` dan `docs/QA-REPORT.md`.

Dokumen ini melengkapi [PRD DevFlow](prd.md). Temuan codebase dibedakan dari rekomendasi. Pola Linear, Notion, ClickUp, Todoist, dan Asana dipakai sebagai rujukan perilaku; arsitektur DevFlow tetap berangkat dari kebutuhan developer dan mempertahankan workspace yang terstruktur serta saling terhubung.

## 1. Keputusan arsitektur produk

### Produk bukan satu inbox untuk semua hal

DevFlow mempunyai tiga konteks kerja dengan ritme berbeda:

1. **Kerja pribadi harian** — memilih pekerjaan, menjadwalkan, fokus, dan mencatat hasil.
2. **Delivery proyek** — menyimpan brief/PRD, tugas tim, milestone, keputusan cakupan, aktivitas teknis, dan konteks untuk melanjutkan.
3. **Pengembangan personal** — tujuan, rutinitas, belajar, refleksi, dan saran.

Ketiganya terhubung lewat referensi eksplisit dan ringkasan yang relevan. Mereka tidak perlu berbagi satu halaman, satu objek “item”, atau satu form besar.

### Keputusan feature-by-feature

| Area saat ini | Keputusan | Alasan dan konsekuensi |
|---|---|---|
| Ikhtisar | **Pertahankan; bedakan dari Hari ini.** | Ikhtisar menjawab “bagaimana keadaan kerja saya?” dan mengarahkan ke satu langkah. Hari ini menjawab “apa yang saya kerjakan sekarang?”. Jangan gabungkan sebelum uji pengguna membuktikan salah satunya tidak dipakai. |
| Rencana hari ini | **Pertahankan; jadikan execution view.** | Di sini pengguna memilih prioritas, melihat jadwal dan refleksi, lalu melanjutkan pekerjaan. Hindari menyalin semua kartu dashboard. |
| Tugas | **Pertahankan satu data tugas; perjelas cakupan dan view.** | List, board, filter, dan detail adalah cara berbeda melihat tugas yang sama. Personal dan proyek harus bisa dibedakan/ difilter, bukan diduplikasi. |
| Proyek & tim | **Pertahankan; pisahkan nama area dari ruang kerja aktif.** | Route “proyek” tidak membuktikan bahwa workspace saat ini adalah tim. Pemilih ruang hanya boleh disebut pemilih jika benar-benar mengubah workspace. Tahap 0 saat ini menyebutnya pengalih area. |
| Resume proyek | **Gabungkan dengan ringkasan sebagai aksi kontekstual; hilangkan tab duplikat setelah validasi.** | “Lanjutkan” lebih berguna sebagai CTA yang jelas dari overview. Halaman resume tetap dapat dibuka melalui tautan; jangan hilangkan data/konten resume. Perubahan ini bukan Tahap 0. |
| PRD/dokumen | **Pertahankan sebagai sumber proyek; satu pintu masuk dokumen.** | Menulis PRD, menyetujui versi, dan mengimpor tugas adalah aksi terkait tetapi berbeda. Gunakan satu area Dokumen & PRD dengan status dan aksi versi yang jelas. |
| Impor PRD | **Pertahankan Markdown/TXT sekarang; ubah urutan interaksi.** | Tampilkan hasil parsing sebelum commit, jadikan tinjau sebagai alur aman, lalu izinkan buat langsung. PDF/Word menjadi kandidat setelah riset dan ekstraksi dapat diandalkan. |
| Tugas hasil impor | **Pertahankan; tambahkan jejak asal yang bisa dibuka.** | Relasi import sudah ada, tetapi posisi/heading sumber perlu menjadi rujukan stabil. Ini memberi alasan tugas tanpa membuat objek requirement baru sebelum kebutuhan terbukti. |
| Milestone proyek | **Pertahankan; hubungkan dengan tugas dan ringkasan.** | Satu milestone proyek dapat mengelompokkan tugas delivery. Jangan campur dengan milestone tujuan personal. |
| Backlog ide | **Pertahankan terpisah dari tugas aktif; tambahkan promosi eksplisit.** | Ide tidak boleh diam-diam menghitung progres. Promosi menjadi task harus meminta status, milestone, dan owner bila relevan. |
| Cakupan / scope review | **Pertahankan hanya jika alur keputusan dipakai.** | Berguna saat PRD/tujuan proyek menjadi acuan, tetapi review dan baseline kini hanya terkait pada proyek, bukan versi PRD tertentu. Rekomendasi jangka menengah: keputusan dapat menyimpan sumber acuan/version; jangan tambah schema pada Tahap 0. |
| Kalender | **Pertahankan sebagai tampilan waktu, bukan duplikasi tugas.** | Task due date adalah tenggat; waktu terjadwal/time block adalah komitmen waktu. Kalender menampilkan event terkait task, goal, routine, atau lesson dan menaut ke sumbernya. |
| Fokus | **Pertahankan sebagai mode kerja, bukan modul perencanaan lain.** | Fokus memilih/menjalankan task dan mencatat sesi. Hindari menambah daftar tugas baru di dalam fokus. |
| Catatan pribadi dan catatan proyek | **Pisahkan; beri nama scope yang tegas.** | Catatan proyek adalah konteks delivery dan kolaborasi; catatan pribadi adalah knowledge/refleksi pemilik akun. Keduanya dapat ditautkan ke goal/task/project/lesson jika hubungan tersebut membantu temu-kembali. |
| Tujuan/rutinitas/belajar | **Pertahankan sebagai ruang pengembangan yang berdiri sendiri.** | Mereka saling terhubung bila pengguna memilih goal, bukan wajib ditautkan ke proyek developer. Jangan membuat setup wizard wajib untuk setiap pengguna. |
| Review mingguan dan Smart Coach | **Pertahankan peran berbeda; kurangi duplikasi rekomendasi.** | Review adalah ritual pengguna meninjau minggu. Coach menghasilkan saran. Saran dapat muncul di review dan halaman Coach menjadi antrean detail hanya jika ada saran; jangan menggabungkan sebelum hasil uji penggunaan. |
| Notifikasi | **Tahan klaim sebagai fitur operasional sampai delivery nyata tersedia.** | Preferensi saja bukan notification center. Feed harus berasal dari event nyata dan actionable. Tidak ada migrasi/pengiriman dalam Tahap 0. |
| GitHub | **Pertahankan sebagai integrasi opsional.** | Aktivitas dan sinkronisasi memberi bukti teknis proyek, tetapi tidak otomatis menyelesaikan tugas atau menentukan progres. |
| Anggota workspace | **Pertahankan pemilik/anggota dulu.** | Model sederhana cukup untuk tim kecil saat ini. Role kustom menambah kebijakan akses dan UI; belum ada bukti yang mengharuskan kompleksitas tersebut. |

Tidak direkomendasikan untuk dihapus dari produk saat ini. Fitur yang berpotensi digabung adalah lokasi/entry point atau ringkasan rekomendasi, bukan record sumber datanya. Keputusan penghapusan memerlukan data pemakaian atau uji tugas.

## 2. Feature relationship matrix

Keterangan: **Ada** = relasi model/database ditemukan; **terbatas** = tersimpan tetapi navigasi/semantik tidak lengkap; **belum ada** = tidak ditemukan relasi domain eksplisit. Tautan route/teks saja tidak dianggap relasi data.

| Sumber → tujuan | Relasi saat ini | Manfaat pengguna | Keputusan pengalaman |
|---|---|---|---|
| Workspace → Project | **Ada** (`project.workspace_id`); akses lewat owner/member | Menentukan akses, anggota, dan konteks proyek | Selalu tampilkan workspace induk pada proyek; jangan infer dari route. |
| Project → Task | **Ada** (`task.project_id`, nullable untuk task personal) | Satu proyek menjadi ruang delivery | Tugas tampil di daftar global dan proyek sebagai record yang sama; tampilkan nama proyek pada inbox lintas proyek. |
| Project → PRD document → version | **Ada**; dokumen memiliki versi draft/approved/superseded | Brief dan perubahan produk tetap berada di proyek | Beri status versi, waktu/pemilik, aksi approve/revise yang dibedakan. |
| PRD version → import → Task | **Ada** secara transitif (`DocumentTaskImport.prd_version_id`, `Task.document_task_import_id`, `source_index`) | Mengubah struktur sumber menjadi pekerjaan dan mencegah impor duplikat | Tugas menunjukkan nama versi/dokumen dan section/posisi sumber yang dapat diklik. Jangan membuat requirement entity dulu. |
| Project → Milestone → Task | **Ada**; task memiliki nullable `milestone_id` | Mengelompokkan dan meninjau hasil delivery | Tugas dapat diberi milestone inline; milestone menunjukkan tugas terkait dan state agregat yang jujur. |
| Project → Scope baseline/review/decision | **Ada terbatas**; baseline dan review terkait project, decision terkait review | Memeriksa usulan terhadap batas proyek | Tunjukkan versi PRD/baseline yang dipakai. Saat ini baseline belum terikat ke PRD version; keputusan perlu menyebutkan sumbernya secara eksplisit sebelum UI mengklaim traceability. |
| Project → Backlog item | **Ada**; backlog item terkait project, bukan task | Menunda ide tanpa mencemari rencana aktif | Pertahankan pemisahan. Aksi “Jadikan tugas” harus berupa konversi terkonfirmasi dengan jejak asal. |
| Project → GitHub repository → Activity | **Ada terbatas**; repo/activity terikat pada project | Melihat aktivitas remote dan membantu resume | Aktivitas GitHub menaut ke repo/commit/pull request bila tersedia. Jangan mengubah status task otomatis. |
| Task → Calendar event | **Ada**; event dapat mengacu task | Menghubungkan pekerjaan dengan slot waktu | Bedakan deadline dari jadwal; event menaut ke task dan perubahan dari kalender jelas apakah memindah waktu atau tenggat. |
| Task → Focus item | **Ada**; focus item menunjuk task dan tanggal | Memulai sesi dari pekerjaan, bukan dari modul baru | “Mulai fokus” tersedia dari detail, Hari ini, dan ringkasan. Status sesi/timer konsisten setelah refresh. |
| Task → Personal goal | **Ada** hanya untuk task personal (validasi melarang goal pada project task) | Menghubungkan tindakan personal ke tujuan | Label hubungan harus menjelaskan constraint; jangan menautkan task proyek ke personal goal tanpa keputusan privasi/model. |
| Task → Learning lesson | **Ada** hanya untuk task personal | Menjadikan tindak lanjut belajar sebagai pekerjaan | Lesson menampilkan task terbuka; task dapat membuka lesson. |
| Goal → Task/routine/learning path/note/calendar event | **Ada** sebagai foreign key/has-many | Menggabungkan bukti progres dari aktivitas berbeda | Hubungan opsional; tampilkan “terkait” dan progres berbasis bukti, bukan memaksa setiap area punya goal. |
| Routine → Goal/Learning path/Calendar event/check-in | **Ada** | Mengulang perilaku dan menjadwalkannya | Satu check-in tidak otomatis membuat task; kalender event berlabel sebagai rutinitas. |
| Learning path → module → lesson → session/assessment/material | **Ada** | Belajar memiliki urutan, bahan dan bukti pemahaman | Tampilkan jalur → modul → lesson; task dan event tetap shortcut/turunan opsional. |
| Personal note → goal/task/project/lesson | **Ada** nullable links | Membuka kembali catatan dari konteks asal | Tampilkan konteks relasi di catatan dan tautan balik. Hindari satu koleksi yang mencampur catatan team-private. |
| Weekly review → carry-forward task IDs | **Terbatas**; snapshot IDs JSON, bukan relasi live | Memindahkan pekerjaan ke minggu selanjutnya | Simpan tindakan review dengan hasil yang dapat dibuka; cegah ID usang tanpa recovery. |
| Smart Coach → source data/action | **Terbatas**; rekomendasi menyimpan proposed action, status konfirmasi/abaikan | Saran dapat diterapkan dengan izin pengguna | Tampilkan alasan, sumber, konsekuensi sebelum konfirmasi; tawarkan undo atau edit. |
| Notification → task/project/goal/event | **Belum ada feed/delivery** dalam implementasi saat audit | Mengarahkan pengguna kembali ke tindakan | Tahap 0 tidak membuat notifikasi; kemudian bangun event dari sumber nyata atau keluarkan link/promise yang belum berfungsi. |

### Alur hubungan yang paling penting

```text
Workspace → Project → (PRD version → import → Task) → Milestone
                           ├→ Scope review/baseline
                           ├→ GitHub repository → Activity
                           └→ Project note

Personal task → Goal / Learning lesson → Calendar event / Focus item
Goal → Routine / Learning path / Note / Calendar event
Weekly review → keputusan carry-forward → task minggu berikutnya
```

Alur di atas adalah peta relasi, bukan kewajiban mengisi semua relasi. Pengguna perlu memilih kapan hubungan membantu pekerjaan.

## 3. Revised sitemap

```text
Publik
├── Landing
├── Masuk / daftar
├── Verifikasi email / reset password / Google OAuth
└── Privasi / syarat layanan

Aplikasi
├── Ikhtisar
├── Hari ini
├── Tugas
│   ├── Inbox dengan cakupan Semua | Pribadi | Proyek
│   ├── List / Papan (view dari dataset sama)
│   └── Detail tugas (drawer/route yang bisa deep-link)
├── Proyek & tim
│   ├── Semua proyek + filter ruang kerja
│   ├── Buat/edit proyek
│   ├── Ruang kerja + anggota/invitasi
│   └── Proyek
│       ├── Ringkasan
│       ├── Tugas (list/papan)
│       ├── Dokumen & PRD (versi, impor, hasil impor)
│       ├── Tahapan/milestone
│       ├── Cakupan dan review
│       ├── Ide nanti/backlog
│       ├── Aktivitas
│       ├── Catatan proyek
│       ├── GitHub (opsional)
│       ├── Pengaturan proyek
│       └── Lanjutkan (aksi/link dari ringkasan)
├── Kalender
├── Fokus
├── Pengembangan personal
│   ├── Tujuan + progres/milestone
│   ├── Rutinitas + check-in
│   ├── Belajar → jalur → modul → lesson → sesi/asesmen/bahan
│   ├── Catatan pribadi
│   ├── Review mingguan
│   └── Smart Coach (saran menunggu tindakan)
└── Akun
    ├── Profil/jadwal
    ├── Pengingat/notifikasi
    └── Integrasi
```

### Aturan navigasi

- Sidebar desktop mengelompokkan **Hari kerja**, **Proyek & tim**, **Pengembangan**; akun dan konfigurasi ada di menu akun. “Buat baru” berisi tindakan create.
- Mobile menampilkan tugas harian/proyek/aksi tambah; menu lainnya membuka drawer yang dapat diakses keyboard.
- Deep link selalu membuka konteks yang tepat dan menunjukkan breadcrumb/ruang induk.
- Menu “ruang kerja” dipakai hanya bila memilih workspace aktif sungguhan. Jika kontrol sekadar berpindah domain, labelnya “Pindah area”.
- Menu proyek inti dibatasi pada Overview/Tugas/Dokumen; fitur tambahan dikelompokkan berdasarkan kerja: Plan (milestone/scope/backlog), Context (catatan/aktivitas), Integrasi (GitHub), Admin (anggota/pengaturan). Kelompok tidak boleh menyembunyikan item aktif.

## 4. End-to-end user flows

### Flow 1 — Mulai tanpa setup berat

```text
Daftar → verifikasi/onboarding minimal → Ikhtisar
→ tambah task pribadi ATAU buat proyek nama-saja ATAU buka Hari ini
→ aksi pertama selesai → saran langkah relevan, bisa dilewati
```

**Keputusan:** jangan meminta goal, workspace tim, PRD, GitHub, notifikasi, atau AI sebelum pengguna memperoleh hasil pertama.

### Flow 2 — PRD ke delivery

```text
Proyek → Dokumen & PRD → tambah PRD (tempel/TXT/MD)
→ parser memberi preview hasil, yang diabaikan dan peringatan
→ pengguna memilih/menyunting tugas dan kelompok
→ buat tugas → tugas membawa milestone/import/source
→ buka board/list → mulai dari tugas → fokus
→ milestone/project overview menampilkan progres aktual
```

Versi PRD yang disetujui adalah acuan; revisi draft tidak mengganti versi approved diam-diam. Hasil parser tidak menganggap setiap bullet adalah task tanpa preview.

### Flow 3 — Capture dan eksekusi tugas

```text
Tombol Buat baru / quick-add → judul + konteks minimum
→ simpan cepat → tampil di inbox/scope proyek yang dipilih
→ tambah detail opsional (prioritas, tenggat, assignee, milestone, goal/lesson)
→ list/board → status berubah → detail/history terbarui
```

**Keputusan:** detail tambahan ditampilkan progresif. Bila quick-add menerima sintaks tanggal/prioritas, sediakan autocomplete/preview; jangan mewajibkan pengguna menghafal sintaks.

### Flow 4 — Task dan waktu

```text
Task dengan deadline → tampil pada Kalender sebagai tenggat
Task dengan time block → event kalender tertaut ke task
Kalender → edit waktu → update event yang sama → buka Task
Fokus → mulai timer dari task → sesi tersimpan ke task
```

Jangan membuat task kedua ketika membuat agenda untuk task. Perubahan jam tidak boleh diam-diam mengubah due date.

### Flow 5 — Goal, kebiasaan dan belajar

```text
Buat Goal opsional → pilih tindakan yang mendukung:
Task / Routine / Learning Path / catatan / agenda
→ progres goal menarik bukti dari relasi tersebut
→ pengguna dapat melepas hubungan tanpa menghapus sumber
→ Review mingguan meninjau hasil
```

### Flow 6 — Ide dan perubahan cakupan

```text
Capture ide ke backlog (tidak dihitung pekerjaan aktif)
→ review terhadap baseline/PRD bila ada
→ pilih: simpan ide | jadikan task | ubah scope melalui keputusan eksplisit
→ konfirmasi dampak milestone/progres
→ tautkan keputusan dan sumber acuan
```

### Flow 7 — Kolaborasi proyek kecil

```text
Pemilik membuat/memilih workspace → buat proyek
→ undang email dan tunjukkan siapa yang punya akses
→ anggota verifikasi akun/email → menerima undangan
→ task ditugaskan/didiskusikan → update yang relevan dapat ditindaklanjuti
→ pemilik mengeluarkan anggota → jelaskan task yang akan dilepas
```

## 5. Interaction specification per permukaan

| Permukaan | Primary action | Interaksi utama | Empty/loading/error/recovery |
|---|---|---|---|
| Ikhtisar | Buka tindakan berikutnya | Ringkasan task/proyek/jadwal; angka dapat diklik ke sumber | Data kosong memberi CTA; skeleton saat memuat; jangan buat metrik dari data yang tidak ada. |
| Hari ini | Mulai/selesaikan prioritas | Pilih maksimal tiga prioritas; buka agenda, fokus, refleksi | Empty state mulai dari task; perubahan fokus dibatalkan jika API gagal. |
| Inbox tugas | Tambah atau mulai task | Filter scope/status/due/project; search; simpan view; list ↔ board dari data yang sama; inline status/assignee | Bedakan “belum ada tugas” dari “filter tidak menemukan tugas”; reset filter; error drag/drop mengembalikan posisi dan menyebut alasan. |
| Quick-add task | Simpan task dengan input minimum | Judul selalu; konteks proyek dipilih bila masuk dari proyek; detail tambahan inline/drawer | Pertahankan draft saat tutup/validasi; tampilkan tujuan penyimpanan sebelum commit jika global. |
| Detail task | Selesaikan langkah berikutnya | Status, assignee, priority, deadline, milestone, links, checklist, comment, history; tiap relasi link-back | Autosave hanya bila status simpan jelas; destructive delete meminta konfirmasi; komentar gagal tidak hilang. |
| Daftar proyek | Buka/buat proyek | Cari, filter workspace, status; create project dari area; akses anggota tampak | Empty state tawarkan proyek nama-saja atau workspace; reset filter satu klik; jelaskan cakupan “semua workspace”. |
| Buat/edit proyek | Simpan metadata minimum | Nama wajib; deskripsi/tujuan/workspace sebagai field bertahap; PRD/GitHub opsional setelah create | Preserve input; validasi inline; setelah save arahkan ke project overview dengan tindakan berikutnya, bukan memaksa setup penuh. |
| Proyek overview | Mulai delivery atau lanjutkan | Status/tujuan, task berikutnya, milestone, dokumen/aktivitas ringkas; CTA tambah task/PRD/lanjutkan | Empty project menunjukkan maksimal beberapa langkah setup opsional; tidak menampilkan progress 0% bila belum ada denominator. |
| Dokumen/PRD | Tambah, approve, atau impor versi | Editor/source, riwayat versi; separate actions Draft, Approve, Generate tasks; preview import | Kegagalan upload/parsing mempertahankan teks/berkas jika memungkinkan; tampilkan format/batas; import duplicate punya link ke hasil sebelumnya. |
| Import review | Buat hanya tugas yang dimaksud | Preview heading/group/task/checklist; select all by group; edit title/assignee/date; bulk include/exclude; confirm count | Hasil nol minta edit dokumen; batas 50/1000 dijelaskan; simpan draft dan pagination; commit idempotent/duplicate. |
| Milestone | Tambah/ubah tahap dan lihat task | Task dapat dihubungkan inline; reorder hanya jika urutan punya arti | Empty state “buat tahapan atau lanjut tanpa milestone”; tanggal yang bentrok ditandai tanpa memblokir semua input. |
| Cakupan/scope | Tinjau perubahan | Tampilkan acuan, source review, keputusan, alasan, efek pada task/backlog; approval eksplisit | Tanpa baseline jangan beri verdict pasti; AI/advisory menyatakan ketidakpastian. |
| Backlog | Menyimpan ide dan mempromosikan terpilih | Quick capture; edit; filter; “jadikan task” mengubah record/menautkan origin | Empty state menjelaskan beda ide dan task aktif; undo/recover jika promosi gagal. |
| Kalender | Jadwalkan atau ubah slot | Day/week/month; task-linked event; drag hanya desktop dengan form alternatif; edit occurrence vs series | No agenda CTA; konflik timezone; drag failure memulihkan posisi; perubahan recurring meminta cakupan. |
| Fokus | Mulai/hentikan sesi | Pilih dari task; timer/resume; tandai hasil | Tanpa task tawarkan pilih task; stop/timer memiliki state server yang konsisten; gagal simpan durasi memberi retry. |
| Tujuan | Catat hasil yang terukur | Progress number/milestone; relasi task/routine/learning/note/event opsional | Hindari progress palsu saat belum ada baseline; beri aksi pertama kecil. |
| Rutinitas | Catat check-in | Satu tap check-in; jadwal dan goal link opsional | State completed/missed/skip dibedakan; undo check-in bila keliru. |
| Belajar | Lanjutkan lesson berikut | Jalur → modul → lesson; sesi, asesmen, materi; lesson dapat membuat follow-up task | Progress jalur dari lesson actual; materi gagal diunduh tetap memberi retry. |
| Catatan pribadi/proyek | Tulis tanpa takut kehilangan | Autosave dengan status; source relation; Markdown; pin/archive | Menyimpan/tersimpan/gagal terlihat; retry; prompt sebelum meninggalkan jika draft belum tersimpan. |
| Review mingguan | Buat keputusan minggu berikut | Ringkasan bukti; selesai/tunda/carry forward; pilih task | Review dapat disimpan bertahap; perubahan carry-forward tercatat; data mingguan kosong dijelaskan. |
| Smart Coach | Tinjau satu saran | Bukti/alasan → edit/konfirmasi/abaikan; link ke item sumber | Saran usang diberi stale label; retry AI; tak ada perubahan tanpa confirm. |
| Anggota/invitasi | Undang atau kelola akses | Owner sees access; invite link/email, expire/revoke, remove member with task consequences | Wrong email/expired/used token memberi recovery; mobile confirmation tidak bergantung pada elemen sidebar tertutup. |
| Notifikasi | Buka update dan ambil tindakan | Filter kategori/belum dibaca; mark read/archive/open source | Bila delivery belum ada, jangan tampilkan badge, empty copy yang menjanjikan, atau pilihan kanal seolah aktif. |
| Integrasi/settings | Sambungkan atau perbaiki koneksi | Status, izin, last sync, reconnect/disconnect; settings profil/jadwal | Unconfigured vs disconnected vs error berbeda; jangan sukses palsu saat secret/env belum tersedia. |
| Auth/onboarding | Masuk dan capai hasil pertama | Form sesingkat mungkin; Google OAuth; email verification hanya sesuai kebijakan | Error di field, show/hide password, intended redirect, resend/cancel; landing publik bukan redirect paksa. |

## 6. Backlog peningkatan produk

Backlog ini adalah urutan rekomendasi, bukan izin mengerjakan seluruh tahap sekaligus.

| ID | Prioritas | Perbaikan | Nilai pengguna | Dampak teknis / dependensi | Status persetujuan |
|---|---|---|---|---|---|
| UX-00 | P0 | Hilangkan label ruang kerja tim yang salah pada daftar proyek; sebut kontrol saat ini pengalih area | Mengurangi salah konteks tanpa mengubah data | Blade shell/index; test responsive/nav | **Disetujui Tahap 0** |
| Q-00 | P0 | Klasifikasikan dan perbaiki tes regresi yang masih menguji kontrak lama | Membuat sinyal CI mencerminkan perilaku yang diniatkan | Test-only untuk ekspektasi stale; produk hanya jika ada bug nyata | **Disetujui Tahap 0** |
| UX-01 | P1 | Validasi ulang grouping sidebar dan nama setiap area dengan uji tree/task | Mempercepat temu-menu | Blade, mobile bottom nav, persistence nav; perlu persetujuan IA | Belum disetujui |
| UX-02 | P1 | Default project buka overview yang menjawab konteks/next step; kurangi tab Resume duplikat | Mempercepat mulai dan lanjut kerja | Route default/nav tests; perlu uji pengguna sebelum ubah | Belum disetujui |
| UX-03 | P1 | Scope toggle inbox Personal / Semua proyek / satu proyek | Mengurangi ambiguitas task lintas konteks | URL filters, query/permissions, saved views | Belum disetujui |
| UX-04 | P1 | Quick-add bertahap dan context-aware | Mengurangi form/bolak-balik | Create components, draft retention, keyboard; ukur completion | Belum disetujui |
| UX-05 | P1 | Hubungkan dokumen tugas ke section sumber yang stabil | Mengurangi waktu verifikasi asal task | Parser source anchors, schema/index, migration/backfill | Belum disetujui |
| UX-06 | P1 | Samakan desktop drag-and-drop dengan keyboard/status control dan restore state | Interaksi lebih handal | JS request lifecycle, response/toast, Playwright regression | Belum disetujui sebagai tahap lintas produk; bug P0 harus diverifikasi dulu |
| UX-07 | P1 | Tampilkan task/event/goal/lesson relation dengan link balik kontekstual | Memakai data relasi yang sudah ada | Blade detail/summary; authorization/scope checks | Belum disetujui |
| UX-08 | P1 | Catatan autosave menampilkan state dan mencegah draft hilang | Mengurangi kehilangan tulisan | JS save state/network retry; mungkin conflict handling | Belum disetujui |
| UX-09 | P1 | Tampilkan rekomendasi coach dalam review tanpa membuat saran duplikat | Mengurangi pindah halaman | Coach/review view model, source links; perlu data pemakaian | Belum disetujui |
| SYS-01 | P1 | Buat notification feed nyata hanya jika pengguna mengonfirmasi kebutuhan | Mengubah preferensi menjadi fungsi actionable | Schema, event generation, scheduler, queue, timezone, delivery/read state | Keputusan produk dibutuhkan |
| SYS-02 | P2 | Ikat scope decision/baseline ke versi dokumen acuan | Audit scope dapat ditelusuri | Migration/version relation/backfill + policy | Belum disetujui |
| SYS-03 | P2 | Evaluasi PDF/Word parsing | Mengurangi copy-paste bila banyak dipakai | Secure extraction/queue/preview/resource limits | Riset dahulu |
| SYS-04 | P2 | Evaluasi role selain owner/member | Mendukung tim dengan akses granular | Roles/policies/invitation UI/tests | Riset dahulu |

## 7. Rencana perubahan Tahap 0 yang disetujui

### Dalam cakupan

1. Mengganti label/eyebrow “Ruang kerja tim” di halaman gabungan proyek menjadi label netral dan faktual.
2. Mengubah nama kontrol dari “Pilih ruang kerja” menjadi “Pindah area” karena control saat ini hanya mengarahkan ke Personal atau Proyek & tim; tidak membuat state workspace selection baru.
3. Mengubah breadcrumb/topbar/sidebar agar menyebut domain **Proyek** alih-alih menyatakan seluruh halaman proyek adalah **Tim**. Nama workspace aktual tetap ditampilkan di breadcrumb/detail yang sudah memiliki data tersebut.
4. Memperbaiki test E2E yang mengasumsikan menu tertutup tetap bisa diakses pada mobile atau asumsi kontrak lama; tetap menguji perilaku yang memang terlihat oleh pengguna.
5. Menyelidiki drag-and-drop gagal; ubah produk hanya jika request/error path gagal secara nyata, bukan untuk membungkam assertion.

### Di luar cakupan

- Mengubah warna, sistem desain, layout visual, atau menata ulang seluruh sidebar.
- Membuat pemilih workspace persisten/state baru.
- Mengubah default section proyek, model task, schema PRD, notifikasi, role, atau data domain.
- Memulai implementasi backlog P1/P2.

### Kriteria selesai Tahap 0

- Tidak ada label route yang menyatakan “workspace tim” ketika halaman merupakan agregat personal dan tim.
- Kontrol area menjelaskan perilaku dan link yang dibuka; pada mobile akses dilakukan setelah drawer/menu terkait dibuka.
- Test yang tersisa membedakan bug aktual dan expectation stale dengan assertion berbasis UI/API yang benar.
- Seluruh PHP feature tests yang relevan dan seluruh E2E suite lulus; jika ada baseline failure yang bukan bagian Tahap 0, asalnya dikonfirmasi dan diberi test expectation yang tepat tanpa mengubah aturan produk.
- Tidak ada perubahan database atau perilaku fitur di luar batas tersebut.

### Hasil pelaksanaan

- Label area di sidebar, topbar, dan daftar proyek kini menjelaskan konteks tanpa menganggap semua proyek sebagai workspace tim.
- PHP feature tests: **128 lulus, 867 assertion**.
- E2E lengkap: **47 lulus, 9 dilewati**; yang dilewati adalah skenario desktop-only pada proyek mobile/touch. Setelah penyesuaian akhir pada konteks topbar workspace, dua skenario undangan desktop/mobile kembali lulus.
- Kegagalan yang diselidiki berasal dari ekspektasi test yang usang atau interaksi test yang tidak sesuai UI: landing page publik, login OAuth untuk akun Google terverifikasi, menu akun tertutup, dan drag-and-drop failure path. Tidak ada perubahan aturan produk untuk membungkam tes; jalur error drag-and-drop diuji dengan event drag/drop yang deterministik dan memastikan rollback saat server mengembalikan 503.

## 8. Referensi pola interaksi

- [Linear — Projects](https://linear.app/docs/projects): proyek menyatukan overview, resources, milestones, task entry, dan views; dipakai sebagai rujukan konteks delivery, bukan disalin sebagai tab set.
- [Linear — Documents](https://linear.app/docs/documents): dokumen melekat ke proyek dan dapat mereferensikan task/header; dipakai sebagai rujukan tautan sumber dan context navigation.
- [Linear — Display options](https://linear.app/docs/display-options) dan [board layout](https://linear.app/docs/board-layout): list/board merupakan view dari issue yang sama dengan grouping/ordering yang dapat dipahami.
- [Notion — Projects and tasks](https://www.notion.com/help/guides/getting-started-with-projects-and-tasks) dan [relations/rollups](https://www.notion.com/help/relations-and-rollups): relasi dua arah dan beberapa view membantu proyek/tugas tersambung tanpa menggandakan record.
- [ClickUp — Relationships](https://help.clickup.com/hc/en-us/articles/6304528030743-Intro-to-Relationships): task dapat merujuk task/dokumen/dependency; DevFlow sebaiknya mengadopsi related references sederhana, bukan custom relationship framework penuh.
- [Todoist — Quick Add](https://www.todoist.com/help/todoist/features/use-keyboard-shortcuts-in-todoist-Wyovn2) dan [filters](https://www.todoist.com/help/todoist/features/introduction-to-filters-V98wIH): capture cepat dan saved query mengurangi form berulang; DevFlow perlu menawarkan discoverable controls, bukan mengharuskan pengguna hafal sintaks.
- [Asana — Project overview](https://help.asana.com/s/article/project-overview-tab) dan [Inbox](https://help.asana.com/s/article/inbox): overview menautkan brief/tugas/milestone; notification center berguna karena update dapat ditindaklanjuti dan diarsipkan.

## 9. Pertanyaan produk yang masih terbuka

Keputusan ini tidak menghambat Tahap 0, tetapi perlu dijawab sebelum mengubah IA dan workflow lebih luas:

1. Pengguna mana yang paling penting dalam 6–12 bulan: solo developer, tim 2–5 orang, atau keduanya dengan prioritas seimbang?
2. Apakah inbox tugas harus default “Semua” atau “Ditugaskan kepada saya” setelah scope terlihat?
3. Apakah focus session harus menjadi timer yang terkait task atau time-block yang otomatis berada di kalender?
4. Apakah scope review/PRD traceability termasuk pekerjaan inti developer atau fitur sekunder?
5. Seberapa sering pengguna mengimpor PDF/Word dibanding menempel/unggah Markdown?
6. Apakah notifikasi in-app/email diperlukan setelah pengingat tugas/rutinitas yang sesungguhnya tersedia?

Tanpa riset pengguna, rekomendasi backlog tetap hipotesis. Uji moderated task yang disarankan: buat proyek tanpa PRD, impor PRD Markdown dan tinjau, ambil task ke hari ini, jadwalkan/fokuskan, lanjutkan setelah konteks berpindah, lalu lakukan satu review mingguan.
