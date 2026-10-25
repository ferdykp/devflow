<x-ui.panel id="project-settings">
    <h2 class="mb-5 font-semibold">Detail proyek</h2>
    <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-5" data-busy-form>@csrf @method('PUT')
        <x-ui.field name="name" label="Nama proyek" :value="$project->name" required maxlength="150" />
        <x-ui.textarea name="description" label="Deskripsi" :value="$project->description" rows="3" maxlength="2000" />
        <details @if($errors->hasAny(['goal', 'status', 'priority', 'target_date'])) open @endif class="rounded-lg border border-zinc-200">
            <summary class="cursor-pointer p-4 text-sm font-medium text-zinc-600">Opsi tambahan</summary>
            <div class="space-y-5 border-t border-zinc-100 p-4">
                <x-ui.textarea name="goal" label="Tujuan proyek" :value="$project->goal" rows="2" maxlength="2000" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.select name="status" label="Status" :value="$project->status" :options="['active' => 'Aktif', 'paused' => 'Dijeda', 'archived' => 'Diarsipkan']" required />
                    <x-ui.select name="priority" label="Prioritas" :value="$project->priority" :options="['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi']" required />
                </div>
                <x-ui.field name="target_date" label="Target selesai" type="date" :value="$project->target_date" />
            </div>
        </details>
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-5"><x-ui.button data-busy-label="Menyimpan…">Simpan pengaturan</x-ui.button><x-ui.button :href="route('projects.show', $project)" variant="secondary">Batal</x-ui.button></div>
    </form>
</x-ui.panel>
@if($project->workspace->user_id === auth()->id())
    <x-ui.panel class="mt-6 border-red-200">
        <h2 class="text-sm font-semibold text-red-700">Hapus proyek</h2><p class="mt-2 text-sm leading-6 text-zinc-500">Menghapus proyek beserta tugas, dokumen, dan diskusinya secara permanen. Untuk menyimpannya tanpa dikerjakan, pilih status Diarsipkan pada opsi tambahan.</p>
        <form class="mt-4" action="{{ route('projects.destroy', $project) }}" method="POST" data-confirm="Hapus proyek {{ $project->name }} beserta seluruh tugas, dokumen, dan diskusinya? Tindakan ini permanen dan tidak dapat dibatalkan.">@csrf @method('DELETE')<x-ui.button variant="danger">Hapus proyek</x-ui.button></form>
    </x-ui.panel>
@endif
