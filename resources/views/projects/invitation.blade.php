@extends('layouts.app')
@section('title', 'Undangan ruang kerja')
@section('page-heading', 'Undangan ruang kerja')
@section('content')
<div class="mx-auto max-w-xl">
    <x-ui.page-heading title="Bergabung dengan ruang kerja" />
    <x-ui.panel>
        <h2 class="break-words text-xl font-semibold">{{ $invitation->workspace->name }}</h2>
        <p class="mt-3 text-sm leading-6 text-zinc-600">Undangan ini ditujukan kepada <strong class="break-all">{{ $invitation->email }}</strong>. Anggota dapat mengelola semua proyek dan tugas dalam ruang kerja ini.</p>
        @if(strtolower(auth()->user()->email) === $invitation->email)
            <form action="{{ route('invitations.accept', $token) }}" method="POST" class="mt-6">@csrf<x-ui.button>Terima undangan</x-ui.button></form>
        @else
            <p role="alert" class="mt-5 rounded-lg bg-amber-50 p-4 text-sm leading-6 text-amber-900">Kamu sedang masuk sebagai {{ auth()->user()->email }}. Keluar terlebih dahulu, lalu masuk atau daftar memakai email yang diundang dan buka kembali tautan ini.</p>
        @endif
    </x-ui.panel>
</div>
@endsection
