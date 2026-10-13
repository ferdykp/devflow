@extends('layouts.app')
@section('title', 'Hubungkan GitHub')
@section('page-heading', 'Hubungkan GitHub')
@section('content')
<div class="mx-auto max-w-2xl space-y-5">
    <div><h1 class="df-heading [font-size:clamp(1.65rem,2.8vw,2.15rem)] [font-weight:650] [letter-spacing:-.045em] [line-height:1.18] tracking-tight">Pilih repositori</h1><p class="mt-1 text-sm text-[#60615e]">Hanya repositori yang kamu izinkan untuk DevFlow yang ditampilkan.</p></div>
    <div class="rounded-xl border border-[#eaeae8] bg-white p-5">
        @forelse($repositories as $repository)
        <form method="POST" action="{{ route('github.app.connect', $project) }}" class="flex items-center justify-between gap-4 border-b border-zinc-100 py-3 last:border-0">
            @csrf
            <input type="hidden" name="repository_id" value="{{ $repository['id'] }}">
            <span class="min-w-0 break-all text-sm font-medium">{{ $repository['full_name'] }}</span>
            <button class="shrink-0 rounded-lg bg-[#191919] px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-700">Hubungkan</button>
        </form>
        @empty
        <p class="text-sm text-[#60615e]">Belum ada repositori GitHub App. Pasang DevFlow GitHub App dengan izin baca untuk Contents dan Metadata terlebih dahulu.</p>
        @endforelse
    </div>
    @error('repository_id') <p role="alert" class="text-sm text-red-600">{{ $message }}</p> @enderror
    <a class="text-sm font-medium underline" href="{{ route('projects.show', $project) }}">Kembali ke proyek</a>
</div>
@endsection
