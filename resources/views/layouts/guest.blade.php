<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · DevFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        referrerpolicy="no-referrer">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <meta name="theme-color" content="#111111">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-5 py-12 sm:px-6">
        <a href="{{ route('login') }}" class="mb-10 flex items-center gap-2.5 text-lg font-bold tracking-tight">
            <span
                class="flex size-10 items-center justify-center rounded-lg bg-[#191919] text-sm font-bold text-white">D<span
                    class="text-[#a1a1aa]">F</span></span>
            DevFlow
        </a>
        @yield('content')
        <p class="mt-8 text-center text-xs text-[#71717A]">Kerja lebih terarah, tanpa ribet.</p>
    </main>
    @stack('scripts')

</body>

</html>
