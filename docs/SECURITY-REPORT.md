# Laporan keamanan — 21 Oktober 2026

## Bukti lokal

- **PASS:** advisori `composer audit` dan `npm audit --omit=dev`: tidak ditemukan kerentanan yang dilaporkan pada dependency terpasang. Ini bukan jaminan tidak ada kerentanan source.
- **PASS:** batas repository GitHub OAuth diperbaiki agar menggunakan token pengguna. Tes memeriksa header Bearer token pengguna, endpoint izin pengguna, dan penolakan repository di luar pilihan. Referensi: [GitHub installations API](https://docs.github.com/en/rest/apps/installations).
- **PASS:** otorisasi proyek/workspace, task/milestone dan dokumen lintas proyek ditolak; output task di-escape dan payload ownership tidak tervalidasi diabaikan. Akses data pengguna lain memakai 404.
- **PASS:** session diregenerasi saat login/register, logout menginvalidasi session. CSRF browser menolak POST tanpa token dengan 419. Limiter login menolak percobaan keenam akun/IP yang sama dengan 429; register memiliki bucket tersendiri. Pembatasan IP agregat login tetap ada.
- **PASS:** webhook memakai HMAC, validasi delivery ID, penolakan duplicate, payload limit, persistence antrean, rekonsiliasi dan pencatatan hasil; regresi yang tersedia lulus.
- **PASS lokal:** AI nonaktif tidak memanggil provider; consent diperlukan; respons gagal/tidak valid tidak memutasi proyek. Data yang dikirim dibatasi dan output di-escape. Provider nyata belum diuji. Jangan memasukkan secret dalam PRD/nama tugas/catatan yang akan dibagikan; consent mencakup cuplikan konten tersebut.
- **PASS lokal:** middleware menambahkan `nosniff`, penolakan framing, kebijakan referrer, permissions dan CSP minimum untuk object/base/frame. CSP ini belum membatasi semua sumber skrip/style karena aplikasi masih mempunyai interaksi inline/CDN.
- **PASS lokal:** cookie production default secure; preflight memeriksa secure, HttpOnly, SameSite, key/cipher, HTTPS URL dan locale. `.env` pengguna tidak diubah.
- Log terminal job GitHub menyimpan tipe exception, bukan pesan exception yang dapat berisi data upstream. Jangan memakai level debug pada production.
- PWA tidak menyimpan navigasi autentikasi/API; pembersihan cache hanya menyasar cache `devflow-static-*`.

## Batas dan risiko operasional

**BLOCKED:** TLS/proxy nyata, permission OS/database least privilege, private key GitHub, rotasi secret, pencabutan izin repository dan instalasi, monitoring akses, serta integrasi provider nyata. Token OAuth tidak dipersistenkan; evaluasi pencabutan akses GitHub pada staging sebelum mengaktifkan App untuk pengguna nyata.

**NOT TESTED:** penetration testing independen, pemeriksaan WCAG menyeluruh, serangan beban/DDoS dan semua kombinasi reverse proxy. Jalankan Nginx hanya dari document root `public`, jangan publikasikan root repository, `.env`, backup, log, atau private key. Backup lokal skrip belum terenkripsi: enkripsi dan unggah ke penyimpanan terpisah diwajibkan sebelum production.

Temuan P0 yang teridentifikasi sudah diperbaiki dan diregresikan secara lokal. Keputusan release tetap NO-GO sampai verifikasi eksternal selesai. Detail lokasi/akar penyebab: [audit](LAUNCH-AUDIT.md).


## Tambahan kolaborasi dan konversi dokumen — 22 Oktober 2026

- Akses proyek mencakup pemilik dan anggota ruang kerja yang diterima; daftar proyek, dashboard, Fokus, detail tugas, draf impor, dan endpoint baru memakai batas ruang kerja yang sama. Anggota adalah editor konten, bukan viewer; keanggotaan memberi akses seluruh proyek dalam ruang kerja tersebut.
- Hanya pemilik mengundang/mencabut anggota lain dan undangan. Undangan mengikat email akun, berumur tujuh hari, token acak 64 karakter disimpan sebagai hash SHA-256, dan penerimaan menggunakan POST ber-CSRF. Undangan lama yang sudah terpakai tidak dapat memulihkan akses anggota yang dikeluarkan.
- Tidak ada pengiriman email otomatis. Tautan undangan perlu dibagikan langsung oleh pemilik; tautan polos ditampilkan sekali melalui flash session, bukan disimpan pada kolom database atau log aplikasi.
- Penanggung jawab dibatasi ke peserta ruang kerja. Pengubahan tugas dan pencabutan anggota menggunakan kunci ruang kerja agar penugasan tidak tertinggal setelah anggota dikeluarkan.
- Komentar di-escape sebagai teks dan dapat dihapus oleh penulis atau pemilik. Resource komentar, versi dokumen dan draf impor selalu dibatasi ke induk proyek/tugasnya.
- Unggahan dibatasi TXT/Markdown, tipe konten teks, 200 KB, UTF-8 dan tanpa karakter biner kontrol. Berkas tidak diterbitkan atau disimpan di folder publik; isinya menjadi versi dokumen privat proyek.
- Konversi berjalan lokal, tanpa HTTP/AI eksternal. Isi sumber dan hasil draf tetap di-escape; konfirmasi dibatasi ke indeks sumber yang sah, validasi pilihan/tenggat/penanggung jawab, transaksi atomik dan constraint idempotensi.
- Pengujian fitur mencakup IDOR, tampering sumber/indeks/penanggung jawab, XSS, rollback seluruh batch, undangan salah akun/kedaluwarsa/dibatalkan/terpakai, serta pencabutan akses. Ini tidak menggantikan penetration test atau verifikasi infrastruktur staging.
