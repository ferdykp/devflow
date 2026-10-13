@extends('layouts.guest')
@section('title', 'Buat akun')
@section('content')
<div class="w-full max-w-[480px] rounded-[16px] border border-[#E4E4E7] bg-white px-6 py-8 shadow-[0_8px_30px_rgba(0,0,0,.025)] sm:px-9 sm:py-10">
    <h1 class="text-[25px] font-semibold tracking-[-.04em]">Buat akun baru</h1>
    <p class="mt-2 text-sm text-[#71717A]">Mulai kelola proyekmu di sini.</p>
    <form action="{{ route('register') }}" method="POST" class="mt-8 space-y-5">@csrf
        <div>
            <label for="name" class="mb-2 block text-sm font-medium">Nama lengkap</label>
            <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" placeholder="Nama lengkapmu" class="w-full rounded-[10px] border border-[#D4D4D8] px-3.5 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10">
            @error('name')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="mb-2 block text-sm font-medium">Alamat email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full rounded-[10px] border border-[#D4D4D8] px-3.5 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10">
            @error('email')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="mb-2 block text-sm font-medium">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" type="password" data-password-input autocomplete="new-password" minlength="8" required placeholder="Minimal 8 karakter" class="w-full rounded-[10px] border border-[#D4D4D8] pl-3.5 pr-12 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10">
                <button type="button" data-toggle-password aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-[10px] text-zinc-500 transition-colors hover:text-zinc-950 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-zinc-800">
                    <i data-eye-open class="fa-regular fa-eye" aria-hidden="true"></i>
                    <i data-eye-closed class="fa-regular fa-eye-slash hidden" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')<p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium">Ulangi kata sandi</label>
            <div class="relative">
                <input id="password_confirmation" name="password_confirmation" type="password" data-password-input autocomplete="new-password" minlength="8" required placeholder="Ulangi kata sandi" class="w-full rounded-[10px] border border-[#D4D4D8] pl-3.5 pr-12 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10">
                <button type="button" data-toggle-password aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-[10px] text-zinc-500 transition-colors hover:text-zinc-950 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-zinc-800">
                    <i data-eye-open class="fa-regular fa-eye" aria-hidden="true"></i>
                    <i data-eye-closed class="fa-regular fa-eye-slash hidden" aria-hidden="true"></i>
                </button>
            </div>
            <p data-password-match role="status" aria-live="polite" class="mt-1.5 text-xs text-zinc-500">Isi kedua kata sandi untuk memastikan kecocokannya.</p>
        </div>
        <button class="w-full rounded-[10px] bg-[#111111] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#303030] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">Buat akun <span aria-hidden="true">→</span></button>
    </form>
    <p class="mt-7 border-t border-[#E4E4E7] pt-6 text-center text-sm text-[#71717A]">Sudah punya akun? <a class="font-semibold text-[#111111] underline-offset-4 hover:underline" href="{{ route('login') }}">Masuk</a></p>
</div>
@endsection
