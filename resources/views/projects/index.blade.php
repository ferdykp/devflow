@extends('layouts.app')
@section('title', 'Proyek')
@section('page-heading', 'Proyek')
@section('content')
<x-ui.page-heading title="Proyek" description="To-do harian dan rencana tim, dalam satu tempat.">
    <x-ui.button :href="route('projects.create')"><i class="fa-solid fa-plus" aria-hidden="true"></i>Proyek baru</x-ui.button>
</x-ui.page-heading>
<div class="mb-6 flex flex-wrap items-center gap-2" aria-label="Daftar ruang kerja">
    @foreach($workspaces as $workspace)
        <a href="{{ route('workspaces.members.show', $workspace) }}" class="inline-flex min-h-11 max-w-full items-center gap-2 rounded-lg bg-zinc-100 px-3 text-sm text-zinc-600 transition-colors hover:bg-blue-50 hover:text-blue-700"><i class="fa-solid fa-users shrink-0 text-xs" aria-hidden="true"></i><span class="truncate">{{ $workspace->name }}@if($workspace->user_id !== auth()->id()) · {{ $workspace->user->name }} @endif</span></a>
    @endforeach
    <a href="{{ route('workspaces.create') }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm text-zinc-500 hover:bg-zinc-50 hover:text-blue-700"><i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>Ruang kerja baru</a>
</div>
<form method="GET" class="mb-5 flex max-w-xl items-center gap-2">
    <label for="project-search" class="sr-only">Cari proyek</label>
    <input id="project-search" name="search" value="{{ request('search') }}" placeholder="Cari proyek…" class="min-w-0 flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-base outline-none focus:border-blue-600 sm:text-sm">
    <x-ui.button variant="secondary">Cari</x-ui.button>
</form>
<div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
    @forelse($projects as $project)
        <article class="min-w-0 rounded-xl border border-zinc-200 bg-white p-5 transition-colors hover:border-zinc-300">
            <div class="mb-5 flex items-center justify-between gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-regular fa-folder" aria-hidden="true"></i></span>
                <div class="flex items-center gap-2"><x-ui.status :value="$project->status" /><a href="{{ route('projects.edit', $project) }}" aria-label="Edit proyek {{ $project->name }}" title="Edit proyek" class="grid size-11 shrink-0 place-items-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900"><i class="fa-solid fa-pen" aria-hidden="true"></i></a></div>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="group block"><h2 class="break-words font-semibold group-hover:text-blue-700">{{ $project->name }}</h2><p class="mt-2 break-words text-xs text-zinc-500">{{ $project->workspace->name }} · {{ $project->tasks_count }} tugas</p><p class="mt-5 inline-flex min-h-11 items-center gap-2 text-sm font-medium text-blue-700">Buka tugas <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></p></a>
        </article>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-200 px-5 py-12 text-center md:col-span-2 2xl:col-span-3"><h2 class="font-semibold">{{ request()->filled('search') ? 'Proyek tidak ditemukan' : 'Mulai dari satu proyek' }}</h2><p class="mt-2 text-sm leading-6 text-zinc-500">{{ request()->filled('search') ? 'Coba kata kunci lain.' : 'Buat proyek, tulis to-do, atau susun tugas otomatis dari dokumen .md.' }}</p></div>
    @endforelse
</div>
<div class="mt-5">{{ $projects->links() }}</div>
@endsection
