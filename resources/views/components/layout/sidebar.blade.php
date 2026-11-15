@php
    $projectActive = request()->routeIs('projects.*', 'scope.*', 'github.*', 'tasks.show', 'tasks.store', 'tasks.update', 'tasks.destroy', 'task-checklist.*', 'task-comments.*');
    $isTeamContext = request()->routeIs('workspaces.*', 'projects.*', 'scope.*', 'github.*', 'tasks.show', 'task-checklist.*', 'task-comments.*');
    $taskActive = request()->routeIs('tasks.index', 'personal-tasks.show');
    $navGroups = [
        [
            'label' => 'Hari ini',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Ikhtisar', 'short' => 'Ikhtisar', 'icon' => 'fa-house', 'active' => request()->routeIs('dashboard')],
                ['route' => 'my-day.index', 'label' => 'Rencana hari ini', 'short' => 'Hari ini', 'icon' => 'fa-sun', 'active' => request()->routeIs('my-day.*')],
            ],
        ],
        [
            'label' => 'Rencana & kerja',
            'items' => [
                ['route' => 'tasks.index', 'label' => 'Tugas', 'short' => 'Tugas', 'icon' => 'fa-list-check', 'active' => $taskActive],
                ['route' => 'projects.index', 'label' => 'Proyek & tim', 'short' => 'Proyek', 'icon' => 'fa-layer-group', 'active' => $projectActive],
                ['route' => 'calendar.index', 'label' => 'Kalender', 'short' => 'Agenda', 'icon' => 'fa-calendar-days', 'active' => request()->routeIs('calendar.*')],
                ['route' => 'focus.index', 'label' => 'Sesi fokus', 'short' => 'Fokus', 'icon' => 'fa-bullseye', 'active' => request()->routeIs('focus.*')],
            ],
        ],
        [
            'label' => 'Tumbuh & tinjau',
            'items' => [
                ['route' => 'goals.index', 'label' => 'Tujuan', 'short' => 'Tujuan', 'icon' => 'fa-flag-checkered', 'active' => request()->routeIs('goals.*')],
                ['route' => 'routines.index', 'label' => 'Rutinitas', 'short' => 'Rutin', 'icon' => 'fa-repeat', 'active' => request()->routeIs('routines.*')],
                ['route' => 'learning.index', 'label' => 'Belajar', 'short' => 'Belajar', 'icon' => 'fa-graduation-cap', 'active' => request()->routeIs('learning.*')],
                ['route' => 'personal-notes.index', 'label' => 'Catatan', 'short' => 'Catatan', 'icon' => 'fa-note-sticky', 'active' => request()->routeIs('personal-notes.*')],
                ['route' => 'weekly-review.index', 'label' => 'Review mingguan', 'short' => 'Review', 'icon' => 'fa-chart-simple', 'active' => request()->routeIs('weekly-review.*')],
                ['route' => 'smart-coach.index', 'label' => 'Smart Coach', 'short' => 'Coach', 'icon' => 'fa-lightbulb', 'active' => request()->routeIs('smart-coach.*')],
            ],
        ],
    ];
@endphp
<div data-sidebar-overlay hidden class="fixed inset-0 z-[29] bg-zinc-950/45 backdrop-blur-[2px] [&:not([hidden])]:block"></div>
<aside id="app-sidebar" data-sidebar aria-label="Navigasi ruang kerja"
    class="group/sidebar fixed inset-y-0 left-0 z-30 flex h-dvh w-[min(300px,calc(100vw-48px))] -translate-x-full flex-col overflow-y-auto border-r bg-white transition-[width,transform] duration-200 data-[open]:translate-x-0 motion-reduce:transition-none min-[851px]:sticky min-[851px]:top-0 min-[851px]:h-svh min-[851px]:w-64 min-[851px]:translate-x-0">
    <div class="flex h-[72px] shrink-0 items-center justify-between border-b border-zinc-100 px-5" data-sidebar-brand>
        <a href="{{ route('dashboard') }}" aria-label="DevFlow, buka ikhtisar" title="DevFlow, buka ikhtisar" class="inline-flex items-center gap-3 font-semibold tracking-tight text-zinc-950">
            <span aria-hidden="true" class="df-brand-mark grid size-9 place-items-center rounded-xl text-base"><i class="fa-solid fa-code"></i></span>
            <span class="df-sidebar-brand-name font-display text-[17px]">DevFlow<span class="text-[#8f344f]">.</span></span>
        </a>
        <button type="button" data-sidebar-collapse aria-label="Kecilkan sidebar" aria-pressed="false" title="Kecilkan sidebar" class="hidden size-8 shrink-0 place-items-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 min-[851px]:grid">
            <i class="fa-solid fa-angles-left text-xs" data-sidebar-collapse-icon aria-hidden="true"></i>
        </button>
        <button type="button" data-sidebar-close aria-label="Tutup navigasi" class="invisible absolute right-3 top-3 grid size-10 place-items-center rounded-xl text-zinc-500 transition hover:bg-zinc-100 group-data-[open]/sidebar:visible min-[851px]:hidden">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="px-4 pt-4" data-sidebar-workspace>
        <details class="group relative" data-sidebar-workspace-switcher>
            <summary class="df-sidebar-switcher flex min-h-14 cursor-pointer list-none items-center gap-3 px-3 py-2 transition [&::-webkit-details-marker]:hidden" aria-label="Pilih ruang kerja">
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-[#651d32] text-xs font-bold text-white" aria-hidden="true">{{ $isTeamContext ? 'T' : 'D' }}</span>
                <span class="df-sidebar-workspace-copy min-w-0 flex-1 text-left">
                    <span class="block truncate text-[12px] font-semibold text-zinc-900">Ruang kerja {{ $isTeamContext ? 'tim' : 'pribadi' }}</span>
                    <span class="mt-0.5 block text-[11px] text-zinc-500">{{ $isTeamContext ? 'Proyek & kolaborasi' : 'Mode personal' }}</span>
                </span>
                <i class="df-sidebar-workspace-chevron fa-solid fa-chevron-down text-[10px] text-zinc-400 transition-transform group-open:rotate-180" aria-hidden="true"></i>
            </summary>
            <div data-sidebar-workspace-menu class="absolute left-0 right-0 top-[calc(100%+6px)] z-40 rounded-xl border border-zinc-200 bg-white p-1.5 shadow-xl">
                <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-xs {{ $isTeamContext ? 'text-zinc-600 hover:bg-zinc-100' : 'bg-[#f1e2e5] font-semibold text-[#651d32]' }}" @if(!$isTeamContext) aria-current="page" @endif><i class="fa-solid fa-user w-4 text-center" aria-hidden="true"></i>Ruang pribadi</a>
                <a href="{{ route('projects.index') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-xs {{ $isTeamContext ? 'bg-[#f1e2e5] font-semibold text-[#651d32]' : 'text-zinc-600 hover:bg-zinc-100' }}" @if($isTeamContext) aria-current="page" @endif><i class="fa-solid fa-people-group w-4 text-center" aria-hidden="true"></i>Proyek & tim</a>
            </div>
        </details>
    </div>

    <nav class="min-h-0 flex-1 overflow-y-auto px-3 pb-4 pt-1" aria-label="Navigasi utama">
        @foreach ($navGroups as $group)
            <div class="df-sidebar-section">{{ $group['label'] }}</div>
            <div class="df-sidebar-nav-group space-y-1">
                @foreach ($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}" class="df-menu-link flex w-full items-center gap-3" @if ($item['active']) aria-current="page" @endif>
                        <i class="fa-solid {{ $item['icon'] }} shrink-0 text-center" aria-hidden="true"></i>
                        <span class="df-sidebar-label-full min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                        <span class="df-sidebar-label-short" aria-hidden="true">{{ $item['short'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-zinc-100 px-3 pb-3 pt-2">
        <button type="button" data-theme-toggle aria-label="Aktifkan tema gelap" class="df-menu-link flex w-full items-center gap-3 min-[851px]:hidden">
            <i class="fa-solid fa-moon shrink-0 text-center" aria-hidden="true" data-theme-icon></i><span data-theme-label>Tema gelap</span>
        </button>
        <button type="button" data-command-open aria-label="Buka pencarian cepat" title="Pencarian cepat" class="df-menu-link flex w-full items-center gap-3">
            <i class="fa-solid fa-magnifying-glass shrink-0 text-center" aria-hidden="true"></i><span class="df-sidebar-label-full flex-1 text-left">Pencarian cepat</span><span class="df-sidebar-label-short" aria-hidden="true">Cari</span><kbd class="df-sidebar-shortcut rounded border border-zinc-200 bg-white px-1.5 py-0.5 font-mono text-[10px] text-zinc-500">⌘ K</kbd>
        </button>
        <a href="{{ route('notifications.index') }}" aria-label="Pusat notifikasi" class="df-menu-link flex w-full items-center gap-3 min-[851px]:hidden {{ request()->routeIs('notifications.*') ? 'bg-[#f1e2e5] text-[#651d32]' : '' }}">
            <i class="fa-regular fa-bell shrink-0 text-center" aria-hidden="true"></i><span>Notifikasi</span>
        </a>
        <a href="{{ route('settings.index') }}" aria-label="Pengaturan" title="Pengaturan" class="df-menu-link flex w-full items-center gap-3 {{ request()->routeIs('settings.*', 'notifications.*') ? 'bg-[#f1e2e5] text-[#651d32]' : '' }}">
            <i class="fa-solid fa-gear shrink-0 text-center" aria-hidden="true"></i><span class="df-sidebar-label-full">Pengaturan</span><span class="df-sidebar-label-short" aria-hidden="true">Setelan</span>
        </a>
        <div class="df-sidebar-user mt-2 flex items-center gap-3 border-t border-zinc-100 px-2 pt-3" title="{{ auth()->user()->name }}">
            <span aria-hidden="true" class="grid size-9 shrink-0 place-items-center rounded-full bg-[#ead9d3] text-sm font-semibold text-[#651d32]">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="df-sidebar-user-copy min-w-0 flex-1"><p class="truncate text-xs font-semibold text-zinc-900">{{ auth()->user()->name }}</p><p class="truncate text-[11px] text-zinc-500" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" aria-label="Keluar dari DevFlow" title="Keluar" class="grid size-9 place-items-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></button></form>
        </div>
    </div>
</aside>
