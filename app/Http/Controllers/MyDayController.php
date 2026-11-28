<?php

namespace App\Http\Controllers;

use App\Models\DailyReflection;
use App\Models\FocusItem;
use App\Models\Routine;
use App\Models\Task;
use App\Models\TaskHistoryEntry;
use App\Services\TaskRecurrenceService;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MyDayController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $timezone = $user->timezone ?: 'Asia/Jakarta';
        $date = $request->date('date') ?? now($timezone)->startOfDay();
        $localDate = $date->copy()->timezone($timezone)->toDateString();
        $dayStartUtc = $date->copy()->timezone($timezone)->startOfDay()->utc();
        $dayEndUtc = $date->copy()->timezone($timezone)->endOfDay()->utc();
        $focusedIds = FocusItem::query()->where('user_id', $user->id)->whereDate('focus_date', $localDate)->pluck('task_id');
        $tasks = Task::query()->where(function ($query) use ($user): void {
            $query->where(fn ($personal) => $personal->where('owner_id', $user->id)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id));
        })->whereNot('status', 'done')->where(function ($query) use ($localDate, $focusedIds, $dayStartUtc, $dayEndUtc): void {
            $query->whereDate('due_date', $localDate)->orWhereIn('id', $focusedIds)
                ->orWhereBetween('scheduled_at', [$dayStartUtc, $dayEndUtc]);
        })->with(['project:id,name', 'personalGoal:id,title'])->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
            ->orderBy('scheduled_at')->orderBy('due_date')->get();
        $routines = $user->routines()->where('status', 'active')->with(['checkIns' => fn ($query) => $query->whereDate('occurred_on', $localDate)])
            ->orderBy('preferred_time')->get()->filter(fn (Routine $routine) => $this->routineApplies($routine, $date));
        $events = $user->calendarEvents()->where('status', 'planned')->whereBetween('starts_at', [$dayStartUtc, $dayEndUtc])->orderBy('starts_at')->get();
        $sessions = $user->learningSessions()->whereDate('occurred_on', $localDate)->with('lesson')->latest()->get();
        $reflection = $user->dailyReflections()->whereDate('reflection_date', $localDate)->first();
        $completedToday = Task::query()->whereNotNull('completed_at')->whereDate('completed_at', $localDate)
            ->where(fn ($query) => $query->where('owner_id', $user->id)->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id)))->count();
        $focusOrder = data_get($user->preferences ?? [], 'focus_order', []);
        $priorityTasks = $tasks->whereIn('id', $focusedIds)
            ->sortBy(fn (Task $task): int => (($position = array_search($task->id, $focusOrder, true)) === false) ? PHP_INT_MAX : $position)
            ->take(3)->values();
        $unscheduledTasks = $tasks->whereNull('scheduled_at')->values();
        $linkedTaskIds = $events->pluck('task_id')->filter()->all();
        $plannedMinutes = $events->sum(fn ($event) => max(0, (int) $event->starts_at->diffInMinutes($event->ends_at)));
        $agendaItems = collect();
        foreach ($events as $event) {
            $agendaItems->push([
                'kind' => 'event',
                'model' => $event,
                'starts_at' => $event->starts_at->timezone($timezone),
                'ends_at' => $event->ends_at->timezone($timezone),
            ]);
        }
        foreach ($tasks->whereNotNull('scheduled_at')->whereNotIn('id', $linkedTaskIds) as $task) {
            $plannedMinutes += (int) ($task->estimated_minutes ?: 30);
            $agendaItems->push([
                'kind' => 'task',
                'model' => $task,
                'starts_at' => $task->scheduled_at->timezone($timezone),
                'ends_at' => $task->scheduled_at->timezone($timezone)->addMinutes($task->estimated_minutes ?: 30),
            ]);
        }
        $agendaItems = $agendaItems->sortBy('starts_at')->values();

        $workSchedule = data_get($user->preferences ?? [], 'work_schedule', ['start' => '09:00', 'end' => '17:00']);
        $availableMinutes = max(0, ((int) substr($workSchedule['end'] ?? '17:00', 0, 2) * 60 + (int) substr($workSchedule['end'] ?? '17:00', 3, 2)) - ((int) substr($workSchedule['start'] ?? '09:00', 0, 2) * 60 + (int) substr($workSchedule['start'] ?? '09:00', 3, 2)));
        $plannedLoadPercent = $availableMinutes > 0 ? min(100, (int) round($plannedMinutes / $availableMinutes * 100)) : 0;

        return view('my-day.index', compact('tasks', 'priorityTasks', 'unscheduledTasks', 'agendaItems', 'routines', 'events', 'sessions', 'reflection', 'completedToday', 'date', 'localDate', 'timezone', 'plannedMinutes', 'availableMinutes', 'plannedLoadPercent'));
    }

    public function complete(Request $request, int $task, TaskRecurrenceService $recurrenceService): RedirectResponse|JsonResponse
    {
        $taskModel = $this->accessibleTask($request, $task);
        $previousStatus = $taskModel->status;
        $taskModel->update(['status' => 'done', 'completed_at' => now()]);
        $recurrenceService->createNextOccurrence($taskModel, $request->user());
        TaskHistoryEntry::query()->create(['task_id' => $taskModel->id, 'user_id' => $request->user()->id, 'action' => 'completed', 'from_status' => $previousStatus, 'to_status' => 'done']);
        FocusItem::query()->where('user_id', $request->user()->id)->where('task_id', $taskModel->id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tugas selesai.', 'task' => ['id' => $taskModel->id, 'status' => 'done']]);
        }

        return back()->with('success', 'Tugas selesai.');
    }

    public function defer(Request $request, int $task): RedirectResponse
    {
        $taskModel = $this->accessibleTask($request, $task);
        $data = $request->validate(['due_date' => ['required', 'date', 'after_or_equal:today']]);
        $taskModel->update(['due_date' => $data['due_date'], 'scheduled_at' => null]);

        return back()->with('success', 'Tugas dijadwalkan ulang.');
    }

    public function reflect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reflection' => ['nullable', 'string', 'max:4000'],
            'reflection_date' => ['required', 'date'],
        ]);
        DailyReflection::query()->updateOrCreate(
            ['owner_id' => $request->user()->id, 'reflection_date' => $data['reflection_date']],
            ['reflection' => $data['reflection'] ?? null],
        );

        return back()->with('success', 'Refleksi harian tersimpan.');
    }

    public function routineCheckIn(Request $request, int $routine): RedirectResponse|JsonResponse
    {
        $routineModel = $request->user()->routines()->findOrFail($routine);
        $data = $request->validate([
            'status' => ['required', Rule::in(['completed', 'skipped'])],
            'activity_level' => ['nullable', Rule::in(['minimum', 'normal', 'extended'])],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        DB::transaction(function () use ($routineModel, $data, $request): void {
            $lockedRoutine = $request->user()->routines()->lockForUpdate()->findOrFail($routineModel->id);
            $lockedRoutine->checkIns()->updateOrCreate(
                ['occurred_on' => today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString()],
                $data,
            );
        });

        $message = $data['status'] === 'completed' ? 'Rutinitas dicatat selesai.' : 'Rutinitas dilewati untuk hari ini.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'status' => $data['status']]);
        }

        return back()->with('success', $message);
    }

    private function accessibleTask(Request $request, int $taskId): Task
    {
        $userId = $request->user()->id;

        return Task::query()->whereKey($taskId)->whereNot('status', 'done')
            ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))
            ->firstOrFail();
    }

    private function routineApplies(Routine $routine, CarbonInterface $date): bool
    {
        return $routine->frequency === 'daily'
            || in_array((int) $date->format('N'), $routine->days_of_week ?? [], true);
    }
}
