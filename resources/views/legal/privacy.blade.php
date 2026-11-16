@extends('layouts.public')

@section('title', 'Kebijakan Privasi')
@section('description', 'Ketahui data yang digunakan DevFlow, alasan penggunaannya, dan pilihan Anda.')

@section('content')
    <article class="mx-auto max-w-[760px]">
        <p class="text-sm font-semibold text-[#8f344f]">DevFlow</p>
        <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Kebijakan Privasi</h1>
        <p class="mt-4 text-sm leading-6 text-subtle">Terakhir diperbarui: 16 November 2026</p>
        <p class="mt-8 text-base leading-7 text-subtle">Kebijakan ini menjelaskan data yang diproses saat Anda menggunakan DevFlow, termasuk saat masuk dengan Google, menghubungkan GitHub, atau meminta analisis proyek dengan AI.</p>

        <div class="mt-10 space-y-9 text-[15px] leading-7 text-ink">
            <section>
                <h2 class="font-display text-xl font-semibold">Data yang kami gunakan</h2>
                <p class="mt-3">Saat Anda membuat akun, DevFlow memproses nama, alamat email, dan kredensial akun. Kata sandi yang dibuat di DevFlow disimpan dalam bentuk hash, bukan sebagai teks yang dapat dibaca.</p>
                <p class="mt-3">Jika Anda mendaftar atau masuk dengan Google, DevFlow meminta izin dasar <span class="font-medium">openid</span>, <span class="font-medium">email</span>, dan <span class="font-medium">profile</span>. Untuk membuat dan mengamankan akun, DevFlow menggunakan nama, alamat email, status verifikasi email, dan ID unik akun Google. DevFlow tidak meminta akses ke isi Gmail, Google Drive, atau Google Calendar.</p>
                <p class="mt-3">Konten yang Anda masukkan ke DevFlow dapat mencakup proyek, tugas, catatan, dokumen, komentar, tujuan, rutinitas, jadwal, preferensi, dan informasi workspace. Jika Anda mengundang anggota lain atau bergabung ke workspace, informasi yang relevan juga diproses untuk menyediakan fitur kolaborasi.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Integrasi GitHub</h2>
                <p class="mt-3">Jika Anda menghubungkan GitHub, DevFlow menggunakan izin baca yang diberikan untuk menampilkan repositori yang Anda pilih serta menyinkronkan metadata repositori dan aktivitas commit. Anda dapat melepas koneksi GitHub dari pengaturan atau proyek terkait. GitHub juga memproses data sesuai kebijakan privasinya sendiri.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Fitur analisis AI</h2>
                <p class="mt-3">DevFlow tidak mengirim konten proyek ke penyedia AI hanya karena Anda membuka aplikasi. Jika fitur AI diaktifkan dan Anda meminta analisis, informasi proyek yang diperlukan—seperti nama dan tujuan proyek, kutipan PRD yang disetujui, batas cakupan, usulan perubahan, tugas terbuka, dan aktivitas terbaru—dapat dikirim ke penyedia AI yang dikonfigurasi untuk DevFlow agar analisis dapat dibuat.</p>
                <p class="mt-3">Data profil Google seperti nama, alamat email, dan ID Google tidak disertakan dalam permintaan analisis tersebut. Hindari memasukkan rahasia, kredensial, atau informasi sensitif ke konten proyek.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Cara data digunakan dan dibagikan</h2>
                <p class="mt-3">Data digunakan untuk membuat dan mengamankan akun, menyediakan fitur DevFlow, mengirim kode verifikasi, mendukung kolaborasi, menjalankan integrasi yang Anda aktifkan, dan menjaga keamanan layanan.</p>
                <p class="mt-3">DevFlow tidak menjual data pribadi. Data hanya dibagikan kepada layanan yang diperlukan untuk menjalankan fitur yang Anda gunakan, seperti Google untuk autentikasi, GitHub untuk koneksi repositori, penyedia email untuk pengiriman pesan, penyedia AI saat Anda meminta analisis, dan penyedia hosting/infrastruktur untuk menjalankan DevFlow.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Penyimpanan dan penghapusan</h2>
                <p class="mt-3">Data akun dan konten disimpan selama diperlukan untuk menyediakan layanan. Untuk meminta salinan atau penghapusan data akun, hubungi <a class="font-medium text-[#651d32] underline underline-offset-4" href="mailto:kpferdy@gmail.com">kpferdy@gmail.com</a> dari alamat email yang terkait dengan akun Anda. Kami mungkin meminta verifikasi identitas sebelum memproses permintaan.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Keamanan dan perubahan</h2>
                <p class="mt-3">DevFlow menerapkan kontrol akses akun dan menggunakan koneksi HTTPS pada situs publik. Tidak ada metode penyimpanan atau pengiriman data yang dapat dijamin aman sepenuhnya.</p>
                <p class="mt-3">Kebijakan ini dapat diperbarui ketika fitur atau cara pemrosesan data berubah. Versi terbaru selalu tersedia di halaman ini.</p>
            </section>

            <section class="border-t border-hairline pt-6">
                <h2 class="font-display text-xl font-semibold">Hubungi kami</h2>
                <p class="mt-3">Untuk pertanyaan privasi atau permintaan terkait data, kirim email ke <a class="font-medium text-[#651d32] underline underline-offset-4" href="mailto:kpferdy@gmail.com">kpferdy@gmail.com</a>.</p>
            </section>
        </div>
    </article>
@endsection
