@extends('layouts.guest')
@section('title', 'Masuk')
@section('content')
    <div
        class="w-full max-w-[440px] rounded-[16px] border border-[#E4E4E7] bg-white px-6 py-8 shadow-[0_8px_30px_rgba(0,0,0,.025)] sm:px-9 sm:py-10">
        <h1 class="text-[25px] font-semibold tracking-[-.04em]">Selamat datang kembali</h1>
        <p class="mt-2 text-sm text-[#71717A]">Masuk untuk melanjutkan pekerjaan di DevFlow.</p>
        <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-5">@csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Alamat email</label>
                <input id="email" name="email" type="email" autocomplete="email" autofocus required
                    value="{{ old('email') }}" placeholder="you@example.com"
                    class="w-full rounded-[10px] border border-[#D4D4D8] bg-white px-3.5 py-3 text-sm outline-none transition focus:border-[#111111] focus:ring-2 focus:ring-[#111111]/10 @error('email') border-red-500 @enderror">
                @error('email')
                    <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium">
                    Kata sandi
                </label>

                <div class="relative">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        placeholder="Masukkan kata sandi"
                        class="w-full rounded-[10px] border border-[#D4D4D8] bg-white
                   py-3 pl-3.5 pr-12 text-sm outline-none transition
                   focus:border-[#111111] focus:ring-2 focus:ring-[#111111]/10">

                    <button type="button" id="toggle-password" aria-label="Tampilkan kata sandi" aria-pressed="false"
                        title="Tampilkan kata sandi"
                        class="absolute inset-y-0 right-0 z-10 flex w-11
                   items-center justify-center rounded-r-[10px]
                   text-zinc-500 transition-colors
                   hover:text-zinc-950
                   focus-visible:outline-2
                   focus-visible:outline-offset-[-3px]
                   focus-visible:outline-zinc-800">
                        <i id="password-eye-icon" class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                @error('password')
                    <p role="alert" class="mt-1.5 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>
            <label class="flex items-center gap-2 text-sm text-[#52525B]"><input name="remember" value="1"
                    type="checkbox" class="rounded border-[#D4D4D8] accent-black"> Ingat saya</label>
            <button
                class="w-full rounded-[10px] bg-[#111111] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#303030] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">Masuk
                <span aria-hidden="true">→</span></button>
        </form>
        <p class="mt-7 border-t border-[#E4E4E7] pt-6 text-center text-sm text-[#71717A]">Belum punya akun? <a
                class="font-semibold text-[#111111] underline-offset-4 hover:underline"
                href="{{ route('register') }}">Daftar sekarang</a></p>
    </div>
    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('password');
                const button = document.getElementById('toggle-password');
                const icon = document.getElementById('password-eye-icon');

                if (!input || !button || !icon) return;

                button.addEventListener('click', () => {
                    const showPassword = input.type === 'password';

                    input.type = showPassword ? 'text' : 'password';

                    icon.classList.toggle('fa-eye', !showPassword);
                    icon.classList.toggle('fa-eye-slash', showPassword);

                    const label = showPassword ?
                        'Sembunyikan kata sandi' :
                        'Tampilkan kata sandi';

                    button.setAttribute('aria-label', label);
                    button.setAttribute('aria-pressed', String(showPassword));
                    button.title = label;
                });
            })();
        </script>
    @endpush
@endsection
