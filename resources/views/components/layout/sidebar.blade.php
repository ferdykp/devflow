@php
    $menuDasar = 'relative flex min-h-[52px] w-[58px] flex-col items-center justify-center gap-1.5 rounded-xl px-0.5 text-center text-[9px] font-semibold leading-tight tracking-normal transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400';
    $menuBiasa = 'text-[#9699a3] hover:bg-[#252830] hover:text-white';
    $menuAktif = 'bg-[#2b3039] text-white before:absolute before:-left-2 before:top-1/2 before:h-[22px] before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-[#7ca4ff]';
    $projectAktif = request()->routeIs('projects.*') || request()->routeIs('tasks.*') || request()->routeIs('scope.*') || request()->routeIs('github.*');
@endphp

<div data-sidebar-overlay hidden
    class="fixed inset-0 z-[29] bg-black/55 [&:not([hidden])]:block">
</div>

<aside id="app-sidebar" data-sidebar aria-label="Navigasi ruang kerja"
    class="fixed inset-y-0 left-0 z-30 flex h-dvh w-[74px] -translate-x-[110%] flex-col items-center border-r border-[#24262b] bg-[#141518] text-[#a9acb4] shadow-[15px_0_45px_#0003] transition-transform duration-200 data-[open]:translate-x-0 motion-reduce:transition-none min-[851px]:sticky min-[851px]:top-0 min-[851px]:h-svh min-[851px]:translate-x-0 min-[851px]:shadow-none">

    <div class="relative flex h-[76px] w-full shrink-0 items-center justify-center">
        <a href="{{ route('dashboard') }}" aria-label="Ringkasan DevFlow"
            class="flex h-11 w-11 items-center justify-center rounded-xl text-[19px] font-extrabold tracking-[-0.095em] text-white">
            d<span class="text-[#b4bdcc]">f</span><b class="text-[#7ba0ff]">.</b>
        </a>

        <button type="button" data-sidebar-close aria-label="Tutup navigasi"
            class="absolute left-20 top-[18px] grid h-9 w-9 place-items-center rounded-lg border border-[#e7e8ec] bg-white text-[#151515] min-[851px]:hidden">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="flex w-full flex-col items-center gap-2 px-2 py-2" aria-label="Navigasi utama">
        <a href="{{ route('dashboard') }}" title="Ringkasan"
            class="{{ $menuDasar }} {{ request()->routeIs('dashboard') ? $menuAktif : $menuBiasa }}"
            @if (request()->routeIs('dashboard')) aria-current="page" @endif>
            <i class="fa-solid fa-house shrink-0 text-[17px]" aria-hidden="true"></i>
            <span class="block whitespace-nowrap">Ringkasan</span>
        </a>

        <a href="{{ route('projects.index') }}" title="Proyek"
            class="{{ $menuDasar }} {{ $projectAktif ? $menuAktif : $menuBiasa }}"
            @if ($projectAktif) aria-current="page" @endif>
            <i class="fa-regular fa-folder shrink-0 text-[17px]" aria-hidden="true"></i>
            <span class="block whitespace-nowrap">Proyek</span>
        </a>

        <a href="{{ route('focus.index') }}" title="Fokus Saya"
            class="{{ $menuDasar }} {{ request()->routeIs('focus.*') ? $menuAktif : $menuBiasa }}"
            @if (request()->routeIs('focus.*')) aria-current="page" @endif>
            <i class="fa-solid fa-bullseye shrink-0 text-[17px]" aria-hidden="true"></i>
            <span class="block whitespace-nowrap">Fokus Saya</span>
        </a>
    </nav>

    <div class="mt-auto flex w-full flex-col items-center gap-2 px-2 pb-5 pt-2">
        <button type="button" data-command-open title="Pencarian cepat" aria-label="Buka pencarian cepat"
            class="{{ $menuDasar }} {{ $menuBiasa }}">
            <i class="fa-solid fa-magnifying-glass shrink-0 text-[17px]" aria-hidden="true"></i>
            <span class="block whitespace-nowrap">Cari</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar" class="{{ $menuDasar }} {{ $menuBiasa }}">
                <i class="fa-solid fa-arrow-right-from-bracket shrink-0 text-[17px]" aria-hidden="true"></i>
                <span class="block whitespace-nowrap">Keluar</span>
            </button>
        </form>

        <div class="mt-3 grid h-[34px] w-[34px] place-items-center rounded-full border border-[#454952] bg-[#2a2d35] text-xs font-bold text-white"
            title="{{ auth()->user()->email }}">
            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</aside>
