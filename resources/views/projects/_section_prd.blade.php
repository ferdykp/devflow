<div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-blue-100 bg-blue-50 p-5">
    <div class="min-w-0"><h2 class="font-semibold text-blue-950">Dari dokumen menjadi tugas</h2><p class="mt-1 text-sm leading-6 text-blue-800">Unggah PRD atau panduan .md untuk membuat tugas berurutan dan checklist. Bisa langsung dibuat atau ditinjau dahulu.</p></div>
    <x-ui.button :href="route('task-imports.create', $project)">Buat tugas dari dokumen</x-ui.button>
</div>
@if($project->taskImports->isNotEmpty())
    <x-ui.panel class="mb-6"><h2 class="font-semibold">Draf dan hasil konversi</h2><ul class="mt-3 divide-y divide-zinc-100">@foreach($project->taskImports as $import)<li><a href="{{ route('task-imports.show', [$project, $import]) }}" class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm"><span class="font-medium text-blue-700">{{ $import->version?->document?->title ?? 'Dokumen tugas' }}<span class="mt-1 block text-xs font-normal text-zinc-500">{{ $import->committed_at ? $import->created_count.' tugas dibuat' : count($import->draft_tasks).' draf untuk ditinjau' }}</span></span><span class="text-xs text-zinc-500">{{ $import->created_at->format('d M Y H:i') }}</span></a></li>@endforeach</ul></x-ui.panel>
@endif
<x-ui.panel>
    <h2 class="mb-3 font-semibold">PRD (opsional)</h2>
    <p class="mb-4 text-xs text-zinc-500">Proyekmu tetap bisa digunakan tanpa PRD.</p>
    <form class="space-y-3" method="POST" action="{{ route('prd.store', $project) }}">
        @csrf
        <x-ui.field name="title" label="Judul dokumen" placeholder="Kebutuhan produk" required/>
        <label for="prd-content" class="block text-sm font-medium">Isi PRD</label>
        <textarea id="prd-content" name="content_markdown" rows="5" required maxlength="200000" placeholder="Tempel isi PRD dalam format Markdown…" class="font-mono w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">{{ old('content_markdown') }}</textarea>
        <x-ui.button>Buat draf</x-ui.button>
    </form>
    @foreach($documents as $document)
        <section class="mt-5 border-t border-zinc-100 pt-4" aria-label="{{ $document->title }}">
            <h3 class="text-sm font-semibold">{{ $document->title }}</h3>
            @foreach($document->versions->sortByDesc('version_number') as $v)
                <details class="mt-3 rounded-lg border border-zinc-200 p-3">
                    <summary class="cursor-pointer text-sm">Versi {{ $v->version_number }} · {{ ['draft' => 'Draf', 'approved' => 'Disetujui', 'superseded' => 'Digantikan'][$v->status] ?? $v->status }}</summary>
                    <pre class="mt-3 whitespace-pre-wrap break-words font-mono text-xs leading-6">{{ $v->content_markdown }}</pre>
                    <form action="{{ route('task-imports.store', $project) }}" method="POST" class="mt-4" data-busy-form>@csrf<input type="hidden" name="version_id" value="{{ $v->id }}"><x-ui.button variant="secondary" data-busy-label="Menyiapkan draf…">Buat tugas dari versi ini</x-ui.button></form>
                    @if($v->status === 'draft')
                        <form action="{{ route('prd.approve', [$project, $document, $v]) }}" method="POST" class="mt-3" data-busy-form data-confirm="Setujui versi PRD ini sebagai acuan? Versi yang disetujui sebelumnya akan digantikan.">
                            @csrf
                            <x-ui.button>Setujui versi ini</x-ui.button>
                        </form>
                    @endif
                </details>
            @endforeach
            <form action="{{ route('prd.revise', [$project, $document]) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <label for="prd-revision-{{ $document->id }}" class="block text-sm font-medium">Isi versi baru</label>
                <textarea id="prd-revision-{{ $document->id }}" name="content_markdown" rows="4" required maxlength="200000" class="font-mono w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">{{ $document->versions->sortByDesc('version_number')->first()?->content_markdown }}</textarea>
                <x-ui.button>Buat versi baru</x-ui.button>
            </form>
        </section>
    @endforeach
</x-ui.panel>
