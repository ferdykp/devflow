@extends('layouts.guest')
@section('title', 'Register')

@section('content')
    <section class="flex items-center justify-center w-screen min-h-screen py-10">
        <div class="w-full max-w-2xl px-4">

            <!-- Header -->
            <div class="mb-6 flex flex-col text-center space-y-1 items-center justify-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Create your engineering workspace
                </h1>
                <p class="text-sm text-gray-500">
                    Free for individual engineers, team workspaces include GitHub sync.
                </p>
            </div>

            <!-- GitHub Login Box -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 space-y-3">
                <button type="button"
                    class="flex items-center justify-center space-x-3 w-full h-11 rounded-lg bg-black hover:bg-gray-800 transition-colors shadow-sm">
                    <span class="text-white text-lg">
                        <i class="fa-brands fa-github"></i>
                    </span>
                    <span class="text-white font-medium text-sm">
                        Continue with GitHub
                    </span>
                </button>
                <div class="flex items-start space-x-2 px-1">
                    <i class="fa-solid fa-shield-halved text-gray-400 mt-0.5"></i>
                    <span class="text-xs text-gray-500">Only requests public repo or organization read access. No code
                        modifications.</span>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white p-6 w-full rounded-xl shadow-sm border border-gray-100 mt-5">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Row: Name & Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input
                                class="w-full px-3.5 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent bg-white"
                                type="text" name="name" id="name" value="{{ old('name') }}"
                                placeholder="Alex Turner" required>
                        </div>
                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input
                                class="w-full px-3.5 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent bg-white"
                                type="email" name="email" id="email" value="{{ old('email') }}"
                                placeholder="alex@test.com" required>
                        </div>
                    </div>

                    <!-- Password with Real-time Length Validation -->
                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input
                                class="w-full px-3.5 py-2 pr-10 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent bg-white"
                                type="password" name="password" id="password" placeholder="••••••••" required>
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <!-- Real-time Length Indicator -->
                        <p id="lengthIndicator" class="text-xs text-gray-400 flex items-center gap-1.5 pt-1">
                            <i id="indicatorIcon" class="fa-solid fa-circle-xmark text-gray-300"></i>
                            <span>Minimum 8 characters</span>
                        </p>
                    </div>

                    <!-- Re-type Password with Match Validation -->
                    <div class="space-y-1">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Re-type
                            Password</label>
                        <div class="relative">
                            <input
                                class="w-full px-3.5 py-2 pr-10 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent bg-white"
                                type="password" name="password_confirmation" id="password_confirmation"
                                placeholder="••••••••" required>
                            <button type="button" id="togglePasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i id="eyeIconConfirm" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <!-- Real-time Match Indicator -->
                        <p id="matchIndicator" class="text-xs text-gray-400 hidden items-center gap-1.5 pt-1">
                            <i id="matchIcon" class="fa-solid fa-circle-xmark"></i>
                            <span id="matchText">Passwords do not match</span>
                        </p>
                    </div>

                    <!-- Bottom Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <button type="submit"
                            class="w-full h-10 rounded-lg bg-black text-white font-medium text-sm hover:bg-gray-800 transition-colors shadow-sm">
                            Create workspace & continue
                        </button>
                        <div class="flex items-center justify-center text-sm text-gray-500">
                            <span>Already have an account? <a href="{{ route('login') }}"
                                    class="text-black font-medium hover:underline">Login here</a></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Script for Toggle, Length & Match Validation -->
    <script>
        // Toggle Password Utama
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        // Toggle Password Konfirmasi
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');

        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
            eyeIconConfirm.classList.toggle('fa-eye');
            eyeIconConfirm.classList.toggle('fa-eye-slash');
        });

        // Real-time Password Length Validation
        const lengthIndicator = document.getElementById('lengthIndicator');
        const indicatorIcon = document.getElementById('indicatorIcon');

        passwordInput.addEventListener('input', validatePassword);
        passwordConfirmInput.addEventListener('input', validatePassword);

        function validatePassword() {
            const valLength = passwordInput.value.length;

            // Check Length
            if (valLength >= 8) {
                lengthIndicator.className = "text-xs text-green-600 flex items-center gap-1.5 pt-1";
                indicatorIcon.className = "fa-solid fa-circle-check text-green-600";
            } else if (valLength > 0) {
                lengthIndicator.className = "text-xs text-amber-600 flex items-center gap-1.5 pt-1";
                indicatorIcon.className = "fa-solid fa-circle-exclamation text-amber-600";
            } else {
                lengthIndicator.className = "text-xs text-gray-400 flex items-center gap-1.5 pt-1";
                indicatorIcon.className = "fa-solid fa-circle-xmark text-gray-300";
            }

            // Check Match
            const confirmVal = passwordConfirmInput.value;
            const matchIndicator = document.getElementById('matchIndicator');
            const matchIcon = document.getElementById('matchIcon');
            const matchText = document.getElementById('matchText');

            if (confirmVal.length > 0) {
                matchIndicator.classList.remove('hidden');
                matchIndicator.classList.add('flex');

                if (passwordInput.value === confirmVal) {
                    matchIndicator.className = "text-xs text-green-600 flex items-center gap-1.5 pt-1";
                    matchIcon.className = "fa-solid fa-circle-check text-green-600";
                    matchText.textContent = "Passwords match";
                } else {
                    matchIndicator.className = "text-xs text-red-600 flex items-center gap-1.5 pt-1";
                    matchIcon.className = "fa-solid fa-circle-xmark text-red-600";
                    matchText.textContent = "Passwords do not match";
                }
            } else {
                matchIndicator.classList.add('hidden');
                matchIndicator.classList.remove('flex');
            }
        }
    </script>
@endsection
