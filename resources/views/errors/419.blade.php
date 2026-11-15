@extends('layouts.guest')
@section('title', 'Sesi kedaluwarsa')
@section('content')
<section class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm"><p class="font-mono text-sm font-semibold text-blue-700">419 · SESI BERAKHIR</p><h1 class="mt-3 text-2xl font-semibold tracking-tight">Sesi perlu dimuat ulang.</h1><p class="mt-2 text-sm leading-6 text-zinc-600">Untuk menjaga keamanan, formulir ini tidak dapat dikirim dengan sesi yang lama.</p><button onclick="location.reload()" class="mt-6 min-h-11 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white">Muat ulang halaman</button></section>
@endsection
