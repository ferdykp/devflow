<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Informasi layanan DevFlow.')">
    <meta name="theme-color" content="#f6f1ea">
    <title>@yield('title') · DevFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/devflow-mark.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="alternate icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <a href="#konten" class="absolute -top-24 left-4 z-50 rounded-lg bg-white px-4 py-3 text-sm font-medium shadow-lg focus:top-3">Lewati ke konten</a>
    <header class="border-b border-hairline bg-surface/90">
        <div class="mx-auto flex w-full max-w-[1100px] items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <a href="{{ route('home') }}" aria-label="DevFlow, halaman utama" class="inline-flex items-center gap-2.5 text-lg font-semibold tracking-tight text-zinc-950">
                <img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-9 rounded-xl">
                <span class="font-display">DevFlow<span class="text-[#8f344f]">.</span></span>
            </a>
            <nav aria-label="Navigasi utama" class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('home') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-medium text-zinc-600 underline-offset-4 hover:text-[#651d32] hover:underline">Beranda</a>
                <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center rounded-lg bg-[#651d32] px-4 text-sm font-semibold text-white hover:bg-[#501528]">Masuk</a>
            </nav>
        </div>
    </header>

    <main id="konten" class="mx-auto w-full max-w-[900px] px-5 py-12 sm:px-8 sm:py-16">
        @yield('content')
    </main>

    <footer class="border-t border-hairline">
        <div class="mx-auto flex w-full max-w-[1100px] flex-col gap-4 px-5 py-6 text-sm text-subtle sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>© {{ date('Y') }} DevFlow</span>
            <nav aria-label="Informasi layanan" class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ route('privacy') }}" class="underline-offset-4 hover:text-[#651d32] hover:underline">Kebijakan privasi</a>
                <a href="{{ route('terms') }}" class="underline-offset-4 hover:text-[#651d32] hover:underline">Syarat layanan</a>
                <a href="mailto:kpferdy@gmail.com" class="underline-offset-4 hover:text-[#651d32] hover:underline">Hubungi DevFlow</a>
            </nav>
        </div>
    </footer>
</body>

</html>
