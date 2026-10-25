<p class="mb-5 text-sm leading-6 text-zinc-600">Dokumen ini berisi {{ count($import->draft_tasks) }} tugas. Pratinjau ditampilkan 25 tugas per halaman. Tombol di bawah membuat seluruh tugas dari dokumen, termasuk halaman lainnya. Detail tugas dapat diubah setelah dibuat.</p>
<div class="space-y-4">
    @foreach($draftPages as $index => $draft)
        <x-ui.panel data-large-draft>
            <p class="text-xs font-medium text-blue-700">Langkah {{ $index + 1 }} @if(!empty($draft['group'])) · {{ $draft['group'] }} @endif</p>
            <h2 class="mt-2 break-words text-base font-semibold">{{ $draft['title'] }}</h2>
            @if($draft['description'])<p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-zinc-600">{{ $draft['description'] }}</p>@endif
            @if(!empty($draft['checklist']))<p class="mt-3 text-xs text-zinc-500">{{ count($draft['checklist']) }} poin checklist</p>@endif
        </x-ui.panel>
    @endforeach
</div>
<div class="my-5">{{ $draftPages->links() }}</div>
<form action="{{ route('task-imports.update', [$project, $import]) }}" method="POST" class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4" data-busy-form>
    @csrf @method('PATCH')
    <input type="hidden" name="confirm_all" value="1">
    <x-ui.button data-busy-label="Membuat seluruh tugas…">Buat semua {{ count($import->draft_tasks) }} tugas</x-ui.button>
    <x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'prd'])" variant="secondary">Kembali ke dokumen</x-ui.button>
</form>
