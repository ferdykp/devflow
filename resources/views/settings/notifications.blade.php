@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-heading', 'Pusat notifikasi')
@section('content')
<x-ui.page-heading title="Pusat notifikasi." description="Notifikasi penting dari jadwal, proyek, dan aktivitas akun akan muncul di sini."><a href="{{ route('settings.index') }}" class="text-sm font-medium text-blue-700">Atur pengingat</a></x-ui.page-heading>
<x-ui.panel><div class="mx-auto max-w-md py-10 text-center"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-xl text-blue-700"><i class="fa-regular fa-bell-slash" aria-hidden="true"></i></span><h2 class="mt-4 font-semibold">Belum ada notifikasi</h2><p class="mt-1 text-sm leading-6 text-zinc-500">Saat ada pembaruan yang perlu kamu tinjau, notifikasi akan tampil di sini. Atur topik pengingat dari Pengaturan.</p><a href="{{ route('settings.index') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white">Buka pengaturan</a></div></x-ui.panel>
@endsection
