@php
    $isTeamContext = request()->routeIs('projects.*', 'scope.*', 'github.*', 'tasks.show', 'task-checklist.*', 'task-comments.*');
@endphp
<header class="df-toolbar sticky top-0 z-20 flex min-h-[72px] w-full min-w-0 items-center justify-between gap-2 border-b px-3 backdrop-blur-xl min-[481px]:gap-3 min-[481px]:px-5 lg:px-7">
    <div class="flex min-w-0 items-center gap-2.5 min-[481px]:gap-3">
        <button type="button" class="grid size-10 shrink-0 place-items-center rounded-xl text-zinc-600 transition hover:bg-zinc-100 min-[851px]:hidden" data-sidebar-open aria-label="Buka navigasi" aria-controls="app-sidebar" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
        <a href="{{ $isTeamContext ? route('projects.index') : route('dashboard') }}" class="hidden min-h-9 shrink-0 items-center gap-2 rounded-lg px-2 text-[11px] font-semibold {{ $isTeamContext ? 'bg-[#f1e2e5] text-[#651d32]' : 'text-zinc-500 hover:bg-zinc-100' }} min-[851px]:inline-flex" aria-label="{{ $isTeamContext ? 'Ruang tim' : 'Ruang pribadi' }}">
            <span class="grid size-6 place-items-center rounded-md {{ $isTeamContext ? 'bg-[#651d32] text-white' : 'bg-[#f0e9e1] text-[#651d32]' }}"><i class="fa-solid {{ $isTeamContext ? 'fa-people-group' : 'fa-user' }} text-[10px]" aria-hidden="true"></i></span>{{ $isTeamContext ? 'Tim' : 'Pribadi' }}
        </a>
        <span class="hidden text-zinc-300 min-[851px]:block" aria-hidden="true">/</span>
        <span class="max-w-[30vw] truncate text-[13px] font-semibold text-zinc-800 min-[481px]:max-w-[34vw] min-[851px]:max-w-[28vw]">@yield('page-heading', 'Ikhtisar')</span>
    </div>

    <button type="button" class="flex min-h-10 min-w-0 max-w-[560px] flex-1 items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-2 text-sm text-zinc-500 transition hover:border-[#d5b6bd] hover:bg-white min-[481px]:min-h-11 min-[481px]:justify-start min-[481px]:gap-3 min-[481px]:px-3 lg:mx-3 lg:flex-none lg:w-[min(38vw,560px)]" data-command-open aria-label="Cari fitur, tugas, proyek, dan aksi" aria-keyshortcuts="Control+k Meta+k">
        <i class="fa-solid fa-magnifying-glass shrink-0 text-zinc-500" aria-hidden="true"></i><span class="hidden min-w-0 flex-1 truncate text-left min-[481px]:block">Cari di DevFlow</span><kbd class="hidden shrink-0 rounded-md border border-zinc-200 bg-white px-2 py-1 font-mono text-[10px] text-zinc-500 sm:block">⌘ K</kbd>
    </button>

    <div class="ml-auto flex shrink-0 items-center gap-1 min-[481px]:gap-2">
        <details class="group relative shrink-0">
            <summary aria-label="Buka menu pembuatan" class="flex min-h-10 cursor-pointer list-none items-center gap-1.5 rounded-xl bg-[#651d32] px-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#501528] [&::-webkit-details-marker]:hidden min-[481px]:min-h-11 min-[481px]:gap-2 min-[481px]:px-3.5">
                <i class="fa-solid fa-plus" aria-hidden="true"></i><span class="hidden sm:inline">Buat baru</span><i class="fa-solid fa-chevron-down text-[9px]" aria-hidden="true"></i>
            </summary>
            <div class="absolute right-0 top-[calc(100%+9px)] z-30 w-56 rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-xl">
                <p class="px-3 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-[.12em] text-zinc-400">Mulai sesuatu</p>
                <a href="{{ route('tasks.index', ['quick_add' => 'task']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-list-check w-4 text-center" aria-hidden="true"></i>Tugas baru</a>
                <a href="{{ route('projects.create') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-folder-plus w-4 text-center" aria-hidden="true"></i>Proyek baru</a>
                <a href="{{ route('calendar.index', ['quick_add' => 'event']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-calendar-plus w-4 text-center" aria-hidden="true"></i>Agenda baru</a>
                <a href="{{ route('personal-notes.index', ['quick_add' => 'note']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-note-sticky w-4 text-center" aria-hidden="true"></i>Catatan baru</a>
                <a href="{{ route('routines.index', ['create' => 'routine']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-repeat w-4 text-center" aria-hidden="true"></i>Rutinitas baru</a>
                <a href="{{ route('goals.index', ['create' => 'goal']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-flag-checkered w-4 text-center" aria-hidden="true"></i>Tujuan baru</a>
                <a href="{{ route('learning.index', ['create' => 'learning-path']) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-zinc-700 hover:bg-[#f1e2e5] hover:text-[#651d32]"><i class="fa-solid fa-graduation-cap w-4 text-center" aria-hidden="true"></i>Jalur belajar</a>
            </div>
        </details>

        <button type="button" data-theme-toggle class="df-toolbar-secondary-action grid size-10 place-items-center rounded-xl text-zinc-600 transition hover:bg-zinc-100" aria-label="Aktifkan tema gelap" title="Ubah tema">
            <i class="fa-solid fa-moon" aria-hidden="true" data-theme-icon></i>
        </button>
        <a href="{{ route('notifications.index') }}" class="df-toolbar-secondary-action relative grid size-10 shrink-0 place-items-center rounded-xl text-zinc-600 transition hover:bg-zinc-100 hover:text-[#651d32]" aria-label="Pusat notifikasi" title="Notifikasi"><i class="fa-regular fa-bell" aria-hidden="true"></i><span class="absolute right-[9px] top-[8px] size-1.5 rounded-full bg-[#8f344f]" aria-hidden="true"></span></a>
        <a href="{{ route('settings.index') }}" class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ead9d3] text-xs font-bold text-[#651d32] ring-2 ring-white" aria-label="Profil {{ auth()->user()->name }}" title="{{ auth()->user()->name }}">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</a>
    </div>
</header>
