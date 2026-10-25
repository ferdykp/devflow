@extends('layouts.app')
@section('title', 'Ringkasan')
@section('page-heading', 'Ringkasan')
@section('content')
<x-ui.page-heading title="Ruang untuk menyelesaikan pekerjaan." description="Mulai dari satu to-do, atau susun rencana dari dokumenmu." eyebrow="Ruang kerja">
    <x-ui.button :href="route('projects.create')"><i class="fa-solid fa-plus" aria-hidden="true"></i> Proyek baru</x-ui.button>
</x-ui.page-heading>
<div class="grid items-start gap-8 xl:grid-cols-[minmax(0,1fr)_300px]">
    <section class="min-w-0" aria-labelledby="projects-title">
        <div class="mb-4 flex items-center justify-between gap-3"><h2 id="projects-title" class="text-sm font-semibold">Proyekmu</h2><a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center text-sm text-zinc-500 hover:text-blue-700">Lihat semua →</a></div>
        <div class="grid gap-4 sm:grid-cols-2">
            @forelse($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="group min-w-0 rounded-xl border border-zinc-200 p-5 transition-colors hover:border-blue-300 hover:bg-blue-50/30">
                    <div class="mb-5 flex items-center justify-between gap-2"><span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-regular fa-folder" aria-hidden="true"></i></span><x-ui.status :value="$project->status" /></div>
                    <h3 class="break-words font-semibold group-hover:text-blue-700">{{ $project->name }}</h3>
                    <p class="mt-2 text-sm text-zinc-500">{{ $project->tasks_count }} tugas</p>
                    <p class="mt-5 text-sm font-medium text-blue-700">Buka daftar tugas →</p>
                </a>
            @empty
                <x-ui.panel class="sm:col-span-2"><h3 class="font-semibold">Mulai dengan proyek pertamamu</h3><p class="mt-2 text-sm leading-6 text-zinc-500">Beri nama proyek, lalu langsung tulis to-do. Jika sudah punya PRD, unggah Markdown untuk menyusun tugas otomatis.</p><x-ui.button :href="route('projects.index')" class="mt-5">Buat proyek pertama</x-ui.button></x-ui.panel>
            @endforelse
        </div>
    </section>
    <aside class="space-y-5">
        <x-ui.panel><span class="grid size-10 place-items-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-regular fa-sun" aria-hidden="true"></i></span><h2 class="mt-4 font-semibold">Sedikit tugas, lebih fokus.</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Ada {{ $openTasks }} tugas belum selesai. Pilih maksimal tiga untuk fokus hari ini.</p><x-ui.button :href="route('focus.index')" variant="secondary" class="mt-5 w-full">Buka Fokus Saya</x-ui.button></x-ui.panel>
        <div class="rounded-xl bg-zinc-50 p-5"><h2 class="text-sm font-medium">Sudah punya rencana proyek?</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Buka proyek → Dokumen → unggah berkas .md. DevFlow menyusunnya menjadi tugas berurutan dan checklist.</p></div>
    </aside>
</div>
@endsection
