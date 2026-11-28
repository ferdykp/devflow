@extends('layouts.app')
@section('title', $task->title)
@section('page-heading', 'Detail tugas')
@section('content')
<x-ui.page-heading eyebrow="{{ $task->project?->name ?? 'Tugas pribadi' }}" :title="$task->title" description="Rincian, jadwal, checklist, dan riwayat tugas ini.">
    <x-ui.button :href="route('tasks.index')" variant="secondary"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Kembali ke tugas</x-ui.button>
</x-ui.page-heading>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,.75fr)]">
    <div class="space-y-5">
        <x-ui.panel>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1">Prioritas {{ ['high' => 'tinggi', 'normal' => 'normal', 'low' => 'rendah'][$task->priority] ?? $task->priority }}</span>
                @if($task->due_date)<span class="rounded-full bg-zinc-100 px-2.5 py-1">Tenggat {{ $task->due_date->format('d M Y') }}</span>@endif
            </div>
            <p class="mt-5 whitespace-pre-wrap text-sm leading-6 text-zinc-700">{{ $task->description ?: 'Belum ada catatan untuk tugas ini.' }}</p>
            @if($task->tags)
                <div class="mt-4 flex flex-wrap gap-2">@foreach($task->tags as $tag)<span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs text-zinc-600">{{ $tag }}</span>@endforeach</div>
            @endif
            @if($task->checklist)
                <h2 class="mt-6 border-t border-zinc-100 pt-5 font-semibold">Checklist</h2>
                <ul class="mt-2 space-y-2">
                    @foreach($task->checklist as $index => $item)
                        <li>
                            <form method="POST" action="{{ route('inbox.checklist.update', [$task, $index]) }}" class="flex min-h-11 items-center gap-3" data-async-form data-async-action="checklist">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="done" value="{{ $item['done'] ? 0 : 1 }}">
                                <button aria-label="{{ $item['done'] ? 'Buka kembali' : 'Tandai selesai' }}: {{ $item['title'] }}" class="grid size-6 shrink-0 place-items-center rounded-md border transition-colors {{ $item['done'] ? 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700' : 'border-zinc-300 bg-white text-transparent hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                                    @if($item['done'])<i class="fa-solid fa-check text-[10px]" aria-hidden="true"></i>@endif
                                </button>
                                <span class="text-sm {{ $item['done'] ? 'text-zinc-400 line-through' : '' }}">{{ $item['title'] }}</span>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.panel>

        <x-ui.panel>
            <h2 class="font-semibold">Aktivitas terbaru</h2>
            <ul class="mt-3 space-y-3">
                @forelse($task->history as $entry)
                    <li class="border-l-2 border-blue-200 pl-3"><p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $entry->action)) }}</p><p class="mt-0.5 text-xs text-zinc-500">{{ $entry->user?->name ?? 'DevFlow' }} · {{ $entry->created_at->diffForHumans() }}</p></li>
                @empty
                    <li class="text-sm text-zinc-500">Belum ada riwayat.</li>
                @endforelse
            </ul>
        </x-ui.panel>

        @if($task->comments->isNotEmpty())
            <x-ui.panel>
                <h2 class="font-semibold">Diskusi</h2>
                <div class="mt-3 space-y-3">
                    @foreach($task->comments as $comment)
                        <div class="rounded-xl bg-zinc-50 p-3"><p class="text-xs font-semibold">{{ $comment->user->name }} <span class="font-normal text-zinc-500">· {{ $comment->created_at->diffForHumans() }}</span></p><p class="mt-1 whitespace-pre-wrap text-sm">{{ $comment->body }}</p></div>
                    @endforeach
                </div>
            </x-ui.panel>
        @endif
    </div>

    <aside class="space-y-4">
        <x-ui.panel>
            <h2 class="font-semibold">Konteks</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div><dt class="text-xs text-zinc-500">Proyek</dt><dd>{{ $task->project?->name ?? 'Pribadi' }}</dd></div>
                @if($task->personalGoal)<div><dt class="text-xs text-zinc-500">Goal</dt><dd><a class="font-medium text-blue-700 underline-offset-4 hover:underline" href="{{ route('goals.show', $task->personalGoal) }}">{{ $task->personalGoal->title }}</a></dd></div>@endif
                @if($task->learningLesson)<div><dt class="text-xs text-zinc-500">Pelajaran</dt><dd><a class="font-medium text-blue-700 underline-offset-4 hover:underline" href="{{ route('learning.lessons.show', $task->learningLesson) }}">{{ $task->learningLesson->title }}</a></dd></div>@endif
                @if($task->assignee)<div><dt class="text-xs text-zinc-500">Penanggung jawab</dt><dd>{{ $task->assignee->name }}</dd></div>@endif
            </dl>
        </x-ui.panel>

        <x-ui.panel>
            <h2 class="font-semibold">Tindakan</h2>
            <div class="mt-3 space-y-2 rounded-xl border border-zinc-200 bg-zinc-50/70 p-2">
                @if($task->status !== 'done')
                    <form method="POST" action="{{ $task->project ? route('tasks.update', [$task->project, $task]) : route('inbox.update', $task) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="done">
                        <button class="flex min-h-11 w-full items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-sm font-semibold text-emerald-800 transition-colors hover:border-emerald-300 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"><i class="fa-solid fa-check" aria-hidden="true"></i>Tandai selesai</button>
                    </form>
                @endif
                <details class="group">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-sm font-semibold text-zinc-700 transition-colors hover:border-zinc-300 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 [&::-webkit-details-marker]:hidden">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-zinc-500" aria-hidden="true"></i>Edit tugas</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 transition-transform group-open:rotate-180" aria-hidden="true"></i>
                    </summary>
                    <form method="POST" action="{{ $task->project ? route('tasks.update', [$task->project, $task]) : route('inbox.update', $task) }}" class="mt-2 space-y-3 rounded-lg border border-zinc-200 bg-white p-3">
                        @csrf
                        @method('PATCH')
                        <x-ui.field label="Judul" name="title" :value="$task->title" required />
                        <x-ui.textarea label="Deskripsi" name="description" rows="4" :value="$task->description" />
                        <x-ui.select label="Status" name="status" :options="$statusGroups" :value="$task->status" />
                        <x-ui.select label="Prioritas" name="priority" :options="['high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah']" :value="$task->priority" />
                        <x-ui.field label="Tenggat" name="due_date" type="date" :value="$task->due_date?->format('Y-m-d')" />
                        <x-ui.field label="Jadwal" name="scheduled_at" type="datetime-local" :value="$task->scheduled_at?->format('Y-m-d\\TH:i')" />
                        <div class="flex justify-end pt-1"><x-ui.button>Simpan perubahan</x-ui.button></div>
                    </form>
                </details>
                @if($task->project)
                    <x-ui.button :href="route('projects.show', $task->project)" variant="secondary" class="w-full justify-start"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Buka proyek</x-ui.button>
                @else
                    <form method="POST" action="{{ route('inbox.destroy', $task) }}" data-confirm="Hapus tugas ini? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" class="w-full justify-start"><i class="fa-solid fa-trash-can" aria-hidden="true"></i>Hapus tugas</x-ui.button>
                    </form>
                @endif
            </div>
        </x-ui.panel>

        <x-ui.panel>
            <h2 class="font-semibold">Tambah komentar</h2>
            <form method="POST" action="{{ $task->project ? route('task-comments.store', [$task->project, $task]) : route('inbox.comments.store', $task) }}" class="mt-3 space-y-3">
                @csrf
                <x-ui.textarea label="Komentar" name="body" rows="3" required />
                <x-ui.button class="w-full"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Kirim komentar</x-ui.button>
            </form>
        </x-ui.panel>
    </aside>
</div>
@endsection
