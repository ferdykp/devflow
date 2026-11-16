@extends('layouts.app')
@section('title', $path->title)
@section('page-heading', 'Jalur belajar')
@section('content')
<x-ui.page-heading eyebrow="Learning Hub · {{ $path->subject }}" :title="$path->title" :description="$path->description ?: 'Jalur belajar yang kamu susun sendiri.'">
    <a href="{{ route('learning.index') }}" class="text-sm font-medium text-blue-700">Learning Hub</a>
    <button type="button" data-create-open="learning-path-edit" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Edit jalur
    </button>
    <button type="button" data-create-open="learning-module" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah modul
    </button>
</x-ui.page-heading>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,.75fr)]">
    <section class="min-w-0 space-y-4">
        @forelse($path->modules as $module)
            <x-ui.panel>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('learning.modules.show', [$path, $module]) }}" class="text-lg font-semibold text-zinc-900 hover:text-blue-700">{{ $loop->iteration }}. {{ $module->title }}</a>
                        @if($module->description)
                            <p class="mt-1 text-sm text-zinc-600">{{ $module->description }}</p>
                        @endif
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs">{{ $module->lessons->count() }} pelajaran</span>
                </div>
                <ul class="mt-4 divide-y divide-zinc-100">
                    @forelse($module->lessons as $lesson)
                        <li>
                            <a href="{{ route('learning.lessons.show', $lesson) }}" class="flex min-h-12 items-center justify-between gap-3 py-2">
                                <span class="min-w-0 truncate text-sm text-blue-700">{{ $lesson->title }}</span>
                                <span class="shrink-0 text-xs text-zinc-500">{{ ['not_started' => 'Belum mulai', 'content_viewed' => 'Dibaca', 'practice_completed' => 'Latihan', 'competency_demonstrated' => 'Terbukti'][$lesson->status] ?? $lesson->status }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-zinc-500">Belum ada pelajaran di modul ini.</li>
                    @endforelse
                </ul>
            </x-ui.panel>
        @empty
            <x-ui.panel>
                <h2 class="font-semibold">Belum ada modul</h2>
                <p class="mt-1 text-sm text-zinc-600">Tambahkan modul untuk mulai menyusun jalur belajar.</p>
                <button type="button" data-create-open="learning-module" class="mt-4 inline-flex min-h-11 items-center gap-2 rounded-lg bg-blue-50 px-3 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>Tambah modul
                </button>
            </x-ui.panel>
        @endforelse
    </section>

    <aside class="space-y-4">
        <x-ui.panel>
            <h2 class="font-semibold">Progres jalur</h2>
            <p class="mt-2 text-sm text-zinc-600">{{ $path->modules->sum(fn ($module) => $module->lessons->whereIn('status', ['practice_completed', 'competency_demonstrated'])->count()) }} pelajaran sudah sampai tahap latihan atau bukti kompetensi.</p>
        </x-ui.panel>
    </aside>
</div>

<x-ui.create-dialog type="learning-path-edit" size="compact" title="Edit jalur belajar" description="Perbarui informasi dan status jalur belajarmu." :errors="$errors->any() && old('_form') === 'edit-path'">
    <form method="POST" action="{{ route('learning.paths.update', $path) }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_form" value="edit-path">
        <x-ui.field label="Judul" name="title" :value="$path->title" required />
        <x-ui.field label="Bidang" name="subject" :value="$path->subject" required />
        <x-ui.textarea label="Deskripsi" name="description" rows="3" :value="$path->description" />
        <x-ui.select label="Status" name="status" :options="['active' => 'Aktif', 'paused' => 'Dijeda', 'completed' => 'Selesai', 'archived' => 'Arsip']" :value="$path->status" />
        <div class="flex justify-end pt-2">
            <x-ui.button>Simpan jalur</x-ui.button>
        </div>
    </form>
</x-ui.create-dialog>

<x-ui.create-dialog type="learning-module" size="compact" title="Tambah modul" description="Tambahkan bagian baru ke jalur belajar ini." :errors="$errors->any() && old('_form') === 'add-module'">
    <form method="POST" action="{{ route('learning.modules.store', $path) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_form" value="add-module">
        <x-ui.field label="Nama modul" name="title" required />
        <x-ui.textarea label="Deskripsi" name="description" rows="3" />
        <div class="flex justify-end pt-2">
            <x-ui.button>Tambah modul</x-ui.button>
        </div>
    </form>
</x-ui.create-dialog>
@endsection
