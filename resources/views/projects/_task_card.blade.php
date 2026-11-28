<article data-task-card data-task-id="{{ $task->id }}" data-task-status="{{ $task->status }}" data-task-update-url="{{ route('tasks.update', [$project, $task]) }}" draggable="true" tabindex="0" aria-label="Tugas {{ $task->title }}. Seret ke kolom status lain." class="group relative min-w-0 cursor-grab rounded-xl border border-zinc-200 bg-white p-4 shadow-xs transition hover:border-blue-300 hover:shadow-sm active:cursor-grabbing [&.is-dragging]:z-20 [&.is-dragging]:opacity-95 [&.is-dragging]:shadow-xl [&.is-dragging]:ring-2 [&.is-dragging]:ring-blue-300">
    <div class="flex min-w-0 flex-wrap items-center gap-2">
        <span data-task-status-label @if(!in_array($task->status, ['backlog', 'in_review', 'blocked'], true)) hidden @endif class="inline-flex items-center rounded-full bg-violet-50 px-2 py-1 text-[11px] font-semibold text-violet-700">{{ \App\Support\UiLabel::status($task->status) }}</span>
        @if($task->priority === 'high')
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700"><i class="fa-solid fa-flag" aria-hidden="true"></i>Prioritas tinggi</span>
        @elseif($task->priority === 'low')
            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-medium text-zinc-600">Prioritas rendah</span>
        @endif
        @if($task->due_date && $task->due_date->isPast())
            <span data-task-overdue @if($task->status === 'done') hidden @endif class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700">Terlambat</span>
        @endif
    </div>

    <h3 data-task-status-title class="mt-2 min-w-0 break-words text-sm font-semibold leading-6 {{ $task->status === 'done' ? 'text-zinc-400 line-through' : 'text-zinc-900' }}"><a draggable="false" href="{{ route('tasks.show', [$project, $task]) }}" class="rounded-sm hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-600">{{ $task->title }}</a></h3>

    @if($task->description)
        <p class="mt-2 line-clamp-3 break-words text-sm leading-6 text-zinc-600">{{ $task->description }}</p>
    @endif

    <div class="mt-3 flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-zinc-500">
        <span>{{ \App\Support\UiLabel::priority($task->priority) }}</span>
        @if($task->due_date)<span class="inline-flex items-center gap-1"><i class="fa-regular fa-calendar" aria-hidden="true"></i>{{ $task->due_date->format('d/m/Y') }}</span>@endif
        @if($task->comments_count)<span class="inline-flex items-center gap-1"><i class="fa-regular fa-comment" aria-hidden="true"></i>{{ $task->comments_count }}</span>@endif
        @if($task->checklist)<span>Checklist {{ collect($task->checklist)->where('done', true)->count() }}/{{ count($task->checklist) }}</span>@endif
    </div>

    @if($task->assignee || $task->milestone)
        <div class="mt-3 space-y-1 text-[11px] text-zinc-500">
            @if($task->assignee)<p class="truncate"><i class="fa-regular fa-user mr-1" aria-hidden="true"></i>{{ $task->assignee->name }}</p>@endif
            @if($task->milestone)<p class="truncate font-medium text-blue-700"><i class="fa-solid fa-flag-checkered mr-1" aria-hidden="true"></i>{{ $task->milestone->title }}</p>@endif
        </div>
    @endif

    @include('projects._task_actions', ['task' => $task, 'columns' => $columns])
</article>
