<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1b181c">
    <title>DevFlow — Kerja dan berkembang dengan lebih terarah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>try{const theme=localStorage.getItem('devflow.theme');document.documentElement.dataset.theme=theme==='light'?'light':'dark'}catch{document.documentElement.dataset.theme='dark'}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen overflow-x-hidden bg-canvas font-sans text-ink antialiased">
    <a href="#konten" class="absolute -top-24 left-4 z-50 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#421627] shadow-xl focus:top-4">Lewati ke konten</a>

    <header class="px-4 pt-4 sm:px-6 sm:pt-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-[1440px] items-center justify-between gap-2 rounded-[20px] border border-white/10 bg-[#48172a] px-3 py-3 text-white shadow-[0_18px_50px_rgba(54,20,34,.18)] sm:gap-4 sm:px-6 sm:py-3.5 lg:px-8 min-[1800px]:max-w-[1680px] min-[2400px]:max-w-[1840px]">
            <a href="{{ route('home') }}" aria-label="DevFlow, halaman utama" class="inline-flex min-h-11 shrink-0 items-center gap-1.5 rounded-lg text-sm font-semibold tracking-tight text-white focus-visible:outline-white min-[360px]:gap-3 min-[360px]:text-lg">
                <img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-9 rounded-xl bg-white/10 p-0.5">
                <span class="font-display">DevFlow<span class="text-[#e3b8a8]">.</span></span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-1 md:flex">
                <a href="#alur" class="inline-flex min-h-11 items-center rounded-xl px-4 text-sm font-medium text-white/75 transition hover:bg-white/10 hover:text-white focus-visible:outline-white">Alur kerja</a>
                <a href="#fitur" class="inline-flex min-h-11 items-center rounded-xl px-4 text-sm font-medium text-white/75 transition hover:bg-white/10 hover:text-white focus-visible:outline-white">Fitur</a>
            </nav>

            <nav aria-label="Akun" class="flex items-center gap-2 sm:gap-3">
                <button type="button" data-theme-toggle aria-label="Aktifkan tema terang" aria-pressed="true" class="grid size-11 shrink-0 place-items-center rounded-xl border border-white/20 text-white/85 transition hover:bg-white/10 hover:text-white" title="Ubah tema"><i class="fa-solid fa-sun" aria-hidden="true" data-theme-icon></i><span class="sr-only" data-theme-label>Tema terang</span></button>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-white sm:px-4">Buka workspace</a>
                @else
                    <a href="{{ route('login') }}" class="hidden min-[420px]:inline-flex min-h-11 items-center rounded-xl px-3 text-xs font-semibold text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-white min-[420px]:text-sm sm:px-4">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-11 shrink-0 items-center gap-1 whitespace-nowrap rounded-xl bg-[#f3e8df] px-2 text-[10px] font-bold text-[#48172a] shadow-sm transition hover:bg-white focus-visible:outline-white min-[360px]:gap-2 min-[360px]:px-3 min-[360px]:text-xs sm:px-5 sm:text-sm">Mulai gratis <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs" aria-hidden="true"></i></a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="konten" class="mx-auto flex w-full max-w-[1440px] flex-col gap-5 px-4 pb-8 pt-5 sm:gap-6 sm:px-6 sm:pb-12 sm:pt-7 lg:gap-7 lg:px-8 lg:pb-16 min-[1800px]:max-w-[1680px] min-[2400px]:max-w-[1840px]">
        <section id="alur" aria-labelledby="hero-title" class="grid items-center gap-5 xl:grid-cols-[minmax(0,.9fr)_minmax(0,1.1fr)] xl:gap-6">
            <div class="flex flex-col justify-center rounded-[28px] border border-[#e9ddd5] bg-[#fcfaf7] p-6 shadow-[0_10px_35px_rgba(58,34,40,.045)] sm:p-9 lg:p-11 xl:p-12">
                <p class="inline-flex min-h-9 w-fit items-center gap-2 rounded-full border border-[#e7cfd2] bg-[#f5e9e9] px-3.5 text-xs font-semibold text-[#6d243b]">
                    <span class="relative flex size-2" aria-hidden="true"><span class="absolute inline-flex size-full animate-ping rounded-full bg-[#a34a62]/60 motion-reduce:animate-none"></span><span class="relative inline-flex size-2 rounded-full bg-[#8f344f]"></span></span>
                    Satu ruang untuk seluruh alur kerjamu
                </p>

                <h1 id="hero-title" class="landing-hero-title mt-6 font-display text-[clamp(2.15rem,3.6vw,3.65rem)] font-semibold leading-[1.02] tracking-[-.055em] text-[#2b2026]">Kerja fokus.<br><span class="text-[#873149]">Tumbuh konsisten.</span></h1>
                <p class="mt-6 max-w-[48ch] text-base leading-7 text-[#6d6468] sm:text-lg sm:leading-8">Tugas, proyek, kalender, dan pembelajaran tersusun dalam satu workspace yang membantu kamu kembali ke pekerjaan dengan lebih cepat.</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#651d32] px-5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(101,29,50,.18)] transition hover:bg-[#501528] focus-visible:outline-[#8f344f]">Buka workspace <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#651d32] px-5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(101,29,50,.18)] transition hover:bg-[#501528] focus-visible:outline-[#8f344f]">Mulai dengan DevFlow <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                        <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e4d9d2] bg-white px-5 text-sm font-semibold text-[#534950] transition hover:border-[#cdaab3] hover:bg-[#faf6f2] focus-visible:outline-[#8f344f]">Saya sudah punya akun</a>
                    @endauth
                </div>

                <div class="mt-8 grid gap-2 border-t border-[#eee4de] pt-5 text-sm text-[#62585d] sm:grid-cols-2">
                    <span class="inline-flex min-h-10 items-center gap-2.5 rounded-xl bg-[#f7f2ed] px-3"><i class="fa-solid fa-check text-xs text-[#24784f]" aria-hidden="true"></i>Alur personal dan tim menyatu</span>
                    <span class="inline-flex min-h-10 items-center gap-2.5 rounded-xl bg-[#f7f2ed] px-3"><i class="fa-solid fa-check text-xs text-[#24784f]" aria-hidden="true"></i>Dibuat untuk kerja developer</span>
                </div>
            </div>

            <section aria-label="Contoh tampilan workspace DevFlow" class="landing-3d-scene relative min-w-0 rounded-[28px] border border-[#e9ddd5] bg-[#e9dfd8] p-2.5 shadow-[0_18px_55px_rgba(58,34,40,.08)] sm:p-4 xl:self-center">
                <div class="landing-3d-float landing-3d-float--top absolute -right-3 -top-3 z-10 hidden items-center gap-2 rounded-full border border-[#e9ddd5] bg-[#fcfaf7] px-3.5 py-2 text-xs font-semibold text-[#51464b] shadow-md sm:flex">
                    <span class="size-2 rounded-full bg-[#2c8a5b]" aria-hidden="true"></span> Workspace aktif
                </div>
                <div class="landing-3d-device rounded-[20px] border border-[#d9cec7] bg-[#fbf9f6] p-1">
                    <div class="overflow-hidden rounded-[16px] border border-[#e6dcd5] bg-[#fbf9f6]">
                    <div class="flex min-h-14 items-center justify-between gap-3 border-b border-[#e7ded5] bg-white px-4 sm:px-5">
                        <div class="flex min-w-0 items-center gap-2.5"><img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-7 rounded-lg bg-[#651d32] p-0.5"><span class="truncate text-xs font-bold text-[#30252b]">DevFlow <span class="font-medium text-[#988b8d]">/ Hari ini</span></span></div>
                        <span class="flex shrink-0 items-center gap-1.5 text-[11px] font-semibold text-[#5f6a60]" aria-label="Semua tersimpan"><span class="size-2 rounded-full bg-[#2c8a5b]" aria-hidden="true"></span><span class="hidden min-[360px]:inline">Semua tersimpan</span></span>
                    </div>

                    <div class="grid min-h-[360px] grid-cols-[42px_minmax(0,1fr)] max-[300px]:grid-cols-[34px_minmax(0,1fr)] sm:grid-cols-[54px_minmax(0,1fr)] lg:min-h-[390px]">
                        <div class="flex flex-col items-center gap-4 border-r border-[#e8dfd9] bg-[#f5f0eb] py-4 text-[#9b8e90]" aria-hidden="true">
                            <i class="fa-solid fa-house text-sm text-[#651d32]"></i><i class="fa-solid fa-inbox text-sm"></i><i class="fa-solid fa-list-check text-sm"></i><i class="fa-solid fa-calendar-days text-sm"></i><i class="fa-solid fa-folder text-sm"></i>
                            <span class="mt-auto grid size-7 place-items-center rounded-full bg-[#e8d6d3] text-[10px] font-bold text-[#651d32]">F</span>
                        </div>

                        <div class="min-w-0 p-3.5 max-[300px]:p-2.5 sm:p-5 lg:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><p class="text-[11px] font-bold text-[#8b7d80]">{{ now()->locale('id')->isoFormat('dddd · D MMMM') }}</p><h2 class="mt-1 font-display text-xl font-semibold tracking-tight text-[#30252b] sm:text-2xl">Mulai dengan jelas.</h2><p class="mt-1 text-xs leading-5 text-[#82777b]">Tiga prioritas. Satu rencana yang realistis.</p></div>
                                <span class="inline-flex items-center gap-2 rounded-lg border border-[#ead7d9] bg-[#f6e9ea] px-3 py-2 text-[11px] font-bold text-[#6d243b]"><i class="fa-regular fa-clock" aria-hidden="true"></i> 2j 30m fokus</span>
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-2.5 max-[300px]:gap-1 sm:gap-3">
                                <div class="rounded-xl border border-[#e9dfd9] bg-white p-2.5 max-[300px]:p-1.5 sm:p-3.5"><p class="text-[10px] font-medium text-[#82777b] sm:text-[11px]">Tugas aktif</p><p class="mt-1 font-display text-xl font-semibold text-[#30252b]">12</p></div>
                                <div class="rounded-xl border border-[#e9dfd9] bg-white p-2.5 max-[300px]:p-1.5 sm:p-3.5"><p class="text-[10px] font-medium text-[#82777b] sm:text-[11px]">Proyek</p><p class="mt-1 font-display text-xl font-semibold text-[#30252b]">04</p></div>
                                <div class="rounded-xl border border-[#e9dfd9] bg-white p-2.5 max-[300px]:p-1.5 sm:p-3.5"><p class="text-[10px] font-medium text-[#82777b] sm:text-[11px]">Belajar</p><p class="mt-1 font-display text-xl font-semibold text-[#30252b]">30m</p></div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-[#e9dfd9] bg-white p-3.5 max-[300px]:p-2 sm:p-4">
                                <div class="mb-3 flex items-center justify-between gap-3"><h3 class="text-sm font-bold text-[#30252b]">Prioritas hari ini</h3><span class="text-[11px] font-semibold text-[#873149]">3 tugas</span></div>
                                <div class="grid gap-2.5">
                                    <div class="flex min-h-11 items-center gap-2.5 rounded-xl bg-[#f8f4f0] px-2.5 max-[300px]:gap-1.5 max-[300px]:px-1.5 sm:px-3"><span class="grid size-5 shrink-0 place-items-center rounded-full border border-[#c48f9c] text-[10px] font-semibold text-[#873149]">1</span><span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-[#473d43] sm:text-xs">Rapikan alur deployment</span><span class="hidden shrink-0 rounded-md bg-[#f1e2e5] px-2 py-1 text-[10px] font-semibold text-[#651d32] min-[390px]:inline-flex">DevFlow</span></div>
                                    <div class="flex min-h-11 items-center gap-2.5 rounded-xl bg-[#f8f4f0] px-2.5 max-[300px]:gap-1.5 max-[300px]:px-1.5 sm:px-3"><span class="grid size-5 shrink-0 place-items-center rounded-full border border-[#c48f9c] text-[10px] font-semibold text-[#873149]">2</span><span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-[#473d43] sm:text-xs">Review pull request tim</span><span class="hidden shrink-0 rounded-md bg-[#edf1ec] px-2 py-1 text-[10px] font-semibold text-[#35644c] min-[390px]:inline">Tim</span></div>
                                    <div class="flex min-h-11 items-center gap-2.5 rounded-xl bg-[#f8f4f0] px-2.5 max-[300px]:gap-1.5 max-[300px]:px-1.5 sm:px-3"><span class="grid size-5 shrink-0 place-items-center rounded-full border border-[#c48f9c] text-[10px] font-semibold text-[#873149]">3</span><span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-[#473d43] sm:text-xs">Belajar observability</span><span class="hidden shrink-0 rounded-md bg-[#f3ede2] px-2 py-1 text-[10px] font-semibold text-[#805621] min-[390px]:inline-flex">Belajar</span></div>
                                </div>
                            </div>

                            <div class="mt-3 flex min-h-12 items-center justify-between gap-3 rounded-xl bg-[#f0e1e3] px-3.5 py-2.5 sm:px-4">
                                <span class="flex items-center gap-2.5 text-[11px] font-semibold text-[#651d32]"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>Kamu punya ruang untuk fokus</span>
                                <span class="hidden shrink-0 text-[11px] font-bold text-[#873149] sm:inline">Mulai sesi <i class="fa-solid fa-arrow-right ml-1 text-[10px]" aria-hidden="true"></i></span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="landing-3d-float landing-3d-float--bottom absolute -bottom-3 left-5 z-10 hidden items-center gap-3 rounded-2xl border border-[#e8ddd6] bg-[#fcfaf7] px-4 py-3 shadow-xl sm:flex">
                    <span class="grid size-9 place-items-center rounded-xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-bolt text-sm" aria-hidden="true"></i></span>
                    <span><span class="block text-[10px] font-medium text-[#8b7d80]">Lanjutkan sesi</span><span class="mt-0.5 block text-xs font-bold text-[#30252b]">Relasi Eloquent · 30 mnt</span></span>
                </div>
            </section>
        </section>

        <section id="fitur" aria-labelledby="modules-title" class="flex flex-col gap-4">
            <div class="grid gap-4 rounded-[26px] border border-[#e8ddd6] bg-[#eee5de] p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_minmax(260px,.65fr)] lg:items-end lg:p-9">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold text-[#873149]">Dirancang sebagai satu alur</p>
                    <h2 id="modules-title" class="mt-2 font-display text-2xl font-semibold leading-tight tracking-[-.04em] text-[#2b2026] sm:text-3xl lg:text-4xl">Semua yang penting, terhubung.</h2>
                </div>
                <p class="max-w-xl text-sm leading-6 text-[#6d6468] lg:justify-self-end lg:text-base">Pindah dari fokus pribadi ke proyek tim tanpa kehilangan konteks, keputusan, atau langkah berikutnya.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-12">
                <article class="group flex min-h-56 flex-col rounded-[22px] border border-[#e8ddd6] bg-[#fcfaf7] p-5 shadow-[0_6px_22px_rgba(58,34,40,.035)] transition duration-200 hover:-translate-y-1 hover:border-[#d8bcc0] hover:shadow-[0_16px_34px_rgba(58,34,40,.08)] motion-reduce:transform-none motion-reduce:transition-none sm:p-6 xl:col-span-5">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-list-check text-base" aria-hidden="true"></i></span>
                    <h3 class="mt-5 text-base font-bold text-[#30252b]">Rencana personal</h3>
                    <p class="mt-2 text-sm leading-6 text-[#706970]">Inbox, rutinitas, tujuan, kalender, dan sesi fokus mengikuti prioritasmu.</p>
                    <div class="mt-auto flex flex-wrap gap-2 pt-5 text-[11px] font-semibold text-[#6b515b]"><span class="rounded-lg bg-[#f3ebe5] px-2.5 py-1.5">Tugas</span><span class="rounded-lg bg-[#f3ebe5] px-2.5 py-1.5">Rutinitas</span><span class="rounded-lg bg-[#f3ebe5] px-2.5 py-1.5">Fokus</span></div>
                </article>

                <article class="group relative flex min-h-56 flex-col overflow-hidden rounded-[22px] border border-[#542237] bg-[#48172a] p-5 text-white shadow-[0_12px_32px_rgba(54,20,34,.14)] transition duration-200 hover:-translate-y-1 hover:bg-[#501b30] motion-reduce:transform-none motion-reduce:transition-none sm:p-6 xl:col-span-7">
                    <span class="absolute -right-10 -top-16 size-48 rounded-full border border-white/10" aria-hidden="true"></span>
                    <span class="absolute -right-1 -top-7 size-28 rounded-full border border-white/10" aria-hidden="true"></span>
                    <span class="grid size-11 place-items-center rounded-2xl bg-white/10 text-[#f3dce0]"><i class="fa-solid fa-code-branch text-base" aria-hidden="true"></i></span>
                    <h3 class="mt-5 text-base font-bold text-white">Alur developer</h3>
                    <p class="mt-2 max-w-[52ch] text-sm leading-6 text-white/75">Turunkan PRD menjadi tugas, simpan keputusan scope, dan lanjutkan dari konteks terakhir.</p>
                    <div class="relative mt-auto flex flex-wrap items-center gap-2 pt-5 text-[11px] font-semibold text-white/85"><span class="rounded-lg border border-white/15 bg-white/10 px-3 py-2">PRD</span><i class="fa-solid fa-arrow-right text-[10px] text-[#e8b9ba]" aria-hidden="true"></i><span class="rounded-lg border border-white/15 bg-white/10 px-3 py-2">Tugas</span><i class="fa-solid fa-arrow-right text-[10px] text-[#e8b9ba]" aria-hidden="true"></i><span class="rounded-lg border border-white/15 bg-white/10 px-3 py-2">Konteks</span></div>
                </article>

                <article class="group flex min-h-56 flex-col justify-between gap-5 rounded-[22px] border border-[#e8ddd6] bg-[#eee5de] p-5 transition duration-200 hover:border-[#d8bcc0] sm:p-6 xl:col-span-7 xl:flex-row xl:items-end">
                    <div class="max-w-xl">
                        <span class="grid size-11 place-items-center rounded-2xl bg-[#fcfaf7] text-[#651d32]"><i class="fa-solid fa-graduation-cap text-base" aria-hidden="true"></i></span>
                        <h3 class="mt-5 text-base font-bold text-[#30252b]">Belajar konsisten</h3>
                        <p class="mt-2 text-sm leading-6 text-[#706970]">Susun jalur belajar, catat sesi, dan hubungkan latihan dengan pekerjaan nyata.</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2 text-[11px] font-semibold text-[#6b515b] xl:max-w-44">
                        <span class="rounded-lg border border-[#d9cbc3] bg-[#f8f4f0] px-3 py-2">Baca</span>
                        <span class="rounded-lg border border-[#d9cbc3] bg-[#f8f4f0] px-3 py-2">Praktik</span>
                        <span class="rounded-lg border border-[#d9cbc3] bg-[#f8f4f0] px-3 py-2">Catat hasil</span>
                    </div>
                </article>

                <article class="group flex min-h-56 flex-col rounded-[22px] border border-[#e8ddd6] bg-[#fcfaf7] p-5 shadow-[0_6px_22px_rgba(58,34,40,.035)] transition duration-200 hover:-translate-y-1 hover:border-[#d8bcc0] hover:shadow-[0_16px_34px_rgba(58,34,40,.08)] motion-reduce:transform-none motion-reduce:transition-none sm:p-6 xl:col-span-5">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#f1e2e5] text-[#651d32]"><i class="fa-solid fa-people-group text-base" aria-hidden="true"></i></span>
                    <h3 class="mt-5 text-base font-bold text-[#30252b]">Kolaborasi tim</h3>
                    <p class="mt-2 text-sm leading-6 text-[#706970]">Bagikan proyek, tetapkan penanggung jawab, dan jaga keputusan tetap terbaca.</p>
                    <div class="mt-auto flex flex-wrap gap-2 pt-5 text-[11px] font-semibold text-[#6b515b]"><span class="rounded-lg bg-[#f3ebe5] px-2.5 py-1.5">Pemilik tugas</span><span class="rounded-lg bg-[#f3ebe5] px-2.5 py-1.5">Keputusan</span></div>
                </article>
            </div>
        </section>

        <section aria-labelledby="closing-title" class="grid gap-5 overflow-hidden rounded-[28px] border border-[#5e2a3b] bg-[#48172a] p-6 text-white shadow-[0_18px_46px_rgba(54,20,34,.16)] sm:p-9 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:px-10 lg:py-9">
            <div>
                <p class="text-xs font-semibold text-[#e0b7b0]">Mulai dari satu langkah</p>
                <h2 id="closing-title" class="mt-2 max-w-2xl font-display text-2xl font-semibold leading-tight tracking-[-.04em] sm:text-3xl">Buat ruang untuk pekerjaan yang berarti.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/70">Atur prioritas, kumpulkan konteks, lalu lanjutkan dengan ritme yang terasa pas.</p>
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#f3e8df] px-5 text-sm font-bold text-[#48172a] transition hover:bg-white focus-visible:outline-white">Buka workspace <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#f3e8df] px-5 text-sm font-bold text-[#48172a] transition hover:bg-white focus-visible:outline-white">Mulai gratis <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/25 px-5 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline-white">Masuk</a>
                @endauth
            </div>
        </section>

        <footer class="flex flex-col gap-4 rounded-[22px] border border-[#e8ddd6] bg-[#fcfaf7] px-5 py-5 text-sm text-[#706970] sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <a href="{{ route('home') }}" aria-label="DevFlow, halaman utama" class="inline-flex min-h-11 items-center gap-2.5 self-start font-semibold text-[#30252b]">
                <img src="{{ asset('icons/devflow-mark.svg') }}" alt="" class="size-7 rounded-lg">
                <span class="font-display">DevFlow<span class="text-[#8f344f]">.</span></span>
            </a>
            <span class="text-xs sm:mr-auto sm:ml-3">© {{ date('Y') }} DevFlow</span>
            <nav aria-label="Informasi layanan" class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium">
                <a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center underline-offset-4 transition hover:text-[#651d32] hover:underline">Kebijakan privasi</a>
                <a href="{{ route('terms') }}" class="inline-flex min-h-11 items-center underline-offset-4 transition hover:text-[#651d32] hover:underline">Syarat layanan</a>
                <a href="mailto:kpferdy@gmail.com" class="inline-flex min-h-11 items-center underline-offset-4 transition hover:text-[#651d32] hover:underline">Hubungi DevFlow</a>
            </nav>
        </footer>
    </main>
</body>

</html>
