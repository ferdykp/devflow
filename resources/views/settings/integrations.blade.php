@extends('layouts.app')
@section('title', 'Integrasi')
@section('page-heading', 'Integrasi')
@section('content')
<x-ui.page-heading title="Layanan yang terhubung." description="Lihat kesiapan integrasi untuk workspace dan fitur berbantuan AI. Rahasia koneksi dikelola lewat konfigurasi server."><a href="{{ route('settings.index') }}" class="text-sm font-medium text-blue-700">Pengaturan</a></x-ui.page-heading>
<div class="space-y-5">
    <x-ui.panel>
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-zinc-900 text-white"><i class="fa-brands fa-github" aria-hidden="true"></i></span>
                <div><h2 class="font-semibold">GitHub</h2><p class="mt-1 text-sm text-zinc-500">Repositori dan status sinkronisasi dikelola per proyek.</p></div>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs {{ $githubConfigured ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $githubConfigured ? 'App terkonfigurasi' : 'Belum dikonfigurasi' }}</span>
        </div>
        <p class="mt-4 text-sm leading-6 text-zinc-600">@if($githubConfigured)GitHub App tersedia di server. Buka proyek untuk menyambungkan repositori yang kamu gunakan.@elseGitHub App belum memiliki konfigurasi kredensial di server. Hubungi administrator untuk menyiapkan GitHub App sebelum menghubungkan repositori.@endif</p>
        <a href="{{ route('projects.index') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-zinc-100 px-3 text-sm font-semibold text-zinc-700">Lihat proyek</a>
    </x-ui.panel>

    <x-ui.panel>
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-violet-50 text-violet-700"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></span>
                <div><h2 class="font-semibold">AI dan Smart Coach</h2><p class="mt-1 text-sm text-zinc-500">Analisis opsional untuk insight proyek.</p></div>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs {{ $aiConfigured ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $aiConfigured ? 'AI aktif' : 'AI tidak tersedia' }}</span>
        </div>
        <p class="mt-4 text-sm leading-6 text-zinc-600">@if($aiConfigured)Kredensial AI server tersedia. Hasil tetap ditampilkan sebagai saran yang bisa ditinjau sebelum tindakan.@elseAI belum aktif. Insight berbasis data lokal yang tersedia tetap bisa dibuka tanpa AI.@endif</p>
        <a href="{{ route('smart-coach.index') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-zinc-100 px-3 text-sm font-semibold text-zinc-700">Buka Smart Coach</a>
    </x-ui.panel>

    <x-ui.panel>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-semibold">Asisten pesan commit</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-zinc-500">Git hook lokal menganalisis perubahan yang sudah di-stage, menampilkan saran di Terminal, lalu menunggu konfirmasi tanpa membuka editor seperti Vim.</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs {{ $aiConfigured ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $aiConfigured ? 'AI siap' : 'AI belum aktif' }}</span>
        </div>
        @if(! $aiConfigured)
            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900">Asisten commit memerlukan <code>DEVFLOW_AI_ENABLED=true</code> dan <code>DEVFLOW_AI_API_KEY</code> pada konfigurasi server. Pengelola server perlu mengaktifkannya sebelum hook dapat menghasilkan saran.</p>
        @endif
        @if(session('generatedCommitHookToken'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-900">Token baru berhasil dibuat</p>
                <p class="mt-1 text-sm text-emerald-800">Salin sekarang atau tampilkan lagi dari bagian Token hook aktif. Jangan bagikan token ini.</p>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <code id="commit-hook-token" class="min-w-0 flex-1 select-all break-all rounded-lg bg-white p-3 text-sm text-zinc-900">{{ session('generatedCommitHookToken') }}</code>
                    <x-ui.button type="button" variant="secondary" data-clipboard-copy data-copy-target="commit-hook-token" data-copy-status="commit-hook-token-status">Salin token</x-ui.button>
                </div>
                <p id="commit-hook-token-status" class="mt-2 text-sm text-emerald-800" role="status" aria-live="polite"></p>
            </div>
        @endif
        @if($commitHookToken)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold">Token hook aktif <code class="break-all text-xs font-normal text-zinc-500">{{ $commitHookToken->token_prefix }}…</code></p>
                    <p class="mt-1 text-xs text-zinc-500">Dibuat {{ $commitHookToken->created_at->diffForHumans() }} · Terakhir dipakai {{ $commitHookToken->last_used_at?->diffForHumans() ?? 'Belum pernah' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($commitHookToken->encrypted_token)
                        <x-ui.button type="button" variant="secondary" data-token-reveal data-token-url="{{ route('settings.commit-hook-token.reveal') }}" data-token-target="commit-hook-active-token" data-token-copy="commit-hook-active-token-copy" data-token-status="commit-hook-active-token-status" aria-expanded="false">Tampilkan token</x-ui.button>
                    @endif
                    <form method="POST" action="{{ route('settings.commit-hook-token.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger">Cabut token</x-ui.button>
                    </form>
                </div>
            </div>
            @if($commitHookToken->encrypted_token)
                <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <code id="commit-hook-active-token" class="min-w-0 flex-1 break-all rounded-lg bg-white p-3 font-mono text-sm text-zinc-900" hidden></code>
                        <x-ui.button type="button" variant="secondary" data-clipboard-copy data-copy-target="commit-hook-active-token" data-copy-status="commit-hook-active-token-status" id="commit-hook-active-token-copy" hidden>Salin token</x-ui.button>
                    </div>
                    <p id="commit-hook-active-token-status" class="mt-2 text-sm text-zinc-600" role="status" aria-live="polite">Token disembunyikan. Tekan “Tampilkan token” untuk melihat atau menyalinnya.</p>
                </div>
            @else
                <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900">Token ini dibuat sebelum fitur tampilkan token tersedia, jadi token lengkapnya tidak bisa dipulihkan. Membuat token baru akan mengganti token ini; repo yang sudah terhubung perlu diperbarui dengan token baru.</p>
            @endif
        @endif
        <form method="POST" action="{{ route('settings.commit-hook-token.store') }}" class="mt-5 space-y-4">
            @csrf
            <label class="flex items-start gap-3 text-sm leading-6 text-zinc-700">
                <input type="checkbox" name="consent" value="1" required class="mt-1 size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                <span>Saya setuju perubahan staged dan nama file dikirim ke server DevFlow serta penyedia AI yang dikonfigurasi untuk membuat saran. DevFlow tidak menyimpan diff tersebut; saya akan memastikan perubahan tidak berisi secret sebelum commit.</span>
            </label>
            @error('consent')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
            <x-ui.button>{{ $commitHookToken ? 'Buat token baru' : 'Aktifkan Git hook' }}</x-ui.button>
        </form>
        <div class="mt-6 border-t border-zinc-200 pt-5">
            <h3 class="text-sm font-semibold">Pasang di repo lokal</h3>
            <p class="mt-1 text-sm leading-6 text-zinc-500">Jalankan dari folder repo yang ingin memakai hook. Perintah berikut meminta token tanpa menampilkannya atau memasukkannya ke riwayat shell. Git menyimpan token sebagai teks biasa di konfigurasi lokal repo; jangan membagikan folder <code>.git</code>.</p>
            @php $devflowBaseUrl = rtrim(url('/'), '/'); @endphp
            <div class="relative mt-3 overflow-hidden rounded-xl bg-zinc-950">
                <button type="button" class="absolute right-3 top-3 z-10 inline-flex size-10 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900 text-zinc-200 shadow-sm transition-colors hover:bg-zinc-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" data-clipboard-copy data-copy-target="commit-hook-install-commands" data-copy-status="commit-hook-install-status" aria-label="Salin perintah pemasangan" title="Salin perintah">
                    <i class="fa-regular fa-copy" aria-hidden="true"></i>
                </button>
                <pre class="overflow-x-auto bg-transparent p-4 pr-16 text-xs leading-6 text-zinc-100"><code id="commit-hook-install-commands">git config --local devflow.url '{{ $devflowBaseUrl }}'
printf 'Tempel token DevFlow lalu tekan Enter: '
stty -echo
IFS= read -r DEVFLOW_TOKEN
stty echo
printf '\n'
git config --local devflow.token "$DEVFLOW_TOKEN"
unset DEVFLOW_TOKEN
git config --local devflow.language en
git config --local core.editor true
HOOK_PATH="$(git rev-parse --git-path hooks/prepare-commit-msg)"
curl -fsSL '{{ asset('devflow-prepare-commit-msg') }}' -o "$HOOK_PATH"
chmod +x "$HOOK_PATH"</code></pre>
            </div>
            <p id="commit-hook-install-status" class="mt-2 text-sm text-zinc-600" role="status" aria-live="polite"></p>
            <p class="mt-3 text-sm leading-6 text-zinc-600">Setelah itu gunakan <code>git add .</code> lalu <code>git commit</code> tanpa <code>-m</code>. Saran tampil di Terminal; tekan Enter untuk pakai, ketik <code>e</code> untuk mengubah, atau <code>n</code> untuk membatalkan. Git tidak membuka Vim. Jika saran tidak tersedia, buat commit manual dengan <code>git commit -m "pesan commit"</code>. Lanjutkan dengan <code>git push origin main</code>. Gunakan <code>git config --local devflow.language id</code> untuk saran berbahasa Indonesia.</p>
        </div>
    </x-ui.panel>
</div>
<x-ui.panel class="mt-5"><h2 class="font-semibold">Privasi koneksi</h2><p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600">Token hook disimpan dalam bentuk hash untuk autentikasi serta terenkripsi agar bisa ditampilkan kembali saat kamu masuk ke akun. Token hanya dikirim ke browser setelah tombol “Tampilkan token” ditekan. Status koneksi repositori GitHub dapat diperiksa dan dilepas dari tab GitHub pada proyek terkait.</p></x-ui.panel>
@endsection
