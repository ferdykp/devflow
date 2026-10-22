@extends('layouts.app')
@section('title', 'Tinjau draf tugas')
@section('page-heading', $project->name)
@section('content')
@include('projects._workspace_navigation', ['section' => 'prd'])
<x-ui.page-heading :title="$import->committed_at ? 'Tugas berhasil dibuat' : 'Tinjau draf tugas'" :description="($import->version?->document?->title ?? 'Dokumen') . ' · ' . count($import->draft_tasks) . ' draf'">
    <x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list'])" variant="secondary">Buka tugas</x-ui.button>
</x-ui.page-heading>
@if($import->committed_at)
    <x-ui.panel><p class="text-sm leading-6 text-zinc-600">{{ $import->created_count }} tugas telah dibuat dari dokumen ini. Mengirim ulang konfirmasi tidak akan membuat tugas tambahan.</p><ul class="mt-4 divide-y divide-zinc-100">@foreach($tasks as $task)<li><a href="{{ route('tasks.show', [$project, $task]) }}" class="block break-words py-3 text-sm font-medium text-blue-700 hover:underline">{{ $task->title }}</a></li>@endforeach</ul></x-ui.panel>
@else
    <ol class="mb-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-500" aria-label="Langkah konversi"><li>1. Pilih dokumen</li><li class="font-medium text-blue-700" aria-current="step">2. Tinjau draf</li><li>3. Buat tugas</li></ol>
    <p class="mb-5 text-sm leading-6 text-zinc-600">Belum ada tugas yang dibuat. Pilih draf yang diperlukan, periksa judul dan uraian, lalu tentukan penanggung jawab. Hanya draf terpilih yang akan masuk ke status Belum dikerjakan.</p>
    <form action="{{ route('task-imports.update', [$project, $import]) }}" method="POST" class="space-y-5" data-busy-form>@csrf @method('PATCH')
        @foreach($import->draft_tasks as $index => $draft)
            <x-ui.panel :aria-label="'Draf tugas ' . ($index + 1)" data-task-draft>
                <label class="mb-5 flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold"><input type="checkbox" name="selected[]" value="{{ $index }}" @checked(in_array((string) $index, array_map('strval', old('selected', $errors->has('selected') ? [] : array_keys($import->draft_tasks))), true)) class="size-5 rounded border-zinc-300 accent-blue-600">Buat tugas {{ $index + 1 }}</label>
                <fieldset data-draft-fields class="grid min-w-0 gap-4 disabled:opacity-50 lg:grid-cols-2">
                    <div class="lg:col-span-2"><x-ui.field :name="'tasks['.$index.'][group]'" label="Kelompok pekerjaan" :value="$draft['group'] ?? null" maxlength="180" placeholder="Contoh: Persiapan" /></div>
                    <div class="lg:col-span-2"><x-ui.field :name="'tasks['.$index.'][title]'" label="Judul tugas" :value="old('tasks.'.$index.'.title', $draft['title'])" required maxlength="200" /></div>
                    <div class="lg:col-span-2"><x-ui.textarea :name="'tasks['.$index.'][description]'" label="Deskripsi dan kriteria selesai" :value="$draft['description']" rows="3" maxlength="4000" /></div>
                    <div class="lg:col-span-2"><x-ui.textarea :name="'tasks['.$index.'][checklist_text]'" label="Checklist — satu poin per baris" :value="implode(PHP_EOL, array_column($draft['checklist'] ?? [], 'title'))" rows="3" maxlength="25050" /></div>
                    <x-ui.select :name="'tasks['.$index.'][priority]'" label="Prioritas" :options="['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi']" value="normal" required />
                    <x-ui.select :name="'tasks['.$index.'][assignee_id]'" label="Penanggung jawab" :options="['' => 'Belum ditentukan'] + $participants->pluck('name', 'id')->all()" />
                    <x-ui.field :name="'tasks['.$index.'][due_date]'" label="Tenggat" type="date" :value="old('tasks.'.$index.'.due_date')" />
                </fieldset>
                <details class="mt-5 border-t border-zinc-100 pt-4"><summary class="cursor-pointer text-sm text-zinc-500">Lihat teks sumber</summary><p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-zinc-600">{{ $draft['source_text'] }}</p></details>
            </x-ui.panel>
        @endforeach
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-lg"><p class="text-sm text-zinc-600">Perubahan hanya berlaku pada draf tugas ini.</p><div class="flex flex-wrap gap-3"><x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'prd'])" variant="secondary">Kembali ke dokumen</x-ui.button><x-ui.button data-busy-label="Membuat tugas…">Buat tugas terpilih</x-ui.button></div></div>
    </form>
@endif
</div>
@endsection
