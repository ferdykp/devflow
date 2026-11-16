@extends('layouts.app')
@section('title', $module->title)
@section('page-heading', 'Modul belajar')
@section('content')
<x-ui.page-heading eyebrow="Learning Hub · {{ $path->title }}" :title="$module->title" description="Pelajaran dan latihan yang tersusun dalam modul ini.">
    <a href="{{ route('learning.paths.show', $path) }}" class="text-sm font-medium text-blue-700">Kembali ke jalur</a>
    <button type="button" data-create-open="learning-module-edit" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Edit modul
    </button>
    <button type="button" data-create-open="learning-lesson-add" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah pelajaran
    </button>
</x-ui.page-heading>

<x-ui.panel>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-semibold">Pelajaran</h2>
        @if($module->lessons->isNotEmpty())
            <button type="button" data-create-open="learning-lesson-add" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah pelajaran
            </button>
        @endif
    </div>
    <ul class="mt-3 divide-y divide-zinc-100">
        @forelse($module->lessons as $lesson)
            <li>
                <a href="{{ route('learning.lessons.show', $lesson) }}" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-medium text-blue-700">{{ $lesson->title }}</p>
                        @if($lesson->objectives)
                            <p class="mt-1 text-sm text-zinc-500">{{ $lesson->objectives }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-zinc-500">{{ $lesson->sessions_count }} sesi · {{ $lesson->assessments_count }} penilaian</span>
                </a>
            </li>
        @empty
            <li class="py-4 text-sm text-zinc-500">Belum ada pelajaran. Tambahkan pelajaran untuk mulai menyusun modul ini.</li>
        @endforelse
    </ul>
</x-ui.panel>

<x-ui.create-dialog type="learning-module-edit" size="compact" title="Edit modul" description="Perbarui judul dan deskripsi modul." :errors="$errors->any() && old('_form') === 'edit-module'">
    <form method="POST" action="{{ route('learning.modules.update', [$path, $module]) }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_form" value="edit-module">
        <x-ui.field label="Judul" name="title" :value="$module->title" required />
        <x-ui.textarea label="Deskripsi" name="description" rows="3" :value="$module->description" />
        <div class="flex justify-end pt-2"><x-ui.button>Simpan modul</x-ui.button></div>
    </form>
</x-ui.create-dialog>

<x-ui.create-dialog type="learning-lesson-add" size="compact" title="Tambah pelajaran" description="Tambahkan pelajaran ke modul ini." :errors="$errors->any() && old('_form') === 'add-lesson'">
    <form method="POST" action="{{ route('learning.lessons.store', $path) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_form" value="add-lesson">
        <input type="hidden" name="learning_module_id" value="{{ $module->id }}">
        <x-ui.field label="Judul" name="title" required />
        <x-ui.textarea label="Tujuan belajar" name="objectives" rows="2" />
        <x-ui.field label="URL referensi" name="reference_url" type="url" />
        <div class="flex justify-end pt-2"><x-ui.button>Tambah pelajaran</x-ui.button></div>
    </form>
</x-ui.create-dialog>
@endsection
