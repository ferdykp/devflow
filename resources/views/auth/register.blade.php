@extends('layouts.guest')
@section('title', 'Buat akun')
@section('content')
<div class="w-full max-w-[440px] rounded-xl border border-zinc-200 bg-white px-6 py-8 shadow-xs sm:px-9 sm:py-10">
    <h1 class="text-[25px] font-semibold tracking-[-.04em]">Buat akun baru</h1>
    <p class="mt-2 text-sm text-[#71717A]">Mulai kelola proyekmu di sini.</p>
    <form action="{{ route('register') }}" method="POST" class="mt-8 space-y-5">@csrf
        <div>
            <label for="name" class="mb-2 block text-sm font-medium">Nama lengkap</label>
            <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" placeholder="Nama lengkapmu" class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">
            @error('name')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="mb-2 block text-sm font-medium">Alamat email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="nama@contoh.com" class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">
            @error('email')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="mb-2 block text-sm font-medium">Kata sandi</label>
            <div class="flex items-center gap-2">
                <input id="password" name="password" type="password" data-password-input autocomplete="new-password" minlength="8" required placeholder="Minimal 8 karakter" class="min-w-0 flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">
                <button type="button" data-toggle-password aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" class="flex size-11 shrink-0 items-center justify-center rounded-[10px] text-zinc-500 transition-colors hover:text-zinc-950 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-zinc-800">
                    <i data-password-icon class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium">Ulangi kata sandi</label>
            <div class="flex items-center gap-2">
                <input id="password_confirmation" name="password_confirmation" type="password" data-password-input autocomplete="new-password" minlength="8" required placeholder="Ulangi kata sandi" class="min-w-0 flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">
                <button type="button" data-toggle-password aria-controls="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" class="flex size-11 shrink-0 items-center justify-center rounded-[10px] text-zinc-500 transition-colors hover:text-zinc-950 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-zinc-800">
                    <i data-password-icon class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <p data-password-match role="status" aria-live="polite" class="mt-1.5 text-xs text-zinc-500">Isi kedua kata sandi untuk memastikan kecocokannya.</p>
        </div>
        <x-ui.button class="w-full">Buat akun <span aria-hidden="true">→</span></x-ui.button>
    </form>
    <p class="mt-7 border-t border-[#E4E4E7] pt-6 text-center text-sm text-[#71717A]">Sudah punya akun? <a class="font-semibold text-[#111111] underline-offset-4 hover:underline" href="{{ route('login') }}">Masuk</a></p>
</div>
@endsection
