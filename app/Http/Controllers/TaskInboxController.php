<?php

namespace App\Http\Controllers;

use App\Models\LearningLesson;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskHistoryEntry;
use App\Services\TaskRecurrenceService;
use App\Support\ProjectAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskInboxController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(['all', 'open', 'backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['nullable', Rule::in(['all', 'low', 'normal', 'high'])],
            'project_id' => ['nullable', 'integer'],
            'due' => ['nullable', Rule::in(['any', 'today', 'overdue', 'next_7_days', 'no_due'])],
            'view' => ['nullable', Rule::in(['list', 'board'])],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $defaultStatus = $request->routeIs('tasks.index') ? 'all' : 'open';
        $status = (string) ($filters['status'] ?? $defaultStatus);
        $priority = $filters['priority'] ?? 'all';
        $dueFilter = $filters['due'] ?? 'any';
        $projectId = isset($filters['project_id']) ? (int) $filters['project_id'] : null;
        $timezone = $user->timezone ?: 'Asia/Jakarta';

        if ($projectId !== null) {
            $project = ProjectAccess::find($projectId);
            abort_unless($project->status === 'active', 404);
        }

        $query = Task::query()
            ->where(function ($tasks) use ($user): void {
                $tasks->where(function ($personal) use ($user): void {
                    $personal->where('owner_id', $user->id)->whereNull('project_id');
                })->orWhere(function ($projects) use ($user): void {
                    $projects->whereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id))
                        ->whereHas('project', fn ($project) => $project->where('status', 'active'));
                });
            })
            ->with([
                'project:id,name,workspace_id', 'project.workspace:id,user_id',
                'personalGoal:id,title', 'learningLesson:id,title', 'comments.user:id,name',
                'history' => fn ($history) => $history->with('user:id,name')->latest()->limit(5),
            ])
            ->when($status === 'open', fn ($tasks) => $tasks->whereNot('status', 'done'))
            ->when($status !== 'all' && $status !== 'open', fn ($tasks) => $tasks->where('status', $status))
            ->when($priority !== 'all', fn ($tasks) => $tasks->where('priority', $priority))
            ->when($projectId !== null, fn ($tasks) => $tasks->where('project_id', $projectId))
            ->when($dueFilter === 'today', fn ($tasks) => $tasks->whereDate('due_date', today($timezone)->toDateString()))
            ->when($dueFilter === 'overdue', fn ($tasks) => $tasks->whereNot('status', 'done')->whereDate('due_date', '<', today($timezone)->toDateString()))
            ->when($dueFilter === 'next_7_days', fn ($tasks) => $tasks->whereBetween('due_date', [today($timezone)->toDateString(), today($timezone)->addDays(7)->toDateString()]))
            ->when($dueFilter === 'no_due', fn ($tasks) => $tasks->whereNull('due_date'))
            ->when($search !== '', fn ($tasks) => $tasks->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }));

        $tasks = $query->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
            ->orderBy('due_date')->orderByDesc('created_at')->paginate(25)->withQueryString();

        $goals = $user->personalGoals()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $projects = Project::query()->whereHas('workspace', fn ($workspace) => $workspace->accessibleTo($user->id))
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $lessons = LearningLesson::query()->whereHas('module.path', fn ($path) => $path->where('owner_id', $user->id)->where('status', 'active'))
            ->orderBy('title')->get(['id', 'title']);
        $savedViews = collect(data_get($user->preferences ?? [], 'task_views', []));
        $filterState = [
            'q' => $search ?: null,
            'status' => $status,
            'priority' => $priority,
            'project_id' => $projectId,
            'due' => $dueFilter,
        ];

        return view('tasks.index', compact('tasks', 'goals', 'projects', 'lessons', 'search', 'status', 'priority', 'projectId', 'dueFilter', 'savedViews', 'filterState'));
    }

    public function storeView(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'return_to' => ['required', Rule::in(['tasks', 'inbox'])],
            'q' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(['all', 'open', 'backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['required', Rule::in(['all', 'low', 'normal', 'high'])],
            'project_id' => ['nullable', 'integer'],
            'due' => ['required', Rule::in(['any', 'today', 'overdue', 'next_7_days', 'no_due'])],
        ]);
        $filters = [
            'status' => $data['status'],
            'priority' => $data['priority'],
            'due' => $data['due'],
        ];
        if (! empty($data['q'])) {
            $filters['q'] = $data['q'];
        }
        if (! empty($data['project_id'])) {
            $filters['project_id'] = (int) $data['project_id'];
        }
        if (isset($filters['project_id'])) {
            $project = ProjectAccess::find((int) $filters['project_id']);
            abort_unless($project->status === 'active', 404);
        }

        $user = $request->user();
        $preferences = $user->preferences ?? [];
        $savedViews = data_get($preferences, 'task_views', []);
        $savedViews[(string) Str::uuid()] = ['name' => $data['name'], 'filters' => $filters];
        $preferences['task_views'] = array_slice($savedViews, -12, null, true);
        $user->update(['preferences' => $preferences]);

        return redirect()->route($data['return_to'] === 'tasks' ? 'tasks.index' : 'inbox.index', $filters)
            ->with('success', 'Tampilan tugas disimpan.');
    }

    public function destroyView(Request $request, string $viewKey): RedirectResponse
    {
        $preferences = $request->user()->preferences ?? [];
        $savedViews = data_get($preferences, 'task_views', []);
        abort_unless(isset($savedViews[$viewKey]), 404);
        unset($savedViews[$viewKey]);
        $preferences['task_views'] = $savedViews;
        $request->user()->update(['preferences' => $preferences]);

        return back()->with('success', 'Tampilan tersimpan dihapus.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:4000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'due_date' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:500'],
            'checklist_text' => ['nullable', 'string', 'max:25000'],
            'personal_goal_id' => ['nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
            'learning_lesson_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'recurrence_frequency' => ['nullable', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:12'],
            'recurrence_days' => ['nullable', 'array', 'max:7'],
            'recurrence_days.*' => ['integer', 'between:1,7'],
            'recurrence_until' => ['nullable', 'date'],
        ]);
        $projectId = $data['project_id'] ?? null;
        unset($data['project_id']);
        $tags = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? ''))))));
        $data['checklist'] = $this->checklistFromText($data['checklist_text'] ?? '', []);
        unset($data['checklist_text']);
        $frequency = $data['recurrence_frequency'] ?? 'none';
        if (! empty($data['scheduled_at'])) {
            $data['scheduled_at'] = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['scheduled_at'], $request->user()->timezone ?: 'Asia/Jakarta')->utc();
        }
        unset($data['tags'], $data['recurrence_frequency'], $data['recurrence_interval'], $data['recurrence_days'], $data['recurrence_until']);

        if (! empty($data['learning_lesson_id'])) {
            LearningLesson::query()->whereKey($data['learning_lesson_id'])
                ->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id))->firstOrFail();
        }
        if ($frequency !== 'none' && empty($data['due_date'])) {
            return back()->withErrors(['due_date' => 'Tugas berulang memerlukan tanggal kejadian pertama.'])->withInput();
        }
        if ($frequency === 'weekly' && empty($request->input('recurrence_days'))) {
            return back()->withErrors(['recurrence_days' => 'Pilih setidaknya satu hari untuk pengulangan mingguan.'])->withInput();
        }
        if ($frequency !== 'none') {
            $data['recurrence_rule'] = [
                'frequency' => $frequency,
                'interval' => (int) $request->input('recurrence_interval', 1),
                'days' => $frequency === 'weekly' ? array_values(array_unique(array_map('intval', $request->input('recurrence_days', [])))) : null,
                'until' => $request->input('recurrence_until') ?: null,
                'start_date' => $data['due_date'],
            ];
            $data['recurrence_series_id'] = (string) Str::uuid();
            $data['recurrence_index'] = 1;
        }

        if ($projectId !== null) {
            $project = ProjectAccess::find((int) $projectId);
            abort_unless($project->status === 'active', 404);
            if (! empty($data['personal_goal_id']) || ! empty($data['learning_lesson_id'])) {
                return back()->withErrors(['project_id' => 'Tautan goal dan pelajaran pribadi hanya tersedia untuk tugas personal.'])->withInput();
            }
            unset($data['personal_goal_id']);
            $data['tags'] = $tags;
            $task = $project->tasks()->create($data);
        } else {
            $data['tags'] = $tags;
            $data['status'] = 'backlog';
            $task = $request->user()->personalTasks()->create($data);
        }

        TaskHistoryEntry::query()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'action' => 'created',
            'to_status' => $task->status,
        ]);

        return back()->with('success', 'Tugas berhasil ditambahkan ke daftar tugas.');
    }

    public function update(Request $request, int $task, TaskRecurrenceService $recurrenceService): RedirectResponse|JsonResponse
    {
        $taskModel = $this->findAccessibleTask($request, $task);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'status' => ['sometimes', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high'])],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tags' => ['sometimes', 'nullable', 'string', 'max:500'],
            'checklist_text' => ['sometimes', 'nullable', 'string', 'max:25000'],
        ]);
        if (array_key_exists('checklist_text', $data)) {
            $data['checklist'] = $this->checklistFromText($data['checklist_text'] ?? '', $taskModel->checklist ?? []);
            unset($data['checklist_text']);
        }
        if (array_key_exists('tags', $data)) {
            $data['tags'] = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $data['tags'])))));
        }
        if (array_key_exists('scheduled_at', $data) && $data['scheduled_at'] !== null) {
            $data['scheduled_at'] = CarbonImmutable::parse($data['scheduled_at'], $request->user()->timezone ?: 'Asia/Jakarta')->utc();
        }
        $previousStatus = $taskModel->status;
        if (array_key_exists('status', $data) && $data['status'] !== $previousStatus) {
            $data['completed_at'] = $data['status'] === 'done' ? now() : null;
        }
        $taskModel->update($data);
        if ($previousStatus !== 'done' && $taskModel->status === 'done') {
            $recurrenceService->createNextOccurrence($taskModel, $request->user());
        }

        TaskHistoryEntry::query()->create([
            'task_id' => $taskModel->id,
            'user_id' => $request->user()->id,
            'action' => 'updated',
            'from_status' => $previousStatus,
            'to_status' => $taskModel->status,
            'metadata' => ['changed_fields' => array_keys($data)],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil diperbarui.',
                'task' => [
                    'id' => $taskModel->id,
                    'status' => $taskModel->status,
                ],
            ]);
        }

        return back()->with('success', 'Tugas diperbarui.');
    }

    public function destroy(Request $request, int $task): RedirectResponse
    {
        $taskModel = $request->user()->personalTasks()->findOrFail($task);
        $taskModel->delete();

        return back()->with('success', 'Tugas pribadi dihapus.');
    }

    public function updateChecklist(Request $request, int $task, int $item): RedirectResponse
    {
        $taskModel = $this->findAccessibleTask($request, $task);
        $data = $request->validate(['done' => ['required', 'boolean']]);
        $checklist = $taskModel->checklist ?? [];
        abort_unless(array_key_exists($item, $checklist), 404);
        $checklist[$item]['done'] = (bool) $data['done'];
        $taskModel->update(['checklist' => $checklist]);

        return back()->with('success', 'Checklist diperbarui.');
    }

    public function storeComment(Request $request, int $task): RedirectResponse
    {
        $taskModel = $this->findAccessibleTask($request, $task);
        $data = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $taskModel->comments()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Catatan tugas ditambahkan.');
    }

    public function destroyComment(Request $request, int $task, int $comment): RedirectResponse
    {
        $taskModel = $this->findAccessibleTask($request, $task);
        $commentModel = $taskModel->comments()->findOrFail($comment);
        abort_unless($commentModel->user_id === $request->user()->id || ($taskModel->project?->workspace?->user_id === $request->user()->id), 403);
        $commentModel->delete();

        return back()->with('success', 'Catatan tugas dihapus.');
    }

    public function bulkUpdate(Request $request, TaskRecurrenceService $recurrenceService): RedirectResponse
    {
        $data = $request->validate([
            'task_ids' => ['required', 'array', 'min:1', 'max:100'],
            'task_ids.*' => ['integer', 'distinct'],
            'status' => ['nullable', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high'])],
        ]);
        if (empty($data['status']) && empty($data['priority'])) {
            return back()->withErrors(['status' => 'Pilih status atau prioritas untuk diperbarui.']);
        }
        $tasks = collect($data['task_ids'])->map(fn (int $id) => $this->findAccessibleTask($request, $id));
        foreach ($tasks as $task) {
            $previousStatus = $task->status;
            $updates = array_filter(['status' => $data['status'] ?? null, 'priority' => $data['priority'] ?? null], fn ($value) => $value !== null);
            if (isset($updates['status']) && $updates['status'] !== $previousStatus) {
                $updates['completed_at'] = $updates['status'] === 'done' ? now() : null;
            }
            $task->update($updates);
            if ($previousStatus !== 'done' && $task->status === 'done') {
                $recurrenceService->createNextOccurrence($task, $request->user());
            }
            TaskHistoryEntry::query()->create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'action' => 'bulk_updated',
                'from_status' => $previousStatus,
                'to_status' => $task->status,
                'metadata' => ['changed_fields' => array_keys($updates)],
            ]);
        }

        return back()->with('success', count($tasks).' tugas diperbarui.');
    }

    private function findAccessibleTask(Request $request, int $task): Task
    {
        $userId = $request->user()->id;

        return Task::query()->whereKey($task)
            ->where(function ($query) use ($userId): void {
                $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                    ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId));
            })->firstOrFail();
    }

    /** @param list<array{title: string, done: bool}> $existing */
    private function checklistFromText(string $items, array $existing): array
    {
        $completed = collect($existing)->keyBy(fn (array $item): string => mb_strtolower(trim($item['title'] ?? '')));

        return collect(preg_split('/\R/u', $items) ?: [])
            ->map(fn (string $title): string => trim($title))
            ->filter()
            ->take(50)
            ->map(fn (string $title): array => [
                'title' => $title,
                'done' => (bool) ($completed->get(mb_strtolower($title))['done'] ?? false),
            ])->values()->all();
    }
}
