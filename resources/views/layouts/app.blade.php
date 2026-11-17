<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#651d32">
    <title>@yield('title', 'Ruang kerja') — DevFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>try{const theme=localStorage.getItem('devflow.theme');if(theme==='dark'||theme==='light'){document.documentElement.dataset.theme=theme}if(localStorage.getItem('devflow.sidebar.collapsed')==='true'){document.documentElement.dataset.sidebarCollapsed='true'}}catch{}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        referrerpolicy="no-referrer">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/devflow-mark.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="alternate icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="df-shell min-h-screen bg-canvas font-sans text-ink antialiased">
    <div id="df-navigation-progress" class="df-navigation-progress" aria-hidden="true"></div>
    <a href="#main-content"
        class="absolute -top-24 left-4 z-[100] rounded-lg bg-white px-4 py-3 text-sm font-medium shadow-lg focus:top-3">Lewati
        ke konten</a>
    <div class="df-workspace grid min-h-svh grid-cols-[256px_minmax(0,1fr)] max-[850px]:grid-cols-[minmax(0,1fr)]">
        @include('components.layout.sidebar')
        <div class="df-stage min-w-0 [overflow-wrap:anywhere]">
            @include('components.layout.topbar')
            <main id="main-content" tabindex="-1" data-page-transition
                class="mx-auto w-full max-w-[2200px] px-4 pb-24 pt-6 sm:px-7 min-[851px]:px-8 min-[851px]:pb-12 min-[851px]:pt-7 xl:px-10">
                <div class="fixed right-3 top-20 z-[1000] grid w-[min(390px,calc(100vw-24px))] gap-3 pointer-events-none sm:right-6"
                    role="region" aria-label="Notifikasi" data-toast-stack>
                    @if (session('success'))
                        <x-ui.toast>{{ session('success') }}</x-ui.toast>
                    @endif
                    @if (session('error'))
                        <x-ui.toast kind="error">{{ session('error') }}</x-ui.toast>
                    @endif
                    @if ($errors->any())
                        <x-ui.toast kind="error">
                            <p class="font-medium">Periksa kembali isianmu</p>
                            <p class="mt-1 text-zinc-600">{{ $errors->first() }}</p>
                            @if ($errors->count() > 1)
                                <p class="mt-1 text-xs text-zinc-500">{{ $errors->count() - 1 }} isian lainnya perlu
                                    diperiksa.</p>
                            @endif
                        </x-ui.toast>
                    @endif
                </div>
                @yield('content')
            </main>
        </div>
    </div>
    @include('components.layout.mobile-nav')
    @include('components.layout.command')
    <template data-error-toast-template><x-ui.toast kind="error"><span
                data-error-message></span></x-ui.toast></template>
    @stack('scripts')
</body>

</html>
