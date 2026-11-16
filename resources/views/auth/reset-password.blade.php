@extends('layouts.guest')
@section('title', 'Atur ulang kata sandi')
@section('content')
    <section class="w-full max-w-[440px] rounded-xl border border-zinc-200 bg-white px-6 py-8 shadow-xs sm:px-9 sm:py-10">
        <h1 class="text-[25px] font-semibold tracking-[-.04em]">Buat kata sandi baru</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-600">Tautan reset berlaku selama 60 menit. Pilih kata sandi minimal 8 karakter.</p>

        <form action="{{ route('password.update') }}" method="POST" class="mt-7 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Alamat email</label>
                <input id="email" name="email" type="email" autocomplete="email" required autofocus value="{{ old('email', $email) }}" placeholder="nama@contoh.com"
                    class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 sm:text-sm">
                @error('email')
                    <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Kata sandi baru</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required placeholder="Minimal 8 karakter"
                    class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 sm:text-sm">
                @error('password')
                    <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium">Ulangi kata sandi baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required placeholder="Ulangi kata sandi baru"
                    class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 sm:text-sm">
            </div>
            <button class="min-h-11 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700">Simpan kata sandi baru</button>
        </form>
    </section>
@endsection
