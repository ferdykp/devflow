@extends('layouts.app')
@section('title', 'Undangan tidak tersedia')
@section('page-heading', 'Undangan ruang kerja')
@section('content')
<div class="mx-auto max-w-xl">
    <x-ui.panel>
        <h1 class="text-xl font-semibold">Undangan tidak tersedia</h1>
        <p class="mt-3 text-sm leading-7 text-zinc-600">Tautan undangan sudah kedaluwarsa atau dibatalkan. Minta tautan baru kepada pemilik ruang kerja.</p>
        <x-ui.button :href="route('projects.index')" variant="secondary" class="mt-6">Kembali ke proyek</x-ui.button>
    </x-ui.panel>
</div>
@endsection
