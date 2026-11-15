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
    <script>try{const theme=localStorage.getItem('devflow.theme');if(theme==='dark'||theme==='light'){document.documentElement.dataset.theme=theme}}catch{}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        referrerpolicy="no-referrer">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <meta name="theme-color" content="#651d32">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
    <div id="df-navigation-progress" class="df-navigation-progress" aria-hidden="true"></div>
    <main data-page-transition class="flex min-h-screen flex-col items-center justify-center px-5 py-12 sm:px-6">
        <a href="{{ route('login') }}" aria-label="DevFlow" class="mb-10 flex items-center gap-2.5 text-lg font-bold tracking-tight text-zinc-950">
            <span aria-hidden="true" class="grid size-10 place-items-center rounded-xl bg-blue-600 text-base text-white"><i class="fa-solid fa-code"></i></span>
            DevFlow
        </a>
        @yield('content')
        <p class="mt-8 text-center text-xs text-[#71717A]">Kerja lebih terarah, tanpa ribet.</p>
    </main>
    @stack('scripts')

</body>

</html>
