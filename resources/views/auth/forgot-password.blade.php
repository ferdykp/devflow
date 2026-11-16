@extends('layouts.guest')
@section('title', 'Lupa kata sandi')
@section('content')
    <section class="w-full max-w-[440px] rounded-xl border border-zinc-200 bg-white px-6 py-8 shadow-xs sm:px-9 sm:py-10">
        <h1 class="text-[25px] font-semibold tracking-[-.04em]">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-600">Masukkan email akunmu. Jika terdaftar, kami akan mengirim tautan untuk membuat kata sandi baru.</p>

        @if (session('status'))
            <p role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Alamat email</label>
                <input id="email" name="email" type="email" autocomplete="email" required autofocus value="{{ old('email') }}" placeholder="nama@contoh.com"
                    class="w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 sm:text-sm">
                @error('email')
                    <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button class="min-h-11 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700">Kirim tautan reset</button>
        </form>

        <p class="mt-7 border-t border-zinc-200 pt-6 text-center text-sm text-zinc-500"><a class="font-semibold text-blue-700 underline-offset-4 hover:underline" href="{{ route('login') }}">Kembali ke login</a></p>
    </section>
@endsection
