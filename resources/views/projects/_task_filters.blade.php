<details class="mb-6 rounded-xl border border-zinc-200 bg-white" @if(request()->filled('search') || request()->filled('status') || request()->filled('priority') || request()->filled('assignee')) open @endif>
<summary class="cursor-pointer px-4 py-3 text-sm font-medium text-zinc-600">Cari dan filter tugas</summary>
<form method="GET" action="{{ route('projects.show', $project) }}" class="grid items-end gap-3 border-t border-zinc-100 p-4 sm:grid-cols-2 xl:grid-cols-[minmax(150px,1.5fr)_1fr_1fr_1fr_auto]" aria-label="Filter tugas">
    <input type="hidden" name="section" value="tasks"><input type="hidden" name="view" value="{{ $view }}">
    @if($taskPlan)<input type="hidden" name="plan" value="{{ $taskPlan->id }}">@endif
    <x-ui.field name="search" id="task-search" label="Cari tugas" :value="request('search')" placeholder="Judul tugas…" maxlength="200" />
    <x-ui.select name="status" id="task-status-filter" label="Status" :value="request('status')" :options="['' => 'Semua status'] + $columns" />
    <x-ui.select name="priority" id="task-priority-filter" label="Prioritas" :value="request('priority')" :options="['' => 'Semua prioritas', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah']" />
    <x-ui.select name="assignee" id="task-assignee-filter" label="Penanggung jawab" :value="request('assignee')" :options="['' => 'Semua anggota', 'me' => 'Ditugaskan ke saya', 'unassigned' => 'Belum ditentukan']" />
    <div class="flex min-h-11 flex-wrap items-center gap-3"><x-ui.button variant="secondary">Terapkan</x-ui.button>@if(request()->filled('search') || request()->filled('status') || request()->filled('priority') || request()->filled('assignee'))<a href="{{ route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => $view, 'plan' => $taskPlan?->id]) }}" class="text-sm text-blue-700 hover:underline">Reset</a>@endif</div>
</form>
</details>
