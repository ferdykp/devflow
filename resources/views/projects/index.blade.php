@extends('layouts.app')
@section('title', 'Proyek')
@section('page-heading', 'Proyek')
@section('content')
<header class="df-panel mb-5 flex flex-wrap items-end justify-between gap-4 rounded-2xl border p-5 sm:p-6">
    <div><p class="mb-1 text-[11px] font-bold uppercase tracking-[.14em] text-[var(--df-secondary)]">Proyek &amp; ruang kerja</p><h1 class="df-page-title">Proyek</h1><p class="df-page-description mt-2">Rencana, dokumen, dan pekerjaan dari ruang pribadi maupun tim.</p></div>
    <div class="flex flex-wrap items-center gap-2"><a href="{{ route('workspaces.create') }}" class="df-button-secondary inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-solid fa-users" aria-hidden="true"></i>Ruang kerja baru</a><x-ui.button :href="route('projects.create', request()->filled('workspace_id') ? ['workspace' => request('workspace_id')] : [])"><i class="fa-solid fa-plus" aria-hidden="true"></i>Proyek baru</x-ui.button></div>
</header>

<div class="mb-5 flex flex-wrap items-end justify-between gap-4">
    <form method="GET" action="{{ route('projects.index') }}" class="df-panel grid w-full min-w-0 gap-3 rounded-2xl border p-4 sm:grid-cols-[minmax(180px,1fr)_minmax(180px,240px)_auto_auto] sm:items-end sm:p-5">
        <div class="min-w-0"><label for="project-search" class="mb-1.5 block text-xs font-semibold text-[var(--df-muted)]">Cari proyek</label><input id="project-search" name="search" value="{{ request('search') }}" placeholder="Nama proyek" class="df-input w-full rounded-xl border px-3 text-sm"></div>
        <div class="min-w-0"><label for="project-workspace" class="mb-1.5 block text-xs font-semibold text-[var(--df-muted)]">Ruang kerja</label><select id="project-workspace" name="workspace_id" class="df-input w-full rounded-xl border px-3 text-sm"><option value="">Semua ruang kerja</option>@foreach($workspaces as $workspace)<option value="{{ $workspace->id }}" @selected((string) request('workspace_id') === (string) $workspace->id)>{{ $workspace->name }}{{ $workspace->user_id !== auth()->id() ? ' · '.$workspace->user->name : '' }}</option>@endforeach</select></div>
        <x-ui.button class="w-full sm:w-auto"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>Filter</x-ui.button>
        @if(request()->filled('search') || request()->filled('workspace_id'))<a href="{{ route('projects.index') }}" class="inline-flex min-h-11 items-center justify-center px-3 text-sm font-medium text-[var(--df-muted)] hover:text-[var(--df-action)]">Reset</a>@endif
    </form>
</div>

<section class="df-panel mb-5 rounded-2xl border p-4 sm:p-5" aria-label="Kelola akses ruang kerja"><div class="mb-2 flex items-baseline justify-between gap-2"><h2 class="text-sm font-semibold">Ruang kerja &amp; anggota</h2><span class="text-xs text-[var(--df-muted)]">Kelola undangan dan akses tim</span></div><div class="flex flex-wrap gap-2">@foreach($workspaces as $workspace)<a href="{{ route('workspaces.members.show', $workspace) }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[var(--df-edge)] bg-[var(--df-paper)] px-3 text-sm font-medium hover:border-[var(--df-secondary)] hover:bg-[var(--df-action-soft)]"><i class="fa-solid fa-users text-xs text-[var(--df-action)]" aria-hidden="true"></i>{{ $workspace->name }}</a>@endforeach</div></section>

<div class="mb-2 flex items-center justify-between gap-3 text-xs text-[var(--df-muted)]"><p>{{ $projects->total() }} proyek{{ request()->filled('workspace_id') ? ' di ruang kerja terpilih' : '' }}</p><a href="{{ route('workspaces.create') }}" class="inline-flex min-h-10 items-center gap-2 font-semibold text-[var(--df-action)]"><i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah ruang kerja</a></div>
<section class="df-panel divide-y divide-[var(--df-edge)] rounded-2xl border px-4 sm:px-5" aria-label="Daftar proyek">
    @forelse($projects as $project)
        <article class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-3 py-4 sm:flex-nowrap">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[var(--df-action-soft)] text-[var(--df-action)]"><i class="fa-regular fa-folder" aria-hidden="true"></i></span>
            <div class="min-w-0 flex-1"><div class="flex min-w-0 flex-wrap items-center gap-2"><a href="{{ route('projects.show', $project) }}" class="max-w-full truncate font-semibold hover:text-[var(--df-action)]">{{ $project->name }}</a><x-ui.status :value="$project->status" /></div><p class="mt-1 truncate text-xs text-[var(--df-muted)]"><span>{{ $project->workspace->name }}</span><span class="px-1">·</span><span>{{ $project->tasks_count }} tugas</span></p></div>
            <a href="{{ route('projects.show', ['project' => $project, 'section' => 'tasks']) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-[var(--df-action)] hover:bg-[var(--df-action-soft)]">Buka <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
            <a href="{{ route('projects.edit', $project) }}" aria-label="Edit proyek {{ $project->name }}" class="grid size-10 shrink-0 place-items-center rounded-lg text-[var(--df-muted)] hover:bg-[var(--df-canvas)] hover:text-[var(--df-ink)]"><i class="fa-solid fa-pen text-xs" aria-hidden="true"></i></a>
        </article>
    @empty
        <div class="py-12 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-[var(--df-action-soft)] text-[var(--df-action)]"><i class="fa-regular fa-folder-open" aria-hidden="true"></i></span><h2 class="mt-4 font-display text-lg font-semibold">{{ request()->filled('search') || request()->filled('workspace_id') ? 'Tidak ada proyek yang cocok' : 'Mulai dengan satu proyek' }}</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[var(--df-muted)]">{{ request()->filled('search') || request()->filled('workspace_id') ? 'Ubah kata kunci atau pilih ruang kerja lain.' : 'Buat ruang untuk menyusun rencana, tugas, dokumen, dan kolaborasi tim.' }}</p>@unless(request()->filled('search') || request()->filled('workspace_id'))<a href="{{ route('projects.create') }}" class="df-button-primary mt-4 inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-solid fa-plus" aria-hidden="true"></i>Buat proyek pertama</a>@endunless</div>
    @endforelse
</section>
<div class="mt-5">{{ $projects->links() }}</div>
@endsection
