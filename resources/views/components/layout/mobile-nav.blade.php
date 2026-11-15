@php
    $taskActive = request()->routeIs('tasks.index', 'inbox.*', 'personal-tasks.show', 'tasks.show', 'tasks.store', 'tasks.update', 'tasks.destroy');
    $workspaceActive = request()->routeIs('projects.*', 'scope.*', 'github.*');
@endphp
<nav aria-label="Navigasi utama mobile" class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-5 border-t border-zinc-200 bg-white/95 px-2 pt-1.5 pb-[max(0.45rem,env(safe-area-inset-bottom))] shadow-[0_-8px_28px_rgba(36,25,31,.06)] backdrop-blur-xl min-[851px]:hidden">
    <a href="{{ route('my-day.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-semibold {{ request()->routeIs('my-day.*') ? 'text-[#651d32]' : 'text-zinc-500 hover:text-zinc-900' }}" @if(request()->routeIs('my-day.*')) aria-current="page" @endif>
        <i class="fa-solid fa-sun text-[15px]" aria-hidden="true"></i><span>Hari ini</span>
    </a>
    <a href="{{ route('tasks.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-semibold {{ $taskActive ? 'text-[#651d32]' : 'text-zinc-500 hover:text-zinc-900' }}" @if($taskActive) aria-current="page" @endif>
        <i class="fa-solid fa-list-check text-[15px]" aria-hidden="true"></i><span>Tugas</span>
    </a>
    <a href="{{ route('tasks.index', ['quick_add' => 'task']) }}" aria-label="Buat tugas baru" class="grid place-items-center">
        <span class="grid size-11 place-items-center rounded-2xl bg-[#651d32] text-lg text-white shadow-md shadow-[#651d32]/20 transition hover:-translate-y-0.5 hover:bg-[#501528]"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
    </a>
    <a href="{{ route('projects.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-semibold {{ $workspaceActive ? 'text-[#651d32]' : 'text-zinc-500 hover:text-zinc-900' }}" @if($workspaceActive) aria-current="page" @endif>
        <i class="fa-solid fa-layer-group text-[15px]" aria-hidden="true"></i><span>Proyek</span>
    </a>
    <button type="button" data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu lainnya" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 text-[10px] font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900">
        <i class="fa-solid fa-ellipsis text-[15px]" aria-hidden="true"></i><span>Lainnya</span>
    </button>
</nav>
