@extends('layouts.guest')
@section('title', 'Akses tidak tersedia')
@section('content')
<section class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm"><p class="font-mono text-sm font-semibold text-blue-700">403 · AKSES TERBATAS</p><h1 class="mt-3 text-2xl font-semibold tracking-tight">Kamu tidak punya akses ke halaman ini.</h1><p class="mt-2 text-sm leading-6 text-zinc-600">Minta pemilik workspace mengundangmu atau buka ruang kerja yang memang kamu ikuti.</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-6 inline-flex min-h-11 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white">Kembali ke DevFlow</a></section>
@endsection
