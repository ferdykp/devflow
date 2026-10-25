@extends('layouts.app')
@section('title', 'Ruang kerja baru')
@section('page-heading', 'Ruang kerja baru')
@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('projects.index') }}" class="mb-5 inline-flex min-h-11 items-center gap-2 text-sm text-zinc-500 hover:text-blue-700"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Semua proyek</a>
    <x-ui.page-heading title="Buat ruang kerja" description="Satu tempat untuk proyek pribadi atau tim kecilmu." />
    <x-ui.panel>
        <form action="{{ route('workspaces.store') }}" method="POST" class="space-y-5" data-busy-form>@csrf
            <x-ui.field name="name" id="workspace_name" label="Nama ruang kerja" required maxlength="100" placeholder="Contoh: Tim desain" autofocus />
            <p class="text-sm leading-6 text-zinc-500">Setelah ini, buat proyek pertamamu. Kamu bisa mengundang anggota dari halaman ruang kerja kapan saja.</p>
            <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-5"><x-ui.button data-busy-label="Membuat…">Buat ruang kerja</x-ui.button><x-ui.button :href="route('projects.index')" variant="secondary">Batal</x-ui.button></div>
        </form>
    </x-ui.panel>
</div>
@endsection
