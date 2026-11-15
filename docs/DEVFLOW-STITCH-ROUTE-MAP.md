# Pemetaan layar Google Stitch ke DevFlow

Dokumen ini mencatat hubungan antara inventaris S01–S45 dan route/view Laravel saat ini. `Tersedia` berarti ada halaman atau alur yang dapat dibuka. `Tertanam` berarti konten atau aksi ada di halaman induk, tetapi belum memiliki layar/rute mandiri seperti yang dispesifikasikan Stitch. `Belum ada` berarti belum ada route atau data aplikasi untuk layar tersebut.

## Layar

| ID | Layar Stitch | Route / implementasi DevFlow | Status dan catatan |
|---|---|---|---|
| S01 | Login | `login` | Tersedia |
| S02 | Pendaftaran | `register` | Tersedia |
| S03 | Selamat datang / pilihan ruang kerja | `onboarding.welcome`, `workspaces.create`, `invitations.show` | Tersedia; pilihan ruang pribadi/tim dan daftar workspace |
| S04 | Pengaturan awal | `onboarding.setup` | Tersedia; zona waktu, hari/jam kerja, dan tujuan belajar opsional |
| S05 | Ikhtisar | `dashboard` | Tersedia; ringkasan dari data pengguna |
| S06 | Hari Ini | `my-day.index` | Tersedia; agenda, tugas, rutinitas, refleksi |
| S07 | Kotak Masuk | `inbox.index` | Tersedia; tangkap, filter, ubah, hapus, dan diskusi tugas yang dapat diakses |
| S08 | Semua Tugas | `tasks.index` | Tersedia; daftar tugas personal/proyek, filter, papan, dan aksi massal |
| S09 | Detail Tugas | `personal-tasks.show` dan `tasks.show` | Tersedia; detail tugas pribadi/proyek, edit, checklist, histori, komentar dan aksi |
| S10 | Planner / Minggu | `calendar.index?view=week` | Tersedia; kolom per hari dan drill-in ke agenda harian |
| S11 | Planner / Hari | `calendar.index?view=day` | Tersedia; timeline per jam, agenda sepanjang hari, tambah dari slot waktu |
| S12 | Planner / Bulan | `calendar.index?view=month` | Tersedia; kisi kalender bulanan dengan drill-in dan overflow |
| S13 | Rutinitas | `routines.index` | Tersedia; daftar, check-in, jeda, dan lanjutkan |
| S14 | Detail Rutinitas | `routines.show` | Tersedia; jadwal, riwayat, check-in, edit, jeda, lanjutkan, dan arsip |
| S15 | Goals | `goals.index` | Tersedia; daftar, progress, milestone, status, dan tautan tindakan |
| S16 | Detail Goal | `goals.show` | Tersedia; milestone, bukti progres, aktivitas terkait dan edit goal |
| S17 | Learning Hub | `learning.index` | Tersedia; jalur, antrean review, dan riwayat sesi |
| S18 | Detail Jalur Belajar | `learning.paths.show` | Tersedia; modul berurutan, progres dan edit jalur |
| S19 | Detail Modul | `learning.modules.show` | Tersedia; daftar pelajaran serta aksi buat/edit modul |
| S20 | Pembaca Pelajaran | `learning.lessons.show` | Tersedia; reader, status, sesi, latihan, lampiran dan navigasi pelajaran |
| S21 | Sesi Belajar | `learning.sessions.index` | Tersedia; tabel sesi dengan edit/hapus dan tautan ke materi |
| S22 | Penilaian / Antrean Review | `learning.assessments.index` | Tersedia; antrean review jatuh tempo, hasil latihan edit/hapus |
| S23 | Perpustakaan Catatan | `personal-notes.index` | Tersedia; cari, arsip, pin, tautan, dan CRUD catatan |
| S24 | Editor Catatan | `personal-notes.show` | Tersedia; editor halaman dengan autosave, tag, koleksi dan konteks tertaut |
| S25 | Review Mingguan | `weekly-review.index` | Tersedia; ringkasan aktivitas, refleksi, rencana, dan carry-forward |
| S26 | Smart Coach | `smart-coach.index` | Tersedia; alasan saran dan konfirmasi/tutup eksplisit |
| S27 | Daftar Proyek | `projects.index` | Tersedia |
| S28 | Ikhtisar Proyek | `projects.show?section=overview` | Tersedia |
| S29 | Tugas Proyek | `projects.show?section=tasks` | Tersedia; daftar dan papan proyek |
| S30 | Detail Tugas Proyek | `tasks.show` | Tersedia; terikat ke proyek dan akses workspace |
| S31 | Fokus Proyek / Tugas | `focus.index` | Tersedia sebagai daftar fokus lintas tugas personal dan proyek |
| S32 | Milestone Proyek | `projects.show?section=milestones` | Tersedia |
| S33 | Catatan dan Backlog Proyek | `projects.show?section=notes`, `projects.show?section=backlog` | Tersedia sebagai bagian terpisah |
| S34 | PRD / Dokumen | `projects.show?section=prd` | Tersedia; versi, persetujuan, dan revisi |
| S35 | Scope / Review | `projects.show?section=scope` | Tersedia |
| S36 | Lanjutkan Proyek | `projects.resume` | Tersedia |
| S37 | Aktivitas / Insights | `projects.show?section=activity`, `projects.insights` | Tersedia; aktivitas proyek dan analisis yang dapat ditinjau |
| S38 | Repositori GitHub | `projects.show?section=github`, route `github.*` | Tersedia |
| S39 | Workspace dan Anggota | `workspaces.members.show` | Tersedia; anggota dan undangan |
| S40 | Profil dan Preferensi | `settings.index` | Tersedia; profil, zona waktu, jadwal dan pengingat |
| S41 | Jam Kerja dan Default Jadwal | `settings.index` bagian jam kerja | Tersedia; hari kerja, jam mulai/selesai dan tujuan belajar tersimpan pada preferensi pengguna |
| S42 | Notifikasi | `settings.index`, `notifications.index` | Tersedia; preferensi kanal/topik dan pusat notifikasi dengan empty state |
| S43 | Integrasi dan AI | `settings.integrations` | Tersedia; status konfigurasi GitHub App dan AI dari server, tanpa menampilkan rahasia |
| S44 | Pencarian / Command Palette | Dialog `df-command` pada app shell | Tersedia; pencarian destinasi dan jalan pintas pencarian record tugas |
| S45 | Error / Permission / Offline | `errors.403`, `errors.404`, `errors.419`, `errors.500`, `public/offline.html` | Tersedia; navigasi kembali dan tindakan coba lagi |

## Overlay dan state

| ID | Kebutuhan Stitch | Implementasi saat ini |
|---|---|---|
| O01 | Quick-add tugas | Menu global `Tambah` membuka form tugas, agenda, atau catatan |
| O02 | Buat/edit penuh tugas | Detail tugas personal/proyek memiliki form edit dan tindakan lengkap |
| O03 | Hapus/arsip tugas | Hapus meminta konfirmasi; catatan dan goal dapat diarsipkan, tugas belum memiliki semantik arsip umum |
| O04 | Buat/edit agenda dan pengecualian berulang | Agenda dapat dibuat, diperbarui, dibatalkan, dan kejadian berulang dijadwalkan ulang |
| O05 | Penyelesaian konflik jadwal | Konflik ditandai pada kalender dengan tautan ke hari terkait untuk penyesuaian event |
| O06 | Buat/edit rutinitas | Detail rutinitas menyediakan edit metadata dan pengelolaan status |
| O07 | Lewati/jeda rutinitas | Lewati/check-in dan jeda tersedia; tidak ada dialog khusus |
| O08 | Buat/edit goal | Detail goal menyediakan edit metadata, progress, status dan arsip |
| O09 | Buat/edit milestone | Pembuatan dan perubahan selesai/buka kembali tersedia |
| O10 | Buat/edit jalur, modul, pelajaran | Pembuatan jalur/modul/pelajaran dan perubahan status pelajaran tersedia; edit semua metadata belum tersedia |
| O11 | Catat sesi belajar | Tersedia pada alur pelajaran |
| O12 | Hasil latihan/penilaian | Form pencatatan hasil tersedia pada alur pelajaran |
| O13 | Buat/ganti nama/hapus catatan | CRUD catatan tersedia; editor berupa panel kontekstual |
| O14 | Selesaikan review mingguan | Simpan refleksi/rencana dan carry-forward tersedia; belum ada drawer penyelesaian |
| O15 | Pratinjau dan setujui saran | Penjelasan saran dan konfirmasi/tutup tersedia; tidak ada pratinjau aksi terpisah |
| O16 | Buat/edit proyek | Route pembuatan dan pengeditan proyek tersedia |
| O17 | Undang/kelola peran anggota | Alur undangan, penerimaan, dan pencabutan akses tersedia; model peran terbatas pada aturan workspace saat ini |
| O18 | Sambung/putus GitHub | Integrasi dan aksi repositori tersedia |
| O19 | Toast, validasi, loading, permission empty | Toast dan validasi tersedia; skeleton/loading khusus belum dipakai untuk halaman server-rendered |

## Catatan cakupan

- Folder Stitch menyediakan 39 ekspor layar/overlay dalam HTML dan screenshot. Dokumen spesifikasi mendefinisikan 45 layar dan 19 overlay; beberapa target konseptual tidak memiliki ekspor visual tersendiri.
- Pemetaan di atas mengikuti route dan view repository. Integrasi AI dan notifikasi menampilkan status/preferensi sesuai konfigurasi; pengiriman email dan jadwal pengingat tetap memerlukan layanan backend yang dikonfigurasi.
- Penyelesaian layar ini menambahkan onboarding, detail personal, kalender berbentuk planner, preferensi jadwal, pusat notifikasi, integrasi dan status pemulihan di atas shell visual Stitch.
