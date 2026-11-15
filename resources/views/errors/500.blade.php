@extends('layouts.guest')
@section('title', 'Terjadi gangguan')
@section('content')
<section class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm"><p class="font-mono text-sm font-semibold text-amber-700">500 · GANGGUAN SEMENTARA</p><h1 class="mt-3 text-2xl font-semibold tracking-tight">Halaman belum bisa dimuat.</h1><p class="mt-2 text-sm leading-6 text-zinc-600">Coba muat ulang. Jika masalah berulang, kembali ke ikhtisar atau hubungi administrator.</p><div class="mt-6 flex flex-wrap gap-2"><button onclick="location.reload()" class="min-h-11 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white">Coba lagi</button><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-11 items-center rounded-xl border border-zinc-200 px-4 text-sm font-medium text-zinc-700">Kembali</a></div></section>
@endsection
