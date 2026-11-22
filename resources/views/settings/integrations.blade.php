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
                <p class="mt-1 max-w-3xl text-sm leading-6 text-zinc-500">Git hook lokal menganalisis perubahan yang sudah di-stage, lalu mengisi draf pesan commit untuk kamu tinjau di editor Git.</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs {{ $aiConfigured ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $aiConfigured ? 'AI siap' : 'AI belum aktif' }}</span>
        </div>
        @if(! $aiConfigured)
            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm leading-6 text-amber-900">Asisten commit memerlukan <code>DEVFLOW_AI_ENABLED=true</code> dan <code>DEVFLOW_AI_API_KEY</code> pada konfigurasi server. Pengelola server perlu mengaktifkannya sebelum hook dapat menghasilkan saran.</p>
        @endif
        @if(session('generatedCommitHookToken'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-900">Salin token ini sekarang</p>
                <p class="mt-1 text-sm text-emerald-800">Token lengkap hanya ditampilkan sekali. Jika hilang, cabut token lalu buat token baru.</p>
                <code class="mt-3 block select-all break-all rounded-lg bg-white p-3 text-sm text-zinc-900">{{ session('generatedCommitHookToken') }}</code>
            </div>
        @endif
        @if($commitHookToken)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold">Token hook aktif <code class="break-all text-xs font-normal text-zinc-500">{{ $commitHookToken->token_prefix }}…</code></p>
                    <p class="mt-1 text-xs text-zinc-500">Dibuat {{ $commitHookToken->created_at->diffForHumans() }} · Terakhir dipakai {{ $commitHookToken->last_used_at?->diffForHumans() ?? 'Belum pernah' }}</p>
                </div>
                <form method="POST" action="{{ route('settings.commit-hook-token.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Cabut token</x-ui.button>
                </form>
            </div>
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
            <pre class="mt-3 overflow-x-auto rounded-xl bg-zinc-950 p-4 text-xs leading-6 text-zinc-100"><code>git config --local devflow.url '{{ $devflowBaseUrl }}'
printf 'Tempel token DevFlow lalu tekan Enter: '
stty -echo
IFS= read -r DEVFLOW_TOKEN
stty echo
printf '\n'
git config --local devflow.token "$DEVFLOW_TOKEN"
unset DEVFLOW_TOKEN
git config --local devflow.language en
HOOK_PATH="$(git rev-parse --git-path hooks/prepare-commit-msg)"
curl -fsSL '{{ asset('devflow-prepare-commit-msg') }}' -o "$HOOK_PATH"
chmod +x "$HOOK_PATH"</code></pre>
            <p class="mt-3 text-sm leading-6 text-zinc-600">Setelah itu gunakan <code>git add .</code> lalu <code>git commit</code> tanpa <code>-m</code>. Editor akan terbuka dengan saran yang bisa kamu edit. Lanjutkan dengan <code>git push origin main</code>. Jika memakai <code>-m</code>, Git memakai pesan yang kamu tulis dan hook dilewati. Gunakan <code>git config --local devflow.language id</code> untuk saran berbahasa Indonesia.</p>
        </div>
    </x-ui.panel>
</div>
<x-ui.panel class="mt-5"><h2 class="font-semibold">Privasi koneksi</h2><p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-600">Token commit hook hanya ditampilkan satu kali saat dibuat dan disimpan sebagai hash di DevFlow. Status koneksi repositori GitHub dapat diperiksa dan dilepas dari tab GitHub pada proyek terkait.</p></x-ui.panel>
@endsection
