<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · DevFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>try{const theme=localStorage.getItem('devflow.theme');document.documentElement.dataset.theme=theme==='light'?'light':'dark'}catch{document.documentElement.dataset.theme='dark'}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        referrerpolicy="no-referrer">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/devflow-mark.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="alternate icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <meta name="theme-color" content="#1b181c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
    <div id="df-navigation-progress" class="df-navigation-progress" aria-hidden="true"></div>
    <button type="button" data-theme-toggle aria-label="Aktifkan tema terang" aria-pressed="true" class="fixed right-4 top-4 z-10 grid size-11 place-items-center rounded-xl border border-hairline bg-surface text-ink shadow-sm transition hover:bg-surface-muted" title="Ubah tema">
        <i class="fa-solid fa-sun" aria-hidden="true" data-theme-icon></i><span class="sr-only" data-theme-label>Tema terang</span>
    </button>
    <main data-page-transition class="flex min-h-screen flex-col items-center justify-center px-5 py-12 sm:px-6">
        <a href="{{ route('login') }}" aria-label="DevFlow" class="mb-10 flex items-center gap-2.5 text-lg font-bold tracking-tight text-zinc-950">
            <img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-10 rounded-xl">
            DevFlow
        </a>
        @yield('content')
        <p class="mt-8 text-center text-xs text-[#71717A]">Kerja lebih terarah, tanpa ribet.</p>
    </main>
    @stack('scripts')

</body>

</html>
