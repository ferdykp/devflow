<header class="df-toolbar sticky top-0 z-20 flex min-h-16 items-center justify-between gap-4 border-b border-zinc-100 bg-white/95 px-5 backdrop-blur-sm sm:px-7 min-[851px]:px-8 xl:px-10">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" class="grid size-11 shrink-0 place-items-center rounded-lg text-zinc-600 hover:bg-zinc-100 min-[851px]:hidden" data-sidebar-open aria-label="Buka navigasi" aria-controls="app-sidebar" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
        <a href="{{ route('dashboard') }}" class="hidden text-sm text-zinc-500 hover:text-zinc-900 sm:block">Ruang kerja</a>
        <span class="hidden text-zinc-300 sm:block" aria-hidden="true">/</span>
        <span class="truncate text-sm font-medium">@yield('page-heading', 'Ringkasan')</span>
    </div>
    <button type="button" class="flex min-h-11 shrink-0 items-center gap-3 rounded-lg px-3 text-sm text-zinc-500 transition-colors hover:bg-zinc-100" data-command-open aria-label="Buka pencarian cepat" aria-keyshortcuts="Control+k Meta+k"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="hidden sm:block">Cari halaman</span><kbd class="hidden rounded border border-zinc-200 px-1.5 text-xs sm:block">⌘ K</kbd></button>
</header>
