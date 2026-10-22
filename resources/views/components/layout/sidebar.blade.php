@php
    $menuBase = 'flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 motion-reduce:transition-none';
    $menuIdle = 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950';
    $menuActive = 'bg-blue-50 text-blue-700';
    $projectActive = request()->routeIs('projects.*', 'tasks.*', 'scope.*', 'github.*');
    $navigation = [
        ['route' => 'dashboard', 'label' => 'Ringkasan', 'icon' => 'fa-house', 'active' => request()->routeIs('dashboard')],
        ['route' => 'projects.index', 'label' => 'Proyek', 'icon' => 'fa-folder', 'active' => $projectActive],
        ['route' => 'focus.index', 'label' => 'Fokus Saya', 'icon' => 'fa-bullseye', 'active' => request()->routeIs('focus.*')],
    ];
@endphp
<div data-sidebar-overlay hidden class="fixed inset-0 z-[29] bg-zinc-950/40 [&:not([hidden])]:block"></div>
<aside id="app-sidebar" data-sidebar aria-label="Navigasi ruang kerja"
    class="group/sidebar fixed inset-y-0 left-0 z-30 flex h-dvh w-64 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-transform duration-200 data-[open]:translate-x-0 motion-reduce:transition-none min-[851px]:sticky min-[851px]:top-0 min-[851px]:h-svh min-[851px]:w-52 min-[851px]:translate-x-0">
    <div class="relative flex h-[67px] shrink-0 items-center border-b border-zinc-200 px-5">
        <a href="{{ route('dashboard') }}" aria-label="Ringkasan DevFlow" class="inline-flex items-center gap-2.5 font-semibold tracking-tight text-zinc-950">
            <span aria-hidden="true" class="grid size-8 place-items-center rounded-lg bg-zinc-950 text-xs font-bold text-white">DF</span>
            <span>DevFlow</span>
        </a>
        <button type="button" data-sidebar-close aria-label="Tutup navigasi"
            class="invisible absolute right-3 top-3 grid size-11 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 group-data-[open]/sidebar:visible min-[851px]:hidden">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
    <div class="px-5 pb-2 pt-6 text-xs font-medium text-zinc-500">Ruang kerja</div>
    <nav class="space-y-1 px-3" aria-label="Navigasi utama">
        @foreach($navigation as $item)
            <a href="{{ route($item['route']) }}" class="{{ $menuBase }} {{ $item['active'] ? $menuActive : $menuIdle }}" @if($item['active']) aria-current="page" @endif>
                <i class="fa-solid {{ $item['icon'] }} w-5 shrink-0 text-center text-base" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="mt-auto space-y-1 p-3">
        <button type="button" data-command-open aria-label="Buka pencarian cepat" class="{{ $menuBase }} {{ $menuIdle }}">
            <i class="fa-solid fa-magnifying-glass w-5 shrink-0 text-center" aria-hidden="true"></i>
            <span>Pencarian cepat</span>
        </button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="{{ $menuBase }} {{ $menuIdle }}">
                <i class="fa-solid fa-arrow-right-from-bracket w-5 shrink-0 text-center" aria-hidden="true"></i>
                <span>Keluar</span>
            </button>
        </form>
        <div class="mt-3 flex min-w-0 items-center gap-2.5 border-t border-zinc-200 px-2 pt-4">
            <span aria-hidden="true" class="grid size-8 shrink-0 place-items-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0"><p class="truncate text-sm font-medium text-zinc-800">{{ auth()->user()->name }}</p><p class="truncate text-xs text-zinc-500" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p></div>
        </div>
    </div>
</aside>
