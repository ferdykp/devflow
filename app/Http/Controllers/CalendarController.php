<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarEventException;
use App\Models\LearningLesson;
use App\Models\LearningSession;
use App\Models\Task;
use App\Services\Calendar\CalendarPlanner;
use App\Services\TaskRecurrenceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarPlanner $planner): View
    {
        $timezone = $request->user()->timezone ?: 'Asia/Jakarta';
        $filters = $request->validate(['date' => ['nullable', 'date'], 'view' => ['nullable', Rule::in(['day', 'week', 'month'])]]);
        $date = CarbonImmutable::parse($filters['date'] ?? now($timezone)->toDateString(), $timezone);
        $viewType = $filters['view'] ?? 'week';
        $periodStart = match ($viewType) {
            'day' => $date->startOfDay(),
            'month' => $date->startOfMonth()->startOfWeek(),
            default => $date->startOfWeek(),
        };
        $periodEnd = match ($viewType) {
            'day' => $date->endOfDay(),
            'month' => $date->endOfMonth()->endOfWeek(),
            default => $date->endOfWeek(),
        };
        $events = $request->user()->calendarEvents()->where('status', '!=', 'cancelled')
            ->where('starts_at', '<=', $periodEnd->utc())->with(['exceptions', 'task.project', 'routine', 'lesson'])->get();
        $occurrences = $planner->occurrences($events, $periodStart, $periodEnd, $timezone);
        $dayGroups = [];
        for ($day = $periodStart->startOfDay(); $day <= $periodEnd->startOfDay(); $day = $day->addDay()) {
            $dayGroups[$day->toDateString()] = array_values(array_filter($occurrences, fn (array $occurrence) => $occurrence['starts_at']->toDateString() === $day->toDateString()));
        }
        $tasks = Task::query()->whereNot('status', 'done')->where(fn ($query) => $query->where('owner_id', $request->user()->id)->whereNull('project_id')->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($request->user()->id)))
            ->with('project:id,name')->orderBy('due_date')->limit(100)->get();
        $goals = $request->user()->personalGoals()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $routines = $request->user()->routines()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $lessons = LearningLesson::query()->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id)->where('status', 'active'))->orderBy('title')->get(['id', 'learning_module_id', 'title']);
        $previousDate = match ($viewType) {
            'day' => $date->subDay(),
            'month' => $date->subMonth(),
            default => $date->subWeek(),
        };
        $nextDate = match ($viewType) {
            'day' => $date->addDay(),
            'month' => $date->addMonth(),
            default => $date->addWeek(),
        };
        $conflicts = $this->conflictGroups($occurrences);
        $timelineHours = range(0, 23);
        $unscheduledTasks = $tasks->whereNull('scheduled_at')->values();
        $workSchedule = data_get($request->user()->preferences ?? [], 'work_schedule', ['days' => [1, 2, 3, 4, 5], 'start' => '09:00', 'end' => '17:00']);

        return view('calendar.index', compact('date', 'viewType', 'periodStart', 'periodEnd', 'dayGroups', 'tasks', 'goals', 'routines', 'lessons', 'previousDate', 'nextDate', 'timezone', 'conflicts', 'timelineHours', 'unscheduledTasks', 'workSchedule'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'starts_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\\TH:i', 'after:starts_at'],
            'timezone' => ['required', 'timezone'],
            'all_day' => ['sometimes', 'boolean'],
            'is_flexible' => ['sometimes', 'boolean'],
            'task_id' => ['nullable', 'integer'],
            'routine_id' => ['nullable', 'integer', Rule::exists('routines', 'id')->where('owner_id', $request->user()->id)],
            'learning_lesson_id' => ['nullable', 'integer'],
            'personal_goal_id' => ['nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
            'recurrence_frequency' => ['nullable', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:12'],
            'recurrence_days' => ['nullable', 'array', 'max:7'],
            'recurrence_days.*' => ['integer', 'between:1,7'],
            'recurrence_until' => ['nullable', 'date'],
        ]);
        $task = null;
        if (! empty($data['task_id'])) {
            $task = $this->accessibleTask($request, (int) $data['task_id']);
            if (($data['recurrence_frequency'] ?? 'none') !== 'none') {
                return back()->withErrors(['task_id' => 'Tugas berulang memakai aturan pengulangan tugasnya sendiri.'])->withInput();
            }
        }
        if (! empty($data['learning_lesson_id'])) {
            LearningLesson::query()->whereKey($data['learning_lesson_id'])
                ->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id))->firstOrFail();
        }
        $startsAt = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['starts_at'], $data['timezone'])->utc();
        $endsAt = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['ends_at'], $data['timezone'])->utc();
        $frequency = $data['recurrence_frequency'] ?? 'none';
        if ($frequency !== 'none' && $task) {
            return back()->withErrors(['task_id' => 'Agenda berulang tidak dapat menautkan satu tugas yang sama ke setiap kejadian.'])->withInput();
        }
        if ($frequency !== 'none' && ! empty($data['recurrence_until']) && $data['recurrence_until'] < substr($data['starts_at'], 0, 10)) {
            return back()->withErrors(['recurrence_until' => 'Tanggal akhir pengulangan harus sama atau setelah tanggal mulai.'])->withInput();
        }
        $recurrenceRule = $frequency === 'none' ? null : [
            'frequency' => $frequency,
            'interval' => $data['recurrence_interval'] ?? 1,
            'days' => $frequency === 'weekly' ? array_values(array_unique($data['recurrence_days'] ?? [$startsAt->setTimezone($data['timezone'])->dayOfWeekIso])) : null,
            'until' => $data['recurrence_until'] ?? null,
        ];
        $eventData = [
            'owner_id' => $request->user()->id,
            'task_id' => $task?->id,
            'routine_id' => $data['routine_id'] ?? null,
            'learning_lesson_id' => $data['learning_lesson_id'] ?? null,
            'personal_goal_id' => $data['personal_goal_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'event_date' => ($data['all_day'] ?? false) ? $startsAt->setTimezone($data['timezone'])->toDateString() : null,
            'timezone' => $data['timezone'],
            'all_day' => $request->boolean('all_day'),
            'is_flexible' => $request->boolean('is_flexible'),
            'status' => 'planned',
            'recurrence_rule' => $recurrenceRule,
            'recurrence_series_id' => $recurrenceRule ? (string) Str::uuid() : null,
        ];
        $event = DB::transaction(function () use ($eventData, $task, $startsAt): CalendarEvent {
            $event = CalendarEvent::query()->create($eventData);
            if ($task) {
                $task->update(['scheduled_at' => $startsAt]);
            }

            return $event;
        });
        $conflicts = CalendarEvent::query()->where('owner_id', $request->user()->id)->where('id', '!=', $event->id)
            ->where('status', 'planned')->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agenda berhasil dijadwalkan.',
                'event' => ['id' => $event->id, 'title' => $event->title],
            ], 201);
        }

        return redirect()->route('calendar.index', ['date' => $startsAt->setTimezone($data['timezone'])->toDateString()])
            ->with('success', 'Agenda tersimpan. '.($conflicts ? "Ada {$conflicts} blok waktu yang bertumpuk untuk ditinjau." : 'Tidak ada konflik waktu terdeteksi.'));
    }

    public function rescheduleOccurrence(Request $request, int $event): RedirectResponse|JsonResponse
    {
        $eventModel = $request->user()->calendarEvents()->with('exceptions')->findOrFail($event);
        $data = $request->validate([
            'original_start' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\\TH:i', 'after:starts_at'],
            'timezone' => ['required', 'timezone'],
        ]);
        $originalStart = CarbonImmutable::parse($data['original_start'])->utc();
        $newStart = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['starts_at'], $data['timezone'])->utc();
        $newEnd = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['ends_at'], $data['timezone'])->utc();
        CalendarEventException::query()->updateOrCreate(
            ['calendar_event_id' => $eventModel->id, 'original_starts_at' => $originalStart],
            ['action' => 'rescheduled', 'new_starts_at' => $newStart, 'new_ends_at' => $newEnd],
        );
        if ($eventModel->task && $eventModel->recurrence_rule === null) {
            $eventModel->task->update(['scheduled_at' => $newStart]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Jadwal diperbarui.', 'event' => ['id' => $eventModel->id]]);
        }

        return back()->with('success', 'Kejadian ini dijadwalkan ulang. Pengulangan lain tidak berubah.');
    }

    public function update(Request $request, int $event): RedirectResponse|JsonResponse
    {
        $eventModel = $request->user()->calendarEvents()->with('task')->findOrFail($event);
        abort_if($eventModel->recurrence_rule !== null, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'starts_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\\TH:i', 'after:starts_at'],
            'timezone' => ['required', 'timezone'],
            'all_day' => ['sometimes', 'boolean'],
            'is_flexible' => ['sometimes', 'boolean'],
        ]);
        $startsAt = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['starts_at'], $data['timezone'])->utc();
        $endsAt = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['ends_at'], $data['timezone'])->utc();
        DB::transaction(function () use ($eventModel, $data, $startsAt, $endsAt): void {
            $eventModel->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'timezone' => $data['timezone'],
                'all_day' => (bool) ($data['all_day'] ?? false),
                'event_date' => ($data['all_day'] ?? false) ? $startsAt->setTimezone($data['timezone'])->toDateString() : null,
                'is_flexible' => (bool) ($data['is_flexible'] ?? false),
            ]);
            if ($eventModel->task) {
                $eventModel->task->update(['scheduled_at' => $startsAt]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agenda diperbarui.', 'event' => ['id' => $eventModel->id]]);
        }

        return back()->with('success', 'Agenda diperbarui.');
    }

    public function cancelOccurrence(Request $request, int $event): RedirectResponse
    {
        $eventModel = $request->user()->calendarEvents()->findOrFail($event);
        $data = $request->validate(['original_start' => ['nullable', 'date']]);
        if ($eventModel->recurrence_rule !== null && isset($data['original_start'])) {
            CalendarEventException::query()->updateOrCreate(
                ['calendar_event_id' => $eventModel->id, 'original_starts_at' => CarbonImmutable::parse($data['original_start'])->utc()],
                ['action' => 'cancelled', 'new_starts_at' => null, 'new_ends_at' => null],
            );
        } else {
            $eventModel->update(['status' => 'cancelled']);
            if ($eventModel->task) {
                $eventModel->task->update(['scheduled_at' => null]);
            }
        }

        return back()->with('success', 'Agenda dibatalkan.');
    }

    public function complete(Request $request, int $event, TaskRecurrenceService $recurrenceService): RedirectResponse
    {
        $eventModel = $request->user()->calendarEvents()->with(['task', 'lesson.module.path', 'routine', 'exceptions'])->findOrFail($event);
        abort_unless($eventModel->status === 'planned', 404);
        $data = $request->validate([
            'actual_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'original_start' => [$eventModel->recurrence_rule ? 'required' : 'nullable', 'date'],
        ]);
        DB::transaction(function () use ($request, $eventModel, $data): void {
            $originalStart = isset($data['original_start']) ? CarbonImmutable::parse($data['original_start'])->utc() : null;
            $exception = $originalStart ? $eventModel->exceptions->first(fn ($item) => $item->original_starts_at->utc()->getTimestamp() === $originalStart->getTimestamp()) : null;
            abort_if(in_array($exception?->action, ['cancelled', 'completed'], true), 404);
            $plannedStart = $exception?->new_starts_at ?? $originalStart ?? $eventModel->starts_at;
            $plannedEnd = $exception?->new_ends_at ?? $plannedStart->addSeconds(max(0, $eventModel->ends_at->getTimestamp() - $eventModel->starts_at->getTimestamp()));
            $plannedMinutes = max(1, (int) ceil($plannedStart->diffInMinutes($plannedEnd)));
            $actualMinutes = min(1440, (int) ($data['actual_minutes'] ?? $plannedMinutes));
            if ($eventModel->recurrence_rule && $originalStart) {
                CalendarEventException::query()->updateOrCreate(
                    ['calendar_event_id' => $eventModel->id, 'original_starts_at' => $originalStart],
                    [
                        'action' => 'completed',
                        'new_starts_at' => $exception?->new_starts_at,
                        'new_ends_at' => $exception?->new_ends_at,
                        'actual_duration_minutes' => $actualMinutes,
                    ],
                );
            } else {
                $eventModel->update(['status' => 'completed', 'actual_duration_minutes' => $actualMinutes]);
            }
            if ($eventModel->task && $eventModel->task->status !== 'done') {
                $previous = $eventModel->task->status;
                $eventModel->task->update(['status' => 'done', 'completed_at' => now()]);
                $eventModel->task->history()->create(['user_id' => $request->user()->id, 'action' => 'completed', 'from_status' => $previous, 'to_status' => 'done']);
                $recurrenceService->createNextOccurrence($eventModel->task, $request->user());
            }
            $activityDate = $plannedStart->setTimezone($eventModel->timezone)->toDateString();
            if ($eventModel->routine) {
                $eventModel->routine->checkIns()->updateOrCreate(['occurred_on' => $activityDate], ['status' => 'completed', 'activity_level' => 'normal']);
            }
            if ($eventModel->lesson) {
                LearningSession::query()->create([
                    'owner_id' => $request->user()->id,
                    'learning_path_id' => $eventModel->lesson->module->learning_path_id,
                    'learning_lesson_id' => $eventModel->learning_lesson_id,
                    'calendar_event_id' => $eventModel->id,
                    'session_type' => 'study',
                    'planned_minutes' => min(1440, $plannedMinutes),
                    'duration_minutes' => $actualMinutes,
                    'occurred_on' => $activityDate,
                ]);
            }
        });

        return back()->with('success', 'Agenda selesai dan aktivitas terkait diperbarui.');
    }

    private function accessibleTask(Request $request, int $taskId): Task
    {
        $userId = $request->user()->id;

        return Task::query()->whereKey($taskId)->whereNot('status', 'done')
            ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))->firstOrFail();
    }

    /** @param list<array{event: CalendarEvent, original_start: CarbonImmutable, starts_at: CarbonImmutable, ends_at: CarbonImmutable}> $occurrences */
    private function conflictGroups(array $occurrences): array
    {
        $conflicts = [];
        foreach ($occurrences as $index => $occurrence) {
            foreach (array_slice($occurrences, $index + 1) as $other) {
                if ($other['starts_at'] >= $occurrence['ends_at']) {
                    break;
                }
                if ($occurrence['event']->id !== $other['event']->id) {
                    $conflicts[$occurrence['event']->id.':'.$other['event']->id] = [$occurrence, $other];
                }
            }
        }

        return array_values($conflicts);
    }
}
