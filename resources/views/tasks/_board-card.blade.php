@php($updateUrl = $task->project ? route('tasks.update', [$task->project, $task]) : route('inbox.update', $task))
<article data-task-card draggable="true" data-task-id="{{ $task->id }}" data-task-status="{{ $task->status }}" data-task-update-url="{{ $updateUrl }}" aria-label="Tugas {{ $task->title }}. Seret ke kolom status lain." class="group min-w-0 cursor-grab rounded-xl border border-zinc-200 bg-white p-4 shadow-xs transition hover:border-blue-300 hover:shadow-sm active:cursor-grabbing [&.is-dragging]:opacity-50">
    <div class="flex min-w-0 flex-wrap items-center gap-2">
        @if($task->priority === 'high')
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700"><i class="fa-solid fa-flag" aria-hidden="true"></i>Prioritas tinggi</span>
        @elseif($task->priority === 'low')
            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-medium text-zinc-600">Prioritas rendah</span>
        @endif
        @if($task->due_date && $task->due_date->isPast() && $task->status !== 'done')
            <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700">Terlambat</span>
        @endif
    </div>

    <h3 class="mt-2 min-w-0 break-words text-sm font-semibold leading-5 [overflow-wrap:anywhere] {{ $task->status === 'done' ? 'text-zinc-400 line-through' : 'text-zinc-900' }}"><a draggable="false" href="{{ $task->project ? route('tasks.show', [$task->project, $task]) : route('personal-tasks.show', $task) }}" class="rounded-sm hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-600">{{ $task->title }}</a></h3>

    @if($task->description)
        <p class="mt-2 line-clamp-2 break-words text-xs leading-5 text-zinc-600 [overflow-wrap:anywhere]">{{ $task->description }}</p>
    @endif

    <div class="mt-3 flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-zinc-500">
        <span class="max-w-full truncate">{{ $task->project?->name ?? 'Pribadi' }}</span>
        @if($task->priority === 'normal')<span>Normal</span>@endif
        @if($task->due_date)<span class="inline-flex items-center gap-1"><i class="fa-regular fa-calendar" aria-hidden="true"></i>{{ $task->due_date->format('d/m/Y') }}</span>@endif
    </div>

    <div class="mt-3 flex items-center justify-between gap-2 border-t border-zinc-100 pt-3">
        @if($task->status !== 'done')
            <form method="POST" action="{{ $updateUrl }}">@csrf @method('PATCH')<input type="hidden" name="status" value="done"><button class="inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"><i class="fa-solid fa-check" aria-hidden="true"></i>Selesaikan</button></form>
        @else
            <form method="POST" action="{{ $updateUrl }}">@csrf @method('PATCH')<input type="hidden" name="status" value="todo"><button class="min-h-9 rounded-lg px-2.5 text-xs font-medium text-zinc-600 hover:bg-zinc-100">Buka kembali</button></form>
        @endif
        <a href="{{ $task->project ? route('tasks.show', [$task->project, $task]) : route('personal-tasks.show', $task) }}" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-2 text-xs font-medium text-blue-700 hover:bg-blue-50">Detail <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i></a>
    </div>
</article>
