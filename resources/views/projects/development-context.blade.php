<div>
    <!-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison -->
</div>
@extends('layouts.app')
@section('title', 'Konteks pengembangan')
@section('page-heading', $project->name)
@section('content')
@include('projects._workspace_navigation', ['section' => 'tasks'])
<x-ui.page-heading title="Siapkan Konteks Pengembangan" description="Tinjau sumber yang akan disertakan, lalu salin ringkasan atau prompt. DevFlow hanya memakai data yang tersimpan di proyek ini.">
    <x-ui.button :href="route('tasks.show', [$project, $task])" variant="secondary">Kembali ke tugas</x-ui.button>
</x-ui.page-heading>

<div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(260px,0.8fr)_minmax(0,1.4fr)]" data-context-builder>
    <section class="space-y-4" aria-labelledby="context-sources-title">
        <x-ui.panel>
            <h2 id="context-sources-title" class="text-base font-semibold text-zinc-900">Pilih sumber konteks</h2>
            <p class="mt-1 text-sm leading-6 text-zinc-600">Teks yang menyerupai kunci rahasia disamarkan sebelum tampil di preview.</p>
            <div class="mt-4 space-y-2">
                @foreach($sections as $section)
                    <label class="flex min-h-12 items-start gap-3 rounded-xl border border-zinc-200 p-3 hover:bg-zinc-50">
                        <input type="checkbox" class="mt-1 size-4 shrink-0 rounded border-zinc-300 text-blue-600 focus:ring-blue-600" data-context-section checked value="{{ $section['key'] }}">
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-zinc-800">{{ $section['label'] }}</span>
                            <a href="{{ $section['source_url'] }}" class="mt-0.5 inline-flex min-h-8 items-center text-xs text-blue-700 hover:underline">Sumber: {{ $section['source'] }}</a>
                        </span>
                    </label>
                @endforeach
            </div>
        </x-ui.panel>
        <x-ui.panel>
            <h2 class="text-sm font-semibold text-zinc-900">Batasan konteks</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-6 text-zinc-600">
                <li>Tidak membaca file lokal atau staged changes.</li>
                <li>Tidak mengirim data ke penyedia AI.</li>
                <li>Ringkasan dan prompt adalah bahan kerja, bukan keputusan scope.</li>
            </ul>
        </x-ui.panel>
    </section>

    <section class="min-w-0" aria-labelledby="context-preview-title">
        <x-ui.panel>
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="context-preview-title" class="text-base font-semibold text-zinc-900">Preview</h2>
                    <p class="mt-1 text-sm text-zinc-600">Pilih format dan periksa hasil sebelum menyalin.</p>
                </div>
                <label class="w-full sm:w-64">
                    <span class="mb-1 block text-xs font-medium text-zinc-700">Format</span>
                    <select class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base text-zinc-900 sm:text-sm" data-context-format>
                        <option value="developer">Ringkasan developer</option>
                        <option value="assistant">Prompt coding assistant</option>
                        <option value="team">Konteks diskusi tim</option>
                    </select>
                </label>
            </div>
            <script type="application/json" data-context-sections>@json($sections)</script>
            <textarea class="mt-4 min-h-[22rem] w-full resize-y rounded-xl border border-zinc-300 bg-zinc-50 p-4 font-mono text-sm leading-6 text-zinc-800 focus:border-blue-600 focus:outline-none focus:ring-3 focus:ring-blue-600/15" rows="16" readonly data-context-preview aria-label="Preview konteks pengembangan">{{ collect($sections)->map(fn ($section) => '## '.$section['label']."\n".$section['content'])->implode("\n\n") }}</textarea>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <x-ui.button type="button" data-context-copy><i class="fa-regular fa-copy" aria-hidden="true"></i>Salin konteks</x-ui.button>
                <span class="text-sm text-zinc-600" aria-live="polite" data-context-status>Preview siap ditinjau.</span>
            </div>
        </x-ui.panel>
    </section>
</div>
@endsection
