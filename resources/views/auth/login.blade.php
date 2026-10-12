@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <section class="flex w-screen h-screen items-center justify-center">
        <div class="w-full max-w-md shadow-2xl rounded-xl p-8 bg-white border border-gray-100">
            <div class="space-y-1 mb-6">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Sign in to Devflow
                </h1>
                <p class="text-sm text-[#52525B]">Continuous context & engineering workspace</p>
            </div>

            <button type="button"
                class="flex items-center justify-center space-x-3 w-full h-11 rounded-lg bg-black hover:bg-gray-800 transition-colors mb-6 shadow-sm">
                <span class="text-white text-lg">
                    <i class="fa-brands fa-github"></i>
                </span>
                <span class="text-white font-medium text-sm">
                    Continue with GitHub
                </span>
            </button>

            <div class="relative flex py-2 items-center mb-4">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink mx-4 text-xs text-gray-400 uppercase">Or continue with email</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div class="space-y-1">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                        placeholder="name@example.com">
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a class="text-sm font-medium text-gray-700 hover:underline" href="">Forgot Password</a>
                    </div>
                    <!-- Wrapper relative untuk memposisikan tombol toggle -->
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full px-3.5 py-2 pr-10 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i id="eyeIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full h-10 rounded-lg bg-black text-white font-medium hover:bg-gray-800 transition-colors shadow-sm mt-2">
                    Sign in
                </button>
                <div class="flex items-center justify-center pt-2">
                    <a href="{{ route('register') }}"
                        class="w-full flex items-center justify-center h-10 rounded-lg bg-[#F7F7F8] text-black font-medium hover:bg-gray-200 transition-colors shadow-sm mt-2">
                        Create account
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- Script untuk Toggle Show/Hide Password -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            // Ubah tipe input dari password ke text atau sebaliknya
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Ubah ikon FontAwesome
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
@endsection
