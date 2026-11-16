<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f1ea">
    <title>DevFlow — Kerja dan berkembang dengan lebih terarah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <a href="#konten" class="absolute -top-24 left-4 z-50 rounded-lg bg-white px-4 py-3 text-sm font-medium shadow-lg focus:top-3">Lewati ke konten</a>
    <header class="mx-auto flex w-full max-w-[1280px] items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-12">
        <a href="{{ route('home') }}" aria-label="DevFlow, halaman utama" class="inline-flex items-center gap-2.5 text-lg font-semibold tracking-tight text-zinc-950">
            <img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-9 rounded-xl">
            <span class="font-display">DevFlow<span class="text-[#8f344f]">.</span></span>
        </a>
        <nav aria-label="Akun" class="flex items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950">Buka workspace</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950">Masuk</a>
                <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#651d32] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#501528]">Mulai gratis <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
            @endauth
        </nav>
    </header>

    <main id="konten" class="mx-auto w-full max-w-[1280px] px-5 pb-20 pt-5 sm:px-8 sm:pt-10 lg:px-12 lg:pb-28">
        <section class="grid items-center gap-12 lg:grid-cols-[minmax(0,.9fr)_minmax(0,1.1fr)] lg:gap-16" aria-labelledby="hero-title">
            <div class="max-w-[620px]">
                <p class="inline-flex min-h-8 items-center gap-2 rounded-full border border-[#ead7d9] bg-[#f1e2e5] px-3 text-xs font-semibold text-[#651d32]"><span class="size-1.5 rounded-full bg-[#8f344f]" aria-hidden="true"></span> Satu ruang untuk seluruh alur kerjamu</p>
                <h1 id="hero-title" class="df-page-title mt-6 max-w-[12ch] text-[clamp(2.6rem,6vw,4.4rem)] leading-[1.04]">Kerja fokus.<br><span class="text-[#8f344f]">Tumbuh konsisten.</span></h1>
                <p class="mt-6 max-w-[48ch] text-base leading-7 text-zinc-600 sm:text-lg sm:leading-8">DevFlow menyatukan tugas, proyek, kalender, fokus, dan pembelajaran dalam satu workspace yang terasa ringan dipakai setiap hari.</p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#651d32] px-5 text-sm font-semibold text-white shadow-md shadow-[#651d32]/15 transition hover:bg-[#501528]">Buka workspace <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#651d32] px-5 text-sm font-semibold text-white shadow-md shadow-[#651d32]/15 transition hover:bg-[#501528]">Mulai dengan DevFlow <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                        <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center rounded-xl border border-zinc-200 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:border-[#d5b6bd] hover:bg-zinc-50">Saya sudah punya akun</a>
                    @endauth
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-zinc-500">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-check text-[#24784f]" aria-hidden="true"></i>Alur personal dan tim menyatu</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-check text-[#24784f]" aria-hidden="true"></i>Dirancang untuk developer</span>
                </div>
            </div>

            <div class="relative min-w-0" aria-label="Contoh tampilan workspace DevFlow">
                <div class="absolute -inset-5 -z-10 rounded-[36px] bg-[#ead9d3]/70 blur-2xl" aria-hidden="true"></div>
                <div class="overflow-hidden rounded-[22px] border border-[#e7ded5] bg-[#fcfaf7] shadow-[0_26px_80px_rgba(70,33,45,.13)]">
                    <div class="flex min-h-14 items-center justify-between gap-3 border-b border-[#e7ded5] px-4 sm:px-5">
                        <div class="flex items-center gap-2.5"><img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-7 rounded-lg"><span class="text-xs font-semibold text-[#242127]">DevFlow <span class="font-normal text-[#9a8e8c]">/ Hari ini</span></span></div>
                        <span class="flex items-center gap-1.5 text-[10px] font-medium text-[#706970]"><span class="size-1.5 rounded-full bg-[#24784f]" aria-hidden="true"></span> Semua sinkron</span>
                    </div>
                    <div class="grid min-h-[365px] grid-cols-[44px_minmax(0,1fr)] sm:grid-cols-[54px_minmax(0,1fr)]">
                        <div class="flex flex-col items-center gap-4 border-r border-[#eee7e0] bg-[#f8f4ef] py-4 text-[#a29694]" aria-hidden="true"><i class="fa-solid fa-house text-xs text-[#651d32]"></i><i class="fa-solid fa-inbox text-xs"></i><i class="fa-solid fa-list-check text-xs"></i><i class="fa-solid fa-calendar-days text-xs"></i><i class="fa-solid fa-folder text-xs"></i><span class="mt-auto grid size-6 place-items-center rounded-full bg-[#ead9d3] text-[9px] font-bold text-[#651d32]">F</span></div>
                        <div class="min-w-0 p-4 sm:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><p class="text-[10px] font-semibold uppercase tracking-[.12em] text-[#9a8e8c]">Senin, 16 November</p><h2 class="mt-1 font-display text-xl font-semibold tracking-tight text-[#242127] sm:text-2xl">Mulai dengan jelas.</h2><p class="mt-1 text-[11px] text-[#82777b]">Tiga prioritas, satu rencana yang realistis.</p></div>
                                <span class="rounded-lg bg-[#f1e2e5] px-2.5 py-1.5 text-[10px] font-semibold text-[#651d32]">2j 30m fokus</span>
                            </div>
                            <div class="mt-5 grid grid-cols-3 gap-2">
                                <div class="rounded-xl border border-[#eee7e0] bg-white p-2.5 sm:p-3"><p class="text-[9px] text-[#8f8585]">Tugas aktif</p><p class="mt-1 font-display text-lg font-semibold text-[#242127]">12</p></div>
                                <div class="rounded-xl border border-[#eee7e0] bg-white p-2.5 sm:p-3"><p class="text-[9px] text-[#8f8585]">Proyek</p><p class="mt-1 font-display text-lg font-semibold text-[#242127]">04</p></div>
                                <div class="rounded-xl border border-[#eee7e0] bg-white p-2.5 sm:p-3"><p class="text-[9px] text-[#8f8585]">Belajar</p><p class="mt-1 font-display text-lg font-semibold text-[#242127]">30m</p></div>
                            </div>
                            <div class="mt-5 rounded-xl border border-[#eee7e0] bg-white p-3 sm:p-4">
                                <div class="mb-3 flex items-center justify-between"><h3 class="text-xs font-semibold text-[#242127]">Prioritas hari ini</h3><span class="text-[10px] font-medium text-[#8f344f]">Lihat semua →</span></div>
                                <div class="space-y-2.5">
                                    <div class="flex items-center gap-2.5 rounded-lg bg-[#faf7f4] p-2.5"><span class="grid size-4 place-items-center rounded-full border border-[#c38e9b] text-[8px] text-[#8f344f]">1</span><span class="min-w-0 flex-1 truncate text-[10px] font-medium text-[#3c353b]">Rapikan alur deployment</span><span class="rounded bg-[#f1e2e5] px-1.5 py-1 text-[9px] text-[#651d32]">DevFlow</span></div>
                                    <div class="flex items-center gap-2.5 rounded-lg bg-[#faf7f4] p-2.5"><span class="grid size-4 place-items-center rounded-full border border-[#c38e9b] text-[8px] text-[#8f344f]">2</span><span class="min-w-0 flex-1 truncate text-[10px] font-medium text-[#3c353b]">Review pull request tim</span><span class="rounded bg-[#eef2ed] px-1.5 py-1 text-[9px] text-[#32654d]">Tim</span></div>
                                    <div class="flex items-center gap-2.5 rounded-lg bg-[#faf7f4] p-2.5"><span class="grid size-4 place-items-center rounded-full border border-[#c38e9b] text-[8px] text-[#8f344f]">3</span><span class="min-w-0 flex-1 truncate text-[10px] font-medium text-[#3c353b]">Belajar observability</span><span class="rounded bg-[#f4ede2] px-1.5 py-1 text-[9px] text-[#8a5b28]">Belajar</span></div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between rounded-xl bg-[#f1e2e5] p-3"><span class="flex items-center gap-2 text-[10px] font-medium text-[#651d32]"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Kamu punya ruang untuk fokus</span><span class="text-[9px] font-semibold text-[#8f344f]">Mulai sesi →</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-20 border-t border-[#e7ded5] pt-8 sm:mt-24" aria-labelledby="modules-title">
            <div class="max-w-xl"><p class="text-xs font-semibold uppercase tracking-[.14em] text-[#8f344f]">Satu ritme kerja</p><h2 id="modules-title" class="mt-2 font-display text-2xl font-semibold tracking-tight text-[#242127] sm:text-3xl">Semua yang penting, terhubung.</h2></div>
            <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-[#e7ded5] bg-[#fcfaf7] p-5"><span class="grid size-10 place-items-center rounded-xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span><h3 class="mt-4 text-sm font-semibold text-[#242127]">Rencana personal</h3><p class="mt-2 text-xs leading-5 text-[#706970]">Inbox, tugas, rutinitas, tujuan, kalender, dan sesi fokus mengikuti prioritasmu.</p></article>
                <article class="rounded-2xl border border-[#e7ded5] bg-[#fcfaf7] p-5"><span class="grid size-10 place-items-center rounded-xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-code-branch" aria-hidden="true"></i></span><h3 class="mt-4 text-sm font-semibold text-[#242127]">Alur developer</h3><p class="mt-2 text-xs leading-5 text-[#706970]">Ubah PRD menjadi pekerjaan, tautkan tugas ke GitHub, dan lanjutkan dari konteks terakhir.</p></article>
                <article class="rounded-2xl border border-[#e7ded5] bg-[#fcfaf7] p-5"><span class="grid size-10 place-items-center rounded-xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span><h3 class="mt-4 text-sm font-semibold text-[#242127]">Belajar konsisten</h3><p class="mt-2 text-xs leading-5 text-[#706970]">Jaga jalur belajar dan catat kemajuan tanpa memisahkannya dari jadwal kerja.</p></article>
                <article class="rounded-2xl border border-[#e7ded5] bg-[#fcfaf7] p-5"><span class="grid size-10 place-items-center rounded-xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span><h3 class="mt-4 text-sm font-semibold text-[#242127]">Kolaborasi tim</h3><p class="mt-2 text-xs leading-5 text-[#706970]">Pindah dari rencana pribadi ke proyek bersama dengan pola kerja yang tetap akrab.</p></article>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#e7ded5]">
        <div class="mx-auto flex max-w-[1280px] flex-col gap-3 px-5 py-5 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
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
