@extends('layouts.guest')
@section('title', 'Verifikasi email')
@section('content')
    <div class="w-full max-w-[440px] rounded-xl border border-zinc-200 bg-white px-6 py-8 shadow-xs sm:px-9 sm:py-10">
        <div class="mb-6 grid size-12 place-items-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true">
            <i class="fa-regular fa-envelope text-xl"></i>
        </div>
        <h1 class="text-[25px] font-semibold tracking-[-.04em]">Periksa email Anda</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-600">
            Kami mengirim kode 6 digit ke <span class="font-semibold text-zinc-900">{{ $user->email }}</span>.
            Masukkan kode untuk menyelesaikan pendaftaran. Kode berlaku 10 menit.
        </p>

        @if (session('status'))
            <p role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @error('email')
            <p role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <form action="{{ route('verification.verify') }}" method="POST" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="code" class="mb-2 block text-sm font-medium">Kode verifikasi</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" minlength="6" required autofocus
                    value="{{ old('code') }}" placeholder="000000"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-center font-mono text-2xl tracking-[.45em] text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] placeholder:text-zinc-300 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 @error('code') border-red-500 @enderror">
                @error('code')
                    <p role="alert" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-ui.button class="w-full">Verifikasi email <span aria-hidden="true">→</span></x-ui.button>
        </form>

        <form action="{{ route('verification.send') }}" method="POST" class="mt-5 text-center">
            @csrf
            <p class="text-sm text-zinc-500">Belum menerima email?</p>
            <button type="submit" class="mt-1 text-sm font-semibold text-blue-700 underline-offset-4 hover:underline">Kirim kode baru</button>
        </form>

        <form action="{{ route('verification.restart') }}" method="POST" class="mt-7 border-t border-zinc-200 pt-6 text-center">
            @csrf
            <p class="text-sm text-zinc-500">Salah alamat email?</p>
            <button type="submit" class="mt-1 text-sm font-semibold text-blue-700 underline-offset-4 hover:underline">Batalkan dan gunakan email lain</button>
        </form>
    </div>
@endsection
