<div class="mt-3 space-y-2 border-t border-zinc-100 pt-3">
    <form action="{{ route('tasks.update', [$project, $task]) }}" method="POST" data-busy-form>
        @csrf @method('PATCH')
        <label class="sr-only" for="status-{{ $task->id }}">Status tugas {{ $task->title }}</label>
        <select id="status-{{ $task->id }}" name="status" class="min-h-10 w-full min-w-0 rounded-lg border border-zinc-200 bg-white px-2 text-xs font-medium text-zinc-700 outline-none transition hover:border-zinc-300 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10" data-task-status onchange="this.form.requestSubmit()">
            @foreach($statusGroups as $group => $statuses)
                <optgroup label="{{ $group }}">
                    @foreach($statuses as $key => $name)
                        <option value="{{ $key }}" @selected($task->status === $key)>{{ $name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </form>
    <div class="flex items-center justify-end gap-1">
        <a href="{{ route('tasks.show', [$project, $task]) }}" aria-label="Diskusi {{ $task->title }}" title="Detail dan diskusi" class="grid size-9 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950"><i class="fa-regular fa-comment" aria-hidden="true"></i></a>
        <a href="{{ route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'edit' => $task->id]) }}" aria-label="Ubah {{ $task->title }}" title="Ubah tugas" class="grid size-9 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
        <form action="{{ route('tasks.destroy', [$project, $task]) }}" method="POST" data-confirm="Hapus tugas ini? Tindakan ini tidak dapat dibatalkan.">
            @csrf @method('DELETE')
            <button type="submit" class="grid size-9 shrink-0 place-items-center rounded-lg text-zinc-500 hover:bg-red-50 hover:text-red-700" aria-label="Hapus {{ $task->title }}" title="Hapus tugas"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
        </form>
    </div>
</div>
