@extends('layouts.public')

@section('title', 'Syarat Layanan')
@section('description', 'Syarat penggunaan DevFlow untuk mengelola pekerjaan dan proyek Anda.')

@section('content')
    <article class="mx-auto max-w-[760px]">
        <p class="text-sm font-semibold text-[#8f344f]">DevFlow</p>
        <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Syarat Layanan</h1>
        <p class="mt-4 text-sm leading-6 text-subtle">Terakhir diperbarui: 16 November 2026</p>
        <p class="mt-8 text-base leading-7 text-subtle">Dengan membuat akun atau menggunakan DevFlow, Anda menyetujui syarat berikut. Jika Anda tidak menyetujuinya, jangan gunakan layanan ini.</p>

        <div class="mt-10 space-y-9 text-[15px] leading-7 text-ink">
            <section>
                <h2 class="font-display text-xl font-semibold">Akun dan keamanan</h2>
                <p class="mt-3">Berikan informasi akun yang benar dan jaga kerahasiaan cara masuk Anda. Anda bertanggung jawab atas aktivitas yang dilakukan melalui akun Anda. Hubungi kami jika Anda mengetahui penggunaan akun tanpa izin.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Konten Anda</h2>
                <p class="mt-3">Anda tetap memiliki hak atas konten yang Anda masukkan ke DevFlow. Anda memberi DevFlow izin terbatas untuk menyimpan, memproses, dan menampilkan konten tersebut sejauh diperlukan untuk menyediakan fitur, termasuk workspace bersama, sinkronisasi GitHub yang Anda aktifkan, dan analisis AI yang Anda minta.</p>
                <p class="mt-3">Anda bertanggung jawab memastikan bahwa Anda memiliki hak untuk menyimpan dan membagikan konten yang Anda masukkan, termasuk saat membagikannya dengan anggota workspace.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Penggunaan yang diperbolehkan</h2>
                <p class="mt-3">Jangan menggunakan DevFlow untuk melanggar hukum, mengganggu layanan, mengakses akun atau data orang lain tanpa izin, atau mengirim kode berbahaya. Jangan memasukkan kredensial, kunci rahasia, atau informasi sensitif ke fitur AI.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Integrasi dan saran otomatis</h2>
                <p class="mt-3">Google dan GitHub adalah layanan pihak ketiga yang tunduk pada syarat dan kebijakan mereka sendiri. DevFlow tidak mengendalikan ketersediaan layanan tersebut.</p>
                <p class="mt-3">Hasil analisis AI bersifat saran untuk membantu peninjauan. Periksa hasilnya sebelum mengambil keputusan atau mengubah proyek; DevFlow tidak menerapkan perubahan proyek secara otomatis berdasarkan saran AI.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Ketersediaan layanan</h2>
                <p class="mt-3">Kami berupaya menjaga DevFlow tetap tersedia dan aman, tetapi tidak menjamin layanan selalu bebas gangguan. Fitur dapat berubah, dihentikan sementara, atau diperbarui untuk menjaga layanan dan meningkatkan fungsinya.</p>
            </section>

            <section>
                <h2 class="font-display text-xl font-semibold">Perubahan syarat</h2>
                <p class="mt-3">Kami dapat memperbarui syarat ini ketika layanan berubah. Versi terbaru akan dipublikasikan di halaman ini dan berlaku sejak tanggal pembaruan yang tercantum.</p>
            </section>

            <section class="border-t border-hairline pt-6">
                <h2 class="font-display text-xl font-semibold">Hubungi kami</h2>
                <p class="mt-3">Pertanyaan tentang syarat layanan dapat dikirim ke <a class="font-medium text-[#651d32] underline underline-offset-4" href="mailto:kpferdy@gmail.com">kpferdy@gmail.com</a>.</p>
            </section>
        </div>
    </article>
@endsection
