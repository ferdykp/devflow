<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\TaskHistoryEntry;
use App\Services\Calendar\CalendarPlanner;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WeeklyReviewController extends Controller
{
    public function index(Request $request, CalendarPlanner $planner): View
    {
        $timezone = $request->user()->timezone ?: 'Asia/Jakarta';
        $weekStart = CarbonImmutable::parse($request->query('week', today($timezone)->startOfWeek()->subWeek()->toDateString()), $timezone)->startOfWeek();
        $weekEnd = $weekStart->endOfWeek();
        $fromUtc = $weekStart->startOfDay()->utc();
        $toUtc = $weekEnd->endOfDay()->utc();
        $userId = $request->user()->id;
        $tasksQuery = Task::query()->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
            ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)));
        $completedTasks = (clone $tasksQuery)->whereBetween('completed_at', [$fromUtc, $toUtc])->with('project:id,name')->orderByDesc('completed_at')->get();
        $incompleteTasks = (clone $tasksQuery)->whereNot('status', 'done')->whereBetween('due_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->with('project:id,name')->orderBy('due_date')->get();
        $learningSessions = $request->user()->learningSessions()->whereBetween('occurred_on', [$weekStart->toDateString(), $weekEnd->toDateString()])->with(['path:id,title', 'lesson:id,title'])->latest('occurred_on')->get();
        $routines = $request->user()->routines()->with(['checkIns' => fn ($query) => $query->whereBetween('occurred_on', [$weekStart->toDateString(), $weekEnd->toDateString()])])->orderBy('title')->get();
        $goals = $request->user()->personalGoals()->where('status', 'active')->with(['progressEntries' => fn ($query) => $query->whereBetween('recorded_on', [$weekStart->toDateString(), $weekEnd->toDateString()])->latest('recorded_on'), 'milestones'])->orderBy('target_date')->get();
        $events = $request->user()->calendarEvents()->where('status', 'planned')->where('starts_at', '<=', $toUtc)->with('exceptions')->get();
        $occurrences = $planner->occurrences($events, $weekStart, $weekEnd, $timezone);
        $conflictPairs = $this->findConflicts($occurrences);
        $review = $request->user()->weeklyReviews()->whereDate('week_starts_on', $weekStart->toDateString())->first();
        $history = $this->history($request, $timezone, $weekStart);

        return view('weekly-review.index', compact('weekStart', 'weekEnd', 'completedTasks', 'incompleteTasks', 'learningSessions', 'routines', 'goals', 'occurrences', 'conflictPairs', 'review', 'history', 'timezone'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'week_starts_on' => ['required', 'date'],
            'reflection' => ['nullable', 'string', 'max:5000'],
            'planned_commitments' => ['nullable', 'string', 'max:4000'],
        ]);
        $request->user()->weeklyReviews()->updateOrCreate(
            ['week_starts_on' => CarbonImmutable::parse($data['week_starts_on'])->startOfWeek()->toDateString()],
            ['reflection' => $data['reflection'] ?? null, 'planned_commitments' => isset($data['planned_commitments']) ? ['notes' => $data['planned_commitments']] : null, 'reviewed_at' => now()],
        );

        return back()->with('success', 'Refleksi dan rencana mingguan tersimpan.');
    }

    public function carryForward(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'week_starts_on' => ['required', 'date'],
            'task_ids' => ['nullable', 'array', 'max:100'],
            'task_ids.*' => ['integer', 'distinct'],
        ]);
        $weekStart = CarbonImmutable::parse($data['week_starts_on'])->startOfWeek();
        $nextWeekStart = $weekStart->addWeek();
        $userId = $request->user()->id;
        DB::transaction(function () use ($request, $data, $weekStart, $nextWeekStart, $userId): void {
            $tasks = Task::query()->whereIn('id', $data['task_ids'] ?? [])->whereNot('status', 'done')
                ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                    ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))->lockForUpdate()->get();
            foreach ($tasks as $task) {
                $oldDueDate = $task->due_date;
                $newDueDate = $oldDueDate && $oldDueDate->betweenIncluded($weekStart, $weekStart->endOfWeek())
                    ? $oldDueDate->addWeek()
                    : $nextWeekStart;
                $task->update(['due_date' => $newDueDate->toDateString(), 'scheduled_at' => null]);
                TaskHistoryEntry::query()->create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'action' => 'carried_forward', 'metadata' => ['from' => $oldDueDate?->toDateString(), 'to' => $newDueDate->toDateString()]]);
            }
            $request->user()->weeklyReviews()->updateOrCreate(
                ['week_starts_on' => $weekStart->toDateString()],
                ['carry_forward_task_ids' => $tasks->modelKeys(), 'reviewed_at' => now()],
            );
        });

        return back()->with('success', 'Tugas terpilih dipindahkan ke minggu berikutnya. Tugas yang sama tetap digunakan.');
    }

    /** @param list<array{event: CalendarEvent, original_start: CarbonImmutable, starts_at: CarbonImmutable, ends_at: CarbonImmutable}> $occurrences */
    private function findConflicts(array $occurrences): array
    {
        $conflicts = [];
        foreach ($occurrences as $index => $occurrence) {
            foreach (array_slice($occurrences, $index + 1) as $other) {
                if ($other['starts_at'] >= $occurrence['ends_at']) {
                    break;
                }
                if ($other['event']->id !== $occurrence['event']->id) {
                    $conflicts[] = [$occurrence, $other];
                }
            }
        }

        return $conflicts;
    }

    private function history(Request $request, string $timezone, CarbonImmutable $currentWeek): array
    {
        $history = [];
        for ($offset = 1; $offset <= 4; $offset++) {
            $start = $currentWeek->subWeeks($offset);
            $end = $start->endOfWeek();
            $history[] = [
                'start' => $start,
                'completed' => Task::query()->whereNotNull('completed_at')->whereBetween('completed_at', [$start->startOfDay()->utc(), $end->endOfDay()->utc()])
                    ->where(fn ($query) => $query->where('owner_id', $request->user()->id)->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($request->user()->id)))->count(),
                'learning_minutes' => $request->user()->learningSessions()->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->sum('duration_minutes'),
                'routines_completed' => $request->user()->routines()->whereHas('checkIns', fn ($query) => $query->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->where('status', 'completed'))->withCount(['checkIns as completed_count' => fn ($query) => $query->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->where('status', 'completed')])->get()->sum('completed_count'),
            ];
        }

        return $history;
    }
}
