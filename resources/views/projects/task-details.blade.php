@extends('layouts.app')
@section('title', $task->title)
@section('page-heading', $project->name)
@section('content')
@include('projects._workspace_navigation', ['section' => 'tasks'])
<x-ui.page-heading :title="$task->title" description="Detail tugas dan diskusi tim">
    <x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'edit' => $task->id])" variant="secondary">Ubah tugas</x-ui.button>
    <x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list'])" variant="secondary">Kembali ke tugas</x-ui.button>
</x-ui.page-heading>
<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="min-w-0 space-y-6">
        <x-ui.panel><h2 class="font-semibold">Deskripsi</h2><p class="mt-4 whitespace-pre-wrap break-words text-sm leading-7 text-zinc-600">{{ $task->description ?: 'Belum ada deskripsi.' }}</p>
            @if($task->taskImport?->version)
                <div class="mt-5 border-t border-zinc-100 pt-4 text-sm text-zinc-500">Sumber: <a href="{{ route('projects.show', ['project' => $project, 'section' => 'prd']) }}" class="text-blue-700 hover:underline">{{ $task->taskImport->version->document->title }} · Versi {{ $task->taskImport->version->version_number }}</a></div>
            @endif
        </x-ui.panel>
        @if($task->checklist)
            <x-ui.panel aria-label="Checklist tugas">
                <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-semibold">Checklist</h2><span class="text-sm text-zinc-500">{{ collect($task->checklist)->where('done', true)->count() }}/{{ count($task->checklist) }} selesai</span></div>
                <div class="mt-4 divide-y divide-zinc-100">
                    @foreach($task->checklist as $index => $check)
                        <form action="{{ route('task-checklist.update', [$project, $task, $index]) }}" method="POST" class="flex items-start gap-3 py-3">@csrf @method('PATCH')
                            <input type="hidden" name="done" value="{{ $check['done'] ? '0' : '1' }}">
                            <button type="submit" role="checkbox" aria-checked="{{ $check['done'] ? 'true' : 'false' }}" aria-label="{{ $check['title'] }}" class="grid size-11 shrink-0 place-items-center rounded-lg border border-zinc-200 text-blue-700 hover:bg-blue-50"><i class="{{ $check['done'] ? 'fa-solid fa-square-check' : 'fa-regular fa-square' }}" aria-hidden="true"></i></button>
                            <span class="min-w-0 break-words pt-2.5 text-sm leading-6 {{ $check['done'] ? 'text-zinc-400 line-through' : 'text-zinc-700' }}">{{ $check['title'] }}</span>
                        </form>
                    @endforeach
                </div>
                <p class="mt-3 text-xs leading-5 text-zinc-500">Checklist dan status tugas dapat diperbarui secara terpisah.</p>
            </x-ui.panel>
        @endif
        <x-ui.panel aria-label="Diskusi tugas"><h2 class="text-lg font-semibold">Diskusi</h2>
            <form action="{{ route('task-comments.store', [$project, $task]) }}" method="POST" class="mt-5 space-y-4" data-busy-form>@csrf
                <x-ui.textarea name="body" label="Tulis komentar" required maxlength="4000" rows="3" placeholder="Bagikan perkembangan atau pertanyaan tentang tugas ini…" />
                <x-ui.button data-busy-label="Mengirim…">Kirim komentar</x-ui.button>
            </form>
            <div class="mt-6 divide-y divide-zinc-100">
                @forelse($comments as $comment)
                    <article class="py-5"><div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-semibold">{{ $comment->user->name }}</p><time datetime="{{ $comment->created_at->toIso8601String() }}" class="text-xs text-zinc-500">{{ $comment->created_at->format('d M Y H:i') }}</time></div><p class="mt-3 whitespace-pre-wrap break-words text-sm leading-7 text-zinc-600">{{ $comment->body }}</p>
                        @if($comment->user_id === auth()->id() || $project->workspace->user_id === auth()->id())
                            <form action="{{ route('task-comments.destroy', [$project, $task, $comment]) }}" method="POST" class="mt-3" data-confirm="Hapus komentar ini?">@csrf @method('DELETE')<x-ui.button variant="ghost" aria-label="Hapus komentar {{ $comment->id }}">Hapus komentar</x-ui.button></form>
                        @endif
                    </article>
                @empty<p class="py-5 text-sm text-zinc-500">Belum ada komentar. Mulai diskusi untuk menyamakan konteks tim.</p>@endforelse
            </div>
            {{ $comments->links() }}
        </x-ui.panel>
    </div>
    <x-ui.panel><h2 class="font-semibold">Pengaturan tugas</h2>
        <form action="{{ route('tasks.update', [$project, $task]) }}" method="POST" class="mt-5 space-y-5">@csrf @method('PATCH')
            <x-ui.select name="status" label="Status" :value="$task->status" :options="['backlog' => 'Antrean tugas', 'todo' => 'Belum dikerjakan', 'in_progress' => 'Sedang dikerjakan', 'in_review' => 'Dalam peninjauan', 'blocked' => 'Terhambat', 'done' => 'Selesai']" required />
            <x-ui.select name="priority" label="Prioritas" :value="$task->priority" :options="['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi']" required />
            <x-ui.select name="assignee_id" label="Penanggung jawab" :value="$task->assignee_id" :options="['' => 'Belum ditentukan'] + $participants->pluck('name', 'id')->all()" />
            <x-ui.select name="milestone_id" label="Kelompok pekerjaan" :value="$task->milestone_id" :options="['' => 'Tanpa kelompok'] + $milestones->pluck('title', 'id')->all()" />
            <x-ui.field name="due_date" label="Tenggat" type="date" :value="$task->due_date?->format('Y-m-d')" />
            <x-ui.button class="w-full">Simpan pengaturan tugas</x-ui.button>
        </form>
    </x-ui.panel>
</div>
</div>
@endsection
