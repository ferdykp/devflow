@php
    $columns = [
        'backlog' => 'Antrean tugas', 'todo' => 'Belum dikerjakan', 'in_progress' => 'Sedang dikerjakan',
        'in_review' => 'Dalam peninjauan', 'blocked' => 'Terhambat', 'done' => 'Selesai',
    ];
    $view = in_array(request('view'), ['list','board'], true) ? request('view') : 'list';
@endphp
<div class="df-task-page" data-tasks-root>
 <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
  <div><h2 class="text-lg font-semibold tracking-tight">Tugas <span class="ml-1 text-sm font-normal text-zinc-400">{{ $taskPlan ? $tasks->total() : $project->tasks_count }}</span></h2><p class="mt-1 text-sm text-zinc-500">Tulis to-do, kerjakan bersama, lalu tandai selesai.</p></div>
  <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Tampilan tugas">
   <div class="inline-flex rounded-lg bg-zinc-100 p-1">
    @foreach(['list' => ['Daftar', 'fa-list'], 'board' => ['Papan', 'fa-table-columns']] as $key => [$label, $icon])
     <a href="{{ route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => $key, 'plan' => $taskPlan?->id]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-md px-3 text-sm font-medium transition-colors {{ $view === $key ? 'bg-white text-zinc-950 shadow-xs' : 'text-zinc-500 hover:text-zinc-950' }}" @if($view === $key) aria-current="page" @endif><i class="fa-solid {{ $icon }} text-xs" aria-hidden="true"></i>{{ $label }}</a>
    @endforeach
   </div>
   <x-ui.button type="button" variant="secondary" data-task-form-toggle aria-controls="new-task-panel" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Tugas baru</x-ui.button>
  </div>
 </div>
 @if($taskPlan)
 <x-ui.panel class="mb-6"><div class="flex flex-wrap items-start justify-between gap-4"><div class="min-w-0"><p class="text-xs font-medium text-blue-700">To-do dari dokumen</p><h3 class="mt-1 break-words font-semibold">{{ $taskPlan->version?->document?->title ?? 'Rencana proyek' }}</h3><p class="mt-2 text-sm leading-6 text-zinc-500">{{ $taskPlan->created_count }} tugas disusun sesuai urutan dokumen. Buka tugas untuk mencentang checklist atau mengubah detailnya.</p></div><x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list'])" variant="secondary">Semua tugas</x-ui.button></div></x-ui.panel>
 @endif
 <x-ui.panel class="mb-5" :padding="false">
  <form action="{{ route('tasks.store', $project) }}" method="POST" class="flex items-center gap-3 p-3 sm:p-4" aria-label="Tambah to-do cepat" data-busy-form>@csrf
   <span class="hidden text-zinc-400 sm:block" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
   <label for="quick-task-title" class="sr-only">Tambah to-do</label>
   <input id="quick-task-title" name="title" required maxlength="200" autocomplete="off" @if(session('quick_task_added')) autofocus @endif placeholder="Apa yang perlu dikerjakan?" aria-describedby="quick-task-help" class="min-w-0 flex-1 rounded-lg border border-transparent bg-transparent px-2 py-2.5 text-base outline-none placeholder:text-zinc-400 focus:border-blue-300 sm:text-sm">
   <input type="hidden" name="status" value="todo"><input type="hidden" name="quick_add" value="1">
   <x-ui.button data-busy-label="Menambah…">Tambah</x-ui.button>
  </form>
  <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-t border-zinc-100 px-4 py-2.5 sm:px-5"><p id="quick-task-help" class="text-xs text-zinc-500">Ketik judul lalu tekan Enter. Detail bisa ditambahkan nanti.</p><a href="{{ route('task-imports.create', $project) }}" class="inline-flex min-h-11 items-center gap-2 text-xs font-medium text-blue-700 hover:underline"><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Buat tugas dari dokumen</a></div>
 </x-ui.panel>
 <div id="new-task-panel" class="df-composer [border:1px_solid_#e0e3e8] [padding:22px] [border-radius:12px] [background:#fff] [margin-bottom:22px] [&[hidden]]:[display:none]" @if(!$errors->any()) hidden @endif>
  <form action="{{ route('tasks.store',$project) }}" method="POST" class="space-y-4">@csrf
    <div class="df-form-grid max-[650px]:[grid-template-columns:1fr] [display:grid] [grid-template-columns:repeat(2,minmax(0,1fr))] [gap:15px]"><div class="df-form-span [grid-column:1/-1]"><label for="new-task-title" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Nama tugas</label><input id="new-task-title" name="title" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none" maxlength="200" required value="{{ old('title') }}" placeholder="Apa yang perlu dikerjakan?"></div>
    <div><label for="new-task-status" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Status</label><select id="new-task-status" name="status" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">@foreach($columns as $key=>$name)<option value="{{ $key }}" @selected(old('status','todo')===$key)>{{ $name }}</option>@endforeach</select></div>
    <div><label for="new-task-priority" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Prioritas</label><select id="new-task-priority" name="priority" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none">@foreach(['normal'=>'Normal','high'=>'Tinggi','low'=>'Rendah'] as $key=>$name)<option value="{{ $key }}" @selected(old('priority','normal')===$key)>{{ $name }}</option>@endforeach</select></div></div>
    <div><label for="new-task-description" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Deskripsi <span class="df-optional [font-size:11px] [font-weight:400] [color:#89909a]">opsional</span></label><textarea id="new-task-description" name="description" rows="2" maxlength="4000" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none" placeholder="Tambahkan informasi atau kriteria penyelesaian">{{ old('description') }}</textarea></div>
    <div class="df-form-grid max-[650px]:[grid-template-columns:1fr] [display:grid] [grid-template-columns:repeat(2,minmax(0,1fr))] [gap:15px]"><div><label for="new-task-due" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Tenggat <span class="df-optional [font-size:11px] [font-weight:400] [color:#89909a]">opsional</span></label><input id="new-task-due" type="date" name="due_date" value="{{ old('due_date') }}" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none"></div><div><label for="new-task-milestone" class="df-form-label [display:block] [font-size:12px] [font-weight:650] [margin-bottom:6px]">Tahapan <span class="df-optional [font-size:11px] [font-weight:400] [color:#89909a]">opsional</span></label><select id="new-task-milestone" name="milestone_id" class="df-input w-full min-w-0 rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base leading-6 text-zinc-900 shadow-xs outline-none transition-[border-color,box-shadow] duration-150 placeholder:text-zinc-400 hover:border-zinc-400 focus:border-blue-600 focus:ring-3 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-500 aria-invalid:border-red-500 sm:text-sm motion-reduce:transition-none"><option value="">Belum ada tahapan</option>@foreach($milestones as $milestone)<option value="{{ $milestone->id }}" @selected(old('milestone_id')==$milestone->id)>{{ $milestone->title }}</option>@endforeach</select></div></div>
    <x-ui.select name="assignee_id" id="new-task-assignee" label="Penanggung jawab" :options="['' => 'Belum ditentukan'] + $participants->pluck('name', 'id')->all()" />
    <div class="flex items-center gap-3"><x-ui.button>Buat tugas</x-ui.button><x-ui.button type="button" variant="secondary" data-task-form-cancel>Batal</x-ui.button></div>
  </form>
 </div>
@if($editingTask) @include('projects._task_editor') @endif
 @include('projects._task_filters')
 @if($view==='board')
 <div class="df-board-viewport [overflow-x:auto] [overscroll-behavior-x:contain] [padding-bottom:20px]" aria-label="Papan tugas"><div class="df-board [display:grid] [grid-auto-flow:column] [grid-auto-columns:minmax(245px,1fr)] [gap:12px] [align-items:start] [min-width:810px] max-[650px]:[grid-auto-columns:270px]">
  @foreach($columns as $status=>$label)
   @php($items = $tasks->getCollection()->where('status',$status))
   <section class="df-board-column [border:1px_solid_#e7e8eb] [border-radius:11px] [background:#f2f3f5] [min-height:240px] [min-width:0]" aria-label="{{ $label }}" data-task-column="{{ $status }}">
    <header class="df-board-column-heading [&_h3]:[font-size:12px] [&_h3]:[font-weight:700] [&_h3]:[white-space:nowrap] [padding:15px_14px_12px]"><div class="flex items-center gap-2"><span class="df-status-dot [width:9px] [height:9px] [border:2px_solid_#a8afb9] [border-radius:50%] [flex:none] [display:inline-block] df-status-{{ $status }}"></span><h3>{{ $label }}</h3><span class="df-column-count [font-size:10px] [color:#717783] [background:#e5e6ea] [border-radius:6px] [padding:2px_7px]">{{ $items->count() }}</span></div></header>
    <div class="df-column-cards [min-height:175px] [padding:0_9px_10px] [display:flex] [flex-direction:column] [gap:9px] [transition:background_.18s] [&.is-over]:[background:#e6ecff] [&.is-over]:[outline:2px_dashed_#7295e9] [&.is-over]:[outline-offset:-4px] [&.is-over]:[border-radius:8px]" data-task-dropzone="{{ $status }}">
     @foreach($items as $task)
      @include('projects._task_card',['task'=>$task,'columns'=>$columns])
     @endforeach
     @if($items->isEmpty()) <p class="df-column-empty [padding:18px_8px] [color:#969ba4] [text-align:center] [font-size:11px]" data-column-empty>Geser tugas ke sini</p> @endif
    </div>
   </section>
  @endforeach
 </div></div>
 @else
 <div class="df-task-list [border-top:1px_solid_#e8e9ec]" aria-label="Daftar tugas">
  @php($previousGroup = null)
  @forelse($tasks as $task)
   @if(($loop->first || $previousGroup !== $task->milestone_id) && $task->milestone_id !== null)
    <h3 class="mt-5 border-b border-zinc-200 bg-zinc-100/60 px-4 py-3 text-sm font-semibold text-zinc-700">{{ $task->milestone?->title ?? 'Tanpa kelompok' }}</h3>
   @endif
   @php($previousGroup = $task->milestone_id)
   <article class="df-task-row [&>.df-status-dot]:[margin-top:6px] [&.df-task-actions]:[margin-top:0] [&.df-task-description]:[max-width:580px] max-[650px]:[&.df-task-actions]:[width:100%] max-[650px]:[&.df-task-actions]:[margin-left:23px] [display:flex] [align-items:start] [gap:14px] [padding:12px_4px] [border-bottom:1px_solid_#e8e9ec] max-[650px]:[flex-wrap:wrap]">
    @if($task->source_index !== null)<span class="grid size-7 shrink-0 place-items-center rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-500" aria-label="Langkah {{ $task->source_index + 1 }}">{{ $task->source_index + 1 }}</span>@endif
    <form action="{{ route('tasks.update', [$project, $task]) }}" method="POST" class="shrink-0" data-busy-form>@csrf @method('PATCH')
     <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'todo' : 'done' }}">
     <button type="submit" role="checkbox" aria-checked="{{ $task->status === 'done' ? 'true' : 'false' }}" aria-label="Selesai: {{ $task->title }}" class="grid size-11 place-items-center rounded-lg text-xl text-zinc-400 hover:bg-blue-50 hover:text-blue-700 aria-checked:text-blue-700"><i class="{{ $task->status === 'done' ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle' }}" aria-hidden="true"></i></button>
    </form>
    <div class="min-w-0 flex-1"><a href="{{ route('tasks.show', [$project, $task]) }}" draggable="false" class="block df-task-title [font-size:14px] [line-height:1.6] [font-weight:550] [overflow-wrap:anywhere] {{ $task->status==='done'?'line-through text-zinc-400':'' }} hover:text-blue-700">{{ $task->title }}</a><p class="df-task-meta [font-size:11px] [color:#757b86] [margin-top:5px]">{{ $columns[$task->status] ?? $task->status }} · {{ \App\Support\UiLabel::priority($task->priority) }}@if($task->assignee) · {{ $task->assignee->name }}@endif @if($task->checklist) · Checklist {{ collect($task->checklist)->where('done', true)->count() }}/{{ count($task->checklist) }} @endif @if($task->comments_count) · {{ $task->comments_count }} komentar @endif @if($task->due_date) · Tenggat {{ $task->due_date->format('M j') }}@endif</p>@if($task->description)<p class="df-task-description [font-size:12px] [line-height:1.65] [color:#717783] [margin-top:7px] [white-space:pre-line] [overflow-wrap:anywhere] line-clamp-2">{{ $task->description }}</p>@endif</div>
    <details class="relative shrink-0" data-task-menu>
     <summary aria-label="Pilihan tugas {{ $task->title }}" class="grid size-11 cursor-pointer list-none place-items-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900 [&::-webkit-details-marker]:hidden"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary>
     <div data-task-menu-panel class="absolute right-0 top-full z-30 w-72 max-w-[calc(100vw-40px)] overflow-y-auto rounded-xl border border-zinc-200 bg-white p-3 shadow-lg">
      @include('projects._task_actions',['task'=>$task,'columns'=>$columns])
     </div>
    </details>
   </article>
  @empty <div class="df-section-empty [&_p]:[font-size:12px] [&_p]:[color:#7a808b] [&_p]:[margin-top:8px] [padding:70px_20px] [text-align:center] [color:#343b47]"><strong>Belum ada tugas</strong><p>{{ request()->filled('search') || request()->filled('status') || request()->filled('priority') || request()->filled('assignee') ? 'Tidak ada tugas yang cocok. Coba ubah atau reset filter.' : 'Buat tugas pertama untuk mulai merencanakan pekerjaan.' }}</p></div> @endforelse
 </div>
 @endif
 @if($tasks->hasPages())<div class="mt-5">{{ $tasks->links() }}<p class="mt-2 text-xs text-zinc-500">Tugas ditampilkan per halaman. Buka halaman berikutnya untuk melihat tugas lainnya.</p></div>@endif

</div>
