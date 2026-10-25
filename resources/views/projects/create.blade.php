@extends('layouts.app')
@section('title', 'Proyek baru')
@section('page-heading', 'Proyek baru')
@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('projects.index') }}" class="mb-5 inline-flex min-h-11 items-center gap-2 text-sm text-zinc-500 hover:text-blue-700"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Semua proyek</a>
    <x-ui.page-heading title="Mulai proyek baru" description="Beri nama, lalu langsung tulis to-do atau unggah rencana .md." />
    @if($workspaces->isEmpty())
        <x-ui.panel><p class="text-sm leading-6 text-zinc-600">Buat ruang kerja terlebih dahulu untuk menyimpan proyekmu.</p><x-ui.button :href="route('workspaces.create')" class="mt-5">Ruang kerja baru</x-ui.button></x-ui.panel>
    @else
        <x-ui.panel>
            <form action="{{ route('projects.store') }}" method="POST" class="space-y-5" data-busy-form>@csrf
                <x-ui.field name="name" label="Nama proyek" required maxlength="150" placeholder="Contoh: Website perusahaan" autofocus />
                <x-ui.select name="workspace_id" id="project-workspace" label="Ruang kerja" :value="$selectedWorkspace" :options="$workspaces->mapWithKeys(fn ($workspace) => [$workspace->id => $workspace->name.($workspace->user_id !== auth()->id() ? ' · '.$workspace->user->name : '')])->all()" required />
                <p class="text-xs text-zinc-500">Butuh tempat terpisah untuk tim lain? <a href="{{ route('workspaces.create') }}" class="text-blue-700 hover:underline">Buat ruang kerja</a></p>
                <x-ui.textarea name="description" label="Deskripsi" rows="3" maxlength="2000" placeholder="Apa yang ingin diselesaikan?" />
                <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-5"><x-ui.button data-busy-label="Membuat…">Buat proyek</x-ui.button><x-ui.button :href="route('projects.index')" variant="secondary">Batal</x-ui.button></div>
            </form>
        </x-ui.panel>
    @endif
</div>
@endsection
