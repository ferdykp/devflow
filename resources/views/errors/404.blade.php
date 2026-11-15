@extends('layouts.guest')
@section('title', 'Halaman tidak ditemukan')
@section('content')
<section class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm"><p class="font-mono text-sm font-semibold text-blue-700">404 · TIDAK DITEMUKAN</p><h1 class="mt-3 text-2xl font-semibold tracking-tight">Halaman ini tidak tersedia.</h1><p class="mt-2 text-sm leading-6 text-zinc-600">Alamatnya mungkin berubah atau item yang diminta sudah dihapus.</p><div class="mt-6 flex flex-wrap gap-2"><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-11 items-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white">Buka ikhtisar</a><button onclick="location.reload()" class="min-h-11 rounded-xl border border-zinc-200 px-4 text-sm font-medium text-zinc-700">Coba lagi</button></div></section>
@endsection
