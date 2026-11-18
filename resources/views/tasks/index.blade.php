@extends('layouts.app')
@php($taskView = request('view') === 'list' ? 'list' : 'board')
@section('title', 'Tugas')
@section('page-heading', 'Tugas')
@section('content')
<x-ui.page-heading title="Tugas" description="Kelola tugas personal dan proyek dalam satu daftar. Pilih tugas terbuka atau tampilkan semua status."><button type="button" data-create-open="task" class="df-button-primary inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah tugas</button></x-ui.page-heading>
<div class="min-w-0" data-tasks-root>
    <section class="min-w-0 space-y-4">
        <div class="df-panel mb-4 rounded-2xl border p-4 sm:p-5">
        <nav aria-label="Filter cepat tugas" class="mb-4 flex flex-wrap items-center gap-2 border-b border-[var(--df-edge)] pb-4">
            <span class="mr-1 text-xs font-semibold text-[var(--df-muted)]">Tampilkan:</span>
            <a href="{{ route('tasks.index', array_merge(request()->except(['status', 'page']), ['status' => 'open'])) }}" @if($status === 'open') aria-current="page" @endif class="inline-flex min-h-9 items-center rounded-lg px-3 text-xs font-semibold transition {{ $status === 'open' ? 'bg-[var(--df-action)] text-white' : 'border border-[var(--df-edge)] bg-[var(--df-paper)] text-[var(--df-muted)] hover:bg-[var(--df-action-soft)] hover:text-[var(--df-action)]' }}">Belum selesai</a>
            <a href="{{ route('tasks.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" @if($status === 'all') aria-current="page" @endif class="inline-flex min-h-9 items-center rounded-lg px-3 text-xs font-semibold transition {{ $status === 'all' ? 'bg-[var(--df-action)] text-white' : 'border border-[var(--df-edge)] bg-[var(--df-paper)] text-[var(--df-muted)] hover:bg-[var(--df-action-soft)] hover:text-[var(--df-action)]' }}">Semua status</a>
        </nav>
        <form method="GET" action="{{ route('tasks.index') }}" class="mb-4 border-b border-[var(--df-edge)] pb-4">
            <div class="grid min-w-0 gap-3 sm:grid-cols-[minmax(180px,1fr)_minmax(150px,220px)_auto] sm:items-end">
                <div class="min-w-0"><label for="task-search" class="mb-1.5 block text-xs font-semibold text-[var(--df-muted)]">Cari tugas</label><div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400" aria-hidden="true"></i><input id="task-search" name="q" value="{{ $search }}" placeholder="Judul atau deskripsi" class="df-input w-full rounded-xl border px-9 text-sm"></div></div>
                <div class="min-w-0"><label for="filter-status" class="mb-1.5 block text-xs font-semibold text-[var(--df-muted)]">Status</label><select id="filter-status" name="status" class="df-input w-full rounded-xl border px-3 text-sm"><option value="open" @selected($status === 'open')>Belum selesai</option><option value="all" @selected($status === 'all')>Semua status</option>@foreach(['backlog' => 'Backlog', 'todo' => 'Akan dikerjakan', 'in_progress' => 'Sedang dikerjakan', 'in_review' => 'Ditinjau', 'blocked' => 'Terhambat', 'done' => 'Selesai'] as $key => $label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach</select></div>
                <button class="df-button-secondary inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>Terapkan</button>
            </div>
            <details class="mt-3" @if($priority !== 'all' || $dueFilter !== 'any' || $projectId) open @endif>
                <summary class="inline-flex min-h-10 cursor-pointer list-none items-center gap-2 text-xs font-semibold text-[var(--df-muted)] marker:hidden"><i class="fa-solid fa-sliders text-[var(--df-action)]" aria-hidden="true"></i>Filter lanjutan <i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i>@if($priority !== 'all' || $dueFilter !== 'any' || $projectId)<span class="rounded-full bg-[var(--df-action-soft)] px-2 py-0.5 text-[var(--df-action)]">Aktif</span>@endif</summary>
                <div class="grid gap-3 border-l-2 border-[var(--df-action-soft)] py-3 pl-4 sm:grid-cols-3">
                    <div class="min-w-0"><label for="filter-priority" class="mb-1.5 block text-xs font-medium text-[var(--df-muted)]">Prioritas</label><select id="filter-priority" name="priority" class="df-input w-full rounded-xl border px-3 text-sm"><option value="all">Semua</option>@foreach(['high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah'] as $key => $label)<option value="{{ $key }}" @selected($priority === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div class="min-w-0"><label for="filter-due" class="mb-1.5 block text-xs font-medium text-[var(--df-muted)]">Tenggat</label><select id="filter-due" name="due" class="df-input w-full rounded-xl border px-3 text-sm">@foreach(['any' => 'Kapan saja', 'today' => 'Hari ini', 'overdue' => 'Terlambat', 'next_7_days' => '7 hari ke depan', 'no_due' => 'Tanpa tenggat'] as $key => $label)<option value="{{ $key }}" @selected($dueFilter === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div class="min-w-0"><label for="filter-project" class="mb-1.5 block text-xs font-medium text-[var(--df-muted)]">Proyek</label><select id="filter-project" name="project_id" class="df-input w-full rounded-xl border px-3 text-sm"><option value="">Semua proyek</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected($projectId === $project->id)>{{ $project->name }}</option>@endforeach</select></div>
                    <div class="flex flex-wrap items-center gap-2 sm:col-span-3"><button class="df-button-primary inline-flex min-h-10 items-center gap-2 rounded-lg px-4 text-sm font-semibold"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Terapkan semua filter</button><a href="{{ route('tasks.index', ['view' => request('view')]) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-medium text-[var(--df-muted)] hover:bg-[var(--df-canvas)]">Reset filter</a></div>
                </div>
            </details>
            <input type="hidden" name="view" value="{{ $taskView }}">
        </form>
        <div class="flex flex-wrap items-center gap-2"><span class="mr-1 text-xs font-semibold text-[var(--df-muted)]">Tampilan tersimpan</span>@forelse($savedViews as $viewKey => $savedView)<div class="inline-flex items-center overflow-hidden rounded-lg border border-[var(--df-edge)] bg-[var(--df-paper)]"><a class="px-3 py-2 text-xs font-medium hover:bg-[var(--df-action-soft)] hover:text-[var(--df-action)]" href="{{ route('tasks.index', array_merge($savedView['filters'] ?? [], ['view' => request('view')])) }}">{{ $savedView['name'] ?? 'Tampilan' }}</a><form method="POST" action="{{ route('task-views.destroy', $viewKey) }}">@csrf @method('DELETE')<button class="grid size-8 place-items-center border-l border-[var(--df-edge)] text-zinc-400 hover:bg-red-50 hover:text-red-600" aria-label="Hapus tampilan {{ $savedView['name'] ?? '' }}"><i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i></button></form></div>@empty<span class="text-xs text-[var(--df-muted)]">Simpan kombinasi filter yang sering dipakai.</span>@endforelse<button type="button" data-create-open="task-view" class="ml-auto inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-semibold text-[var(--df-action)] hover:bg-[var(--df-action-soft)]"><i class="fa-solid fa-bookmark" aria-hidden="true"></i>Simpan tampilan ini</button></div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs text-zinc-500">{{ $tasks->total() }} tugas · {{ $taskView === 'board' ? 'Papan status' : 'Daftar tugas' }}</p><a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => $taskView === 'board' ? 'list' : 'board'])) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700"><i class="fa-solid {{ $taskView === 'board' ? 'fa-list' : 'fa-table-columns' }}" aria-hidden="true"></i>{{ $taskView === 'board' ? 'Daftar' : 'Papan' }}</a></div>
        @if($tasks->isNotEmpty() && $taskView === 'list')<form id="bulk-update" method="POST" action="{{ route('inbox.bulk-update') }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-zinc-200 bg-white p-3">@csrf<span class="mr-1 text-xs font-medium text-zinc-600">Pilih tugas dari daftar:</span><x-ui.select label="Status massal" name="status" :options="['' => 'Pertahankan status', 'todo' => 'To do', 'in_progress' => 'Sedang dikerjakan', 'blocked' => 'Terhambat', 'done' => 'Selesai']" /><x-ui.select label="Prioritas massal" name="priority" :options="['' => 'Pertahankan prioritas', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah']" /><button class="min-h-11 rounded-lg bg-zinc-900 px-3 text-xs font-semibold text-white">Perbarui yang dipilih</button></form>@endif
        @if($taskView === 'board')
            <p class="text-xs text-zinc-500">Seret kartu untuk memperbarui status, atau ubah status lewat pilihan pada kartu. Kolom menyesuaikan lebar layar.</p>
            <div class="min-w-0 pb-3" aria-label="Papan tugas, kolom status menyesuaikan lebar layar">
                <div class="grid min-w-0 grid-cols-1 items-start gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-3 2xl:grid-cols-4 min-[2200px]:grid-cols-6">
                @foreach(['backlog' => 'Backlog', 'todo' => 'Akan dikerjakan', 'in_progress' => 'Sedang dikerjakan', 'in_review' => 'Ditinjau', 'blocked' => 'Terhambat', 'done' => 'Selesai'] as $statusKey => $statusLabel)
                    @php($statusTasks = $tasks->getCollection()->where('status', $statusKey))
                    <section data-task-dropzone="{{ $statusKey }}" class="min-h-52 space-y-3 rounded-2xl border border-dashed border-zinc-300 bg-zinc-100/80 p-3 transition-colors [&.is-over]:bg-blue-50 [&.is-over]:outline-2 [&.is-over]:outline-dashed [&.is-over]:outline-blue-400">
                        <header class="flex items-center justify-between gap-3 px-1 py-1"><h2 class="text-sm font-semibold text-zinc-800">{{ $statusLabel }}</h2><span title="Tugas pada halaman ini" class="rounded-full bg-white px-2 py-0.5 text-xs font-medium tabular-nums text-zinc-500">{{ $statusTasks->count() }}</span></header>
                        @foreach($statusTasks as $task)
                            @include('tasks._board-card', ['task' => $task])
                        @endforeach
                        @if($statusTasks->isEmpty())<p class="rounded-xl border border-dashed border-zinc-300 px-3 py-5 text-center text-xs text-zinc-500">Lepas tugas di sini</p>@endif
                    </section>
                @endforeach
                </div>
            </div>
        @else
            <div class="space-y-3">
                @forelse($tasks as $task)
                    @include('tasks._card', ['task' => $task])
                @empty
                    <x-ui.panel><h2 class="font-semibold">{{ $search ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}</h2><p class="mt-2 text-sm text-zinc-600">{{ $search ? 'Coba kata kunci lain atau ubah filter status.' : 'Tambahkan tugas baru atau sesuaikan filter yang dipilih.' }}</p>@unless($search)<button type="button" data-create-open="task" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-blue-50 px-3 text-sm font-semibold text-blue-700 hover:bg-blue-100"><i class="fa-solid fa-plus mr-2" aria-hidden="true"></i>Tambah tugas</button>@endunless</x-ui.panel>
                @endforelse
            </div>
        @endif
        {{ $tasks->links() }}
    </section>
</div>

<x-ui.create-dialog type="task-view" size="compact" title="Simpan tampilan" description="Gunakan lagi filter ini kapan saja." :errors="$errors->hasAny(['name', 'return_to', 'status', 'priority', 'due'])"><form method="POST" action="{{ route('task-views.store') }}" class="space-y-4">@csrf<input type="hidden" name="return_to" value="tasks"><input type="hidden" name="q" value="{{ $filterState['q'] }}"><input type="hidden" name="status" value="{{ $filterState['status'] }}"><input type="hidden" name="priority" value="{{ $filterState['priority'] }}"><input type="hidden" name="project_id" value="{{ $filterState['project_id'] }}"><input type="hidden" name="due" value="{{ $filterState['due'] }}"><x-ui.field label="Nama tampilan" name="name" required placeholder="Contoh: Tugas minggu ini" /><x-ui.button class="w-full"><i class="fa-solid fa-bookmark" aria-hidden="true"></i>Simpan tampilan</x-ui.button></form></x-ui.create-dialog>

<x-ui.create-dialog type="task" title="Tambah tugas" description="Tugas baru akan muncul di daftar ini. Atur status, jadwal, atau proyeknya kapan saja." :errors="$errors->hasAny(['title', 'description', 'priority', 'due_date', 'scheduled_at', 'estimated_minutes', 'category', 'tags', 'checklist_text', 'personal_goal_id', 'learning_lesson_id', 'recurrence_frequency', 'recurrence_interval', 'recurrence_days', 'recurrence_until'])"><form method="POST" action="{{ route('inbox.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-modal-form>@csrf
            <x-ui.field label="Judul" name="title" required placeholder="Apa yang perlu dilakukan?" wrapper-class="sm:col-span-2" />
            <x-ui.textarea label="Catatan" name="description" rows="3" wrapper-class="sm:col-span-2" />
            <x-ui.select label="Prioritas" name="priority" :options="['normal' => 'Normal', 'high' => 'Tinggi', 'low' => 'Rendah']" value="normal" />
            <x-ui.field label="Tenggat" name="due_date" type="date" />
            <x-ui.field label="Waktu rencana (opsional)" name="scheduled_at" type="datetime-local" />
            <x-ui.field label="Estimasi waktu (menit)" name="estimated_minutes" type="number" min="1" max="1440" />
            <x-ui.field label="Kategori" name="category" placeholder="Contoh: belajar, kerja" />
            <x-ui.field label="Tag, pisahkan dengan koma" name="tags" placeholder="ide, penting" />
            <x-ui.textarea wrapper-class="sm:col-span-2" label="Checklist, satu poin per baris" name="checklist_text" rows="3" placeholder="Contoh:\nTinjau kebutuhan\nTulis langkah berikutnya" />
            <x-ui.select label="Tautkan ke goal personal" name="personal_goal_id" :options="['' => 'Tanpa goal'] + $goals->mapWithKeys(fn ($goal) => [$goal->id => $goal->title])->all()" />
            <x-ui.select label="Tautkan ke pelajaran" name="learning_lesson_id" :options="['' => 'Tanpa pelajaran'] + $lessons->mapWithKeys(fn ($lesson) => [$lesson->id => $lesson->title])->all()" />
            <x-ui.select wrapper-class="sm:col-span-2" label="Tambahkan ke proyek (opsional)" name="project_id" :options="['' => 'Tugas pribadi'] + $projects->mapWithKeys(fn ($project) => [$project->id => $project->name])->all()" />
            <details class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 sm:col-span-2"><summary class="min-h-10 cursor-pointer py-2 text-sm font-medium">Ulangi tugas</summary><div class="mt-3 space-y-3"><x-ui.select label="Frekuensi" name="recurrence_frequency" :options="['none' => 'Tidak berulang', 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']" value="none" /><x-ui.field label="Setiap (interval)" name="recurrence_interval" type="number" min="1" max="12" value="1" /><x-ui.field label="Sampai tanggal" name="recurrence_until" type="date" /><div class="grid grid-cols-4 gap-2 text-xs">@foreach([1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'] as $number => $label)<label class="flex min-h-10 items-center gap-1 rounded-lg border border-zinc-200 px-2"><input type="checkbox" name="recurrence_days[]" value="{{ $number }}">{{ $label }}</label>@endforeach</div></div></details>
            <x-ui.button class="w-full sm:col-span-2"><i class="fa-solid fa-plus" aria-hidden="true"></i> Simpan tugas</x-ui.button>
        </form></x-ui.create-dialog>
@endsection
