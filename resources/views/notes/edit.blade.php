@extends('layouts.app')
@section('title', 'Edit '.$note->title)
@section('page-heading', 'Editor catatan')
@section('content')
<x-ui.page-heading eyebrow="Perpustakaan catatan" title="{{ $note->title ?: 'Catatan baru' }}" description="Perubahan tersimpan otomatis saat kamu berhenti mengetik."><a href="{{ route('personal-notes.index') }}" class="text-sm font-medium text-blue-700">← Kembali ke catatan</a></x-ui.page-heading>
<form method="POST" action="{{ route('personal-notes.update', $note) }}" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(280px,340px)]" data-note-autosave data-note-id="{{ $note->id }}" data-note-owner="{{ auth()->id() }}">
    @csrf @method('PATCH')
    <main class="space-y-4">
        <x-ui.panel>
            <x-ui.field label="Judul" name="title" :value="$note->title" required data-note-field />
            <div class="mt-4">
                <x-ui.textarea label="Isi catatan (Markdown didukung)" name="content" rows="24" :value="$note->content" placeholder="Mulai menulis…" data-note-field />
                <p class="mt-2 text-xs text-zinc-500">Tersimpan otomatis setelah kamu selesai mengetik. Teks tetap ada jika penyimpanan gagal.</p>
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3" data-note-recovery hidden>
                    <p class="text-sm font-medium text-amber-900">Ada draf lokal dari tab ini yang belum tersimpan.</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" data-note-recovery-restore class="min-h-10 rounded-lg bg-amber-900 px-3 text-sm font-semibold text-white">Pulihkan draf</button>
                        <button type="button" data-note-recovery-discard class="min-h-10 rounded-lg border border-amber-300 px-3 text-sm font-medium text-amber-900">Buang draf</button>
                    </div>
                </div>
            </div>
        </x-ui.panel>
        <x-ui.button>Simpan catatan</x-ui.button>
    </main>
    <aside class="space-y-4">
        <x-ui.panel>
            <h2 class="font-semibold">Organisasi</h2>
            <div class="mt-3 space-y-3">
                <x-ui.field label="Koleksi" name="collection" :value="$note->collection" placeholder="Contoh: Belajar SQL" data-note-field />
                <x-ui.field label="Tag, pisahkan dengan koma" name="tags_input" :value="implode(', ', $note->tags ?? [])" data-note-field />
                <input type="hidden" name="is_pinned" value="0">
                <label class="flex min-h-10 items-center gap-2 text-sm"><input type="checkbox" name="is_pinned" value="1" @checked($note->is_pinned) data-note-field>Sematkan catatan</label>
            </div>
        </x-ui.panel>
        <x-ui.panel>
            <h2 class="font-semibold">Tautkan ke</h2>
            <p class="mt-1 text-xs text-zinc-500">Hubungkan catatan dengan konteks kerja.</p>
            <div class="mt-3 space-y-3">
                <x-ui.select label="Goal" name="personal_goal_id" :options="['' => 'Tanpa goal'] + $goals->mapWithKeys(fn ($item) => [$item->id => $item->title])->all()" :value="$note->personal_goal_id" data-note-field />
                <x-ui.select label="Tugas" name="task_id" :options="['' => 'Tanpa tugas'] + $tasks->mapWithKeys(fn ($item) => [$item->id => $item->title])->all()" :value="$note->task_id" data-note-field />
                <x-ui.select label="Proyek" name="project_id" :options="['' => 'Tanpa proyek'] + $projects->mapWithKeys(fn ($item) => [$item->id => $item->name])->all()" :value="$note->project_id" data-note-field />
                <x-ui.select label="Pelajaran" name="learning_lesson_id" :options="['' => 'Tanpa pelajaran'] + $lessons->mapWithKeys(fn ($item) => [$item->id => $item->title])->all()" :value="$note->learning_lesson_id" data-note-field />
                <p class="text-xs text-zinc-500" data-note-save-status aria-live="polite">Perubahan tersimpan otomatis.</p>
                <button type="button" data-note-save-retry disabled class="min-h-10 rounded-lg border border-zinc-300 px-3 text-sm font-medium text-zinc-700 enabled:hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-50">Coba simpan lagi</button>
            </div>
        </x-ui.panel>
    </aside>
</form>
@endsection
