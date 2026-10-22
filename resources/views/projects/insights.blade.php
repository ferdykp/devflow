@extends('layouts.app')
@section('title', 'Analisis AI')
@section('page-heading', 'Analisis AI')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('projects.show', $project) }}" class="text-sm text-[#60615e] underline">← {{ $project->name }}</a>
    <div><h1 class="df-heading [font-size:clamp(1.65rem,2.8vw,2.15rem)] [font-weight:650] [letter-spacing:-.045em] [line-height:1.18] tracking-tight">Analisis AI</h1><p class="mt-1 text-sm text-[#60615e]">Saran ini bersifat opsional. Keputusan cakupan tetap ada di tanganmu.</p></div>
    <div class="rounded-lg border border-[#e4e4e1] bg-white p-5 sm:p-6">
        @if(! $ready)
            <div role="status" class="rounded-lg bg-zinc-100 p-4 text-sm text-zinc-700">Fitur AI belum aktif. Hubungi pengelola untuk mengaktifkannya. Tugas, peninjauan cakupan, dan ringkasan proyek tetap bisa digunakan.</div>
        @else
            <form action="{{ route('projects.insights.analyze', $project) }}" method="POST" class="space-y-4">@csrf
                <div><label for="mode" class="mb-1 block text-sm font-medium">Analisis</label><select id="mode" name="mode" class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none" required><option value="scope" @selected(old('mode') === 'scope')>Saran cakupan</option><option value="progress" @selected(old('mode') === 'progress')>Progres dan langkah berikutnya</option></select></div>
                <div><label for="proposal" class="mb-1 block text-sm font-medium">Usulan perubahan (wajib untuk analisis cakupan)</label><textarea id="proposal" name="proposal" maxlength="3000" rows="3" class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none" placeholder="Jelaskan ide atau perubahan yang ingin ditinjau">{{ old('proposal') }}</textarea>@error('proposal')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <label class="flex items-start gap-3 text-sm text-zinc-700"><input type="checkbox" name="consent" value="1" class="mt-1" required>Kirim ringkasan proyek, sebagian PRD, dan usulanmu ke penyedia AI untuk analisis ini saja.</label>
                @error('consent')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
                @error('ai')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
                <button type="submit" class="rounded-lg bg-[#191919] px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">Analisis</button>
            </form>
        @endif
    </div>
    @if($result)
        <section class="space-y-3 rounded-lg border border-[#e4e4e1] bg-white p-5 sm:p-6" aria-live="polite">
            <div class="flex items-center justify-between gap-3"><h2 class="font-semibold">Saran</h2><span class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs text-zinc-700">{{ ['in_scope' => 'Dalam cakupan', 'out_of_scope' => 'Di luar cakupan', 'uncertain' => 'Belum jelas', 'not_assessable' => 'Belum bisa dinilai'][$result['classification']] ?? $result['classification'] }}</span></div>
            <p class="whitespace-pre-wrap text-sm text-zinc-700">{{ $result['summary'] }}</p>
            @if($result['evidence'])<h3 class="text-sm font-semibold">Bukti dari informasi yang tersedia</h3><ul class="list-disc space-y-1 pl-5 text-sm text-[#60615e]">@foreach($result['evidence'] as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
            <h3 class="text-sm font-semibold">Langkah berikutnya yang disarankan</h3><p class="whitespace-pre-wrap text-sm text-zinc-700">{{ $result['suggested_next_step'] }}</p>
            <p class="border-t border-zinc-100 pt-3 text-xs text-[#747571]">Saran AI perlu diperiksa kembali. PRD, keputusan cakupan, dan tugas tidak diubah. Gunakan alur peninjauan cakupan untuk mengambil keputusan.</p>
        </section>
    @endif
</div>
@endsection
