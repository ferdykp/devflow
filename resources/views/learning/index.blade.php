@extends('layouts.app')
@section('title', 'Learning Hub')
@section('page-heading', 'Belajar')
@section('content')
<x-ui.page-heading title="Pahami materi, praktikkan, tunjukkan kompetensimu." description="Jalur belajar dapat dipakai untuk Software Engineering, English Development, atau bidang lain. Materi contoh bukan kurikulum terakreditasi.">
    <a href="{{ route('learning.sessions.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-zinc-200 px-3 text-sm">Sesi belajar</a>
    <a href="{{ route('learning.assessments.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-zinc-200 px-3 text-sm">Latihan dan review</a>
    <button type="button" data-create-open="learning-path" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"><i class="fa-solid fa-plus" aria-hidden="true"></i>Jalur baru</button>
</x-ui.page-heading>
<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,.75fr)]">
    <section class="min-w-0 space-y-4">
        @if($reviewQueue->isNotEmpty())
            <x-ui.panel>
                <div class="flex items-center gap-2"><i class="fa-solid fa-rotate text-blue-700" aria-hidden="true"></i><h2 class="font-semibold">Antrean review</h2></div>
                <ul class="mt-3 space-y-2">@foreach($reviewQueue as $lesson)<li class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-blue-50/60 p-3"><div><p class="text-sm font-medium"><a href="{{ route('learning.lessons.show', $lesson) }}" class="hover:text-blue-700">{{ $lesson->title }}</a></p><p class="text-xs text-zinc-500">{{ $lesson->module->path->title }}</p></div><form method="POST" action="{{ route('learning.lessons.update', [$lesson->module->learning_path_id, $lesson]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $lesson->status }}"><input type="hidden" name="next_review_at" value="{{ now()->addDays(7)->format('Y-m-d\\TH:i') }}"><button class="min-h-10 rounded-lg bg-white px-3 text-xs font-semibold text-blue-700">Review selesai · ulang 7 hari</button></form></li>@endforeach</ul>
            </x-ui.panel>
        @endif
        @forelse($paths as $path)
            <x-ui.panel>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $path->subject }}</p><h2 class="mt-1 text-lg font-semibold"><a href="{{ route('learning.paths.show', $path) }}" class="hover:text-blue-700">{{ $path->title }}</a></h2>@if($path->description)<p class="mt-2 whitespace-pre-wrap text-sm text-zinc-600">{{ $path->description }}</p>@endif @if($path->goal)<p class="mt-2 text-xs text-zinc-500">Goal terkait: {{ $path->goal->title }}</p>@endif</div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs text-emerald-700">{{ $path->status === 'active' ? 'Aktif' : ucfirst($path->status) }}</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 rounded-lg bg-zinc-50 p-3 text-sm"><p><span class="block text-xs text-zinc-500">Modul</span><strong>{{ $path->modules->count() }}</strong></p><p><span class="block text-xs text-zinc-500">Sesi</span><strong>{{ $path->sessions->count() }}</strong></p><p><span class="block text-xs text-zinc-500">Waktu belajar</span><strong>{{ $path->sessions->sum('duration_minutes') }} mnt</strong></p></div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-3"><p class="text-xs text-zinc-500">{{ $path->modules->sum(fn ($module) => $module->lessons->count()) }} pelajaran tersusun</p><a href="{{ route('learning.paths.show', $path) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">Buka jalur <i class="fa-solid fa-arrow-right ml-2 text-xs" aria-hidden="true"></i></a></div>
            </x-ui.panel>
        @empty
            <x-ui.panel><h2 class="font-semibold">Belum ada jalur belajar</h2><p class="mt-2 text-sm text-zinc-600">Buat jalur untuk mengatur materi, sesi, latihan, dan progresmu.</p><button type="button" data-create-open="learning-path" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-blue-50 px-3 text-sm font-semibold text-blue-700">Buat jalur belajar</button></x-ui.panel>
        @endforelse
    </section>
    <aside class="space-y-4">
        <x-ui.panel><h2 class="font-semibold">Contoh struktur awal</h2><p class="mt-2 text-xs leading-5 text-zinc-500">Gunakan sebagai titik awal dan edit sesuai kebutuhan. Ini bukan materi resmi atau kurikulum terakreditasi.</p><div class="mt-3 space-y-3"><div class="rounded-lg bg-zinc-50 p-3"><p class="text-sm font-medium">Software Engineering Fundamentals</p><p class="mt-1 text-xs text-zinc-600">Computational thinking · Programming · Problem solving · Data structures & algorithms · Database & SQL · Software engineering practices</p></div><div class="rounded-lg bg-zinc-50 p-3"><p class="text-sm font-medium">English Development</p><p class="mt-1 text-xs text-zinc-600">Vocabulary · Grammar · Listening · Reading · Speaking · Writing · Professional communication · TOEFL/IELTS preparation</p></div></div></x-ui.panel>
        <x-ui.panel><h2 class="font-semibold"><a href="{{ route('learning.sessions.index') }}" class="hover:text-blue-700">Riwayat belajar</a></h2><ul class="mt-3 space-y-2">@forelse($history as $session)<li class="flex justify-between gap-2 text-xs"><span>{{ $session->lesson?->title ?? $session->path->title }} · {{ $session->occurred_on->format('d/m') }}</span><strong>{{ $session->duration_minutes }} mnt</strong></li>@empty<li class="text-xs text-zinc-500">Belum ada sesi belajar tercatat.</li>@endforelse</ul></x-ui.panel>
    </aside>
</div>
<x-ui.create-dialog type="learning-path" title="Buat jalur belajar" description="Atur bidang belajar dan tujuan yang ingin kamu capai." :errors="$errors->any()"><form method="POST" action="{{ route('learning.paths.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-modal-form>@csrf<x-ui.field label="Nama" name="title" required placeholder="Software Engineering Fundamentals" /><x-ui.field label="Bidang" name="subject" required placeholder="Software Engineering / English" /><x-ui.textarea wrapper-class="sm:col-span-2" label="Tujuan atau deskripsi" name="description" rows="3" /><x-ui.select label="Goal terkait" name="personal_goal_id" :options="['' => 'Tanpa goal'] + $goals->mapWithKeys(fn ($goal) => [$goal->id => $goal->title])->all()" /><x-ui.button class="w-full sm:col-span-2">Buat jalur</x-ui.button></form></x-ui.create-dialog>
@endsection
