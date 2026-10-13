<div class="df-task-actions [display:flex] [align-items:center] [gap:8px] [justify-content:space-between] [margin-top:12px]">
 <form action="{{ route('tasks.update',[$project,$task]) }}" method="POST">@csrf @method('PATCH')
  <label class="sr-only" for="status-{{ $task->id }}">Status for {{ $task->title }}</label>
  <select id="status-{{ $task->id }}" name="status" class="df-task-status [border:1px_solid_#e2e4e9] [border-radius:7px] [padding:7px_21px_7px_8px] [font-size:11px] [color:#4c535f] [background:#fff] [max-width:155px]" data-task-status onchange="this.form.requestKirim()">@foreach($columns as $key=>$name)<option value="{{ $key }}" @selected($task->status===$key)>{{ $name }}</option>@endforeach</select>
 </form>
 <form action="{{ route('tasks.destroy',[$project,$task]) }}" method="POST" data-confirm="Hapus this task? This can't be undone.">@csrf @method('DELETE')
  <button type="submit" class="df-icon-button [min-height:32px] [min-width:32px] [border-radius:7px] [color:#89909a] hover:[color:#b91c1c] hover:[background:#fff1f1]" aria-label="Hapus {{ $task->title }}" title="Hapus task"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
 </form>
</div>