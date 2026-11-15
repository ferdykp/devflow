<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\FocusItem;
use App\Models\Project;
use App\Models\Task;
use App\Services\Calendar\CalendarPlanner;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, CalendarPlanner $planner): View
    {
        $user = $request->user();
        $timezone = $user->timezone ?: 'Asia/Jakarta';
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $todayStartUtc = $today->utc();
        $todayEndUtc = $today->endOfDay()->utc();
        $focusIds = FocusItem::query()->where('user_id', $user->id)->whereDate('focus_date', $today->toDateString())->pluck('task_id');
        $taskScope = fn ($query) => $query->where(fn ($visible) => $visible->where(fn ($personal) => $personal->where('owner_id', $user->id)->whereNull('project_id'))
            ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id)));
        $priorityTasks = Task::query()->tap($taskScope)->whereIn('id', $focusIds)->whereNot('status', 'done')->with('project:id,name')->get();
        $overdueTasks = Task::query()->tap($taskScope)->whereNot('status', 'done')->whereDate('due_date', '<', $today->toDateString())
            ->with('project:id,name')->orderBy('due_date')->limit(8)->get();
        $upcomingTasks = Task::query()->tap($taskScope)->whereNot('status', 'done')->whereBetween('due_date', [$today->toDateString(), $today->addDays(7)->toDateString()])
            ->with('project:id,name')->orderBy('due_date')->limit(8)->get();
        $events = $user->calendarEvents()->where('status', 'planned')->where('starts_at', '<=', $today->addDays(7)->endOfDay()->utc())->with('exceptions')->get();
        $occurrences = $planner->occurrences($events, $today, $today->addDays(7)->endOfDay(), $timezone);
        $todayAgenda = array_values(array_filter($occurrences, fn (array $occurrence) => $occurrence['starts_at']->toDateString() === $today->toDateString()));
        $upcomingAgenda = array_values(array_filter($occurrences, fn (array $occurrence) => $occurrence['starts_at'] >= CarbonImmutable::now($timezone)));
        $nextActivity = $upcomingAgenda[0] ?? null;
        $routines = $user->routines()->where('status', 'active')->with(['checkIns' => fn ($query) => $query->whereDate('occurred_on', $today->toDateString())])->orderBy('title')->get();
        $routineCompleted = $routines->filter(fn ($routine) => $routine->checkIns->contains('status', 'completed'))->count();
        $learningSessions = $user->learningSessions()->whereBetween('occurred_on', [$today->toDateString(), $today->toDateString()])->sum('duration_minutes');
        $learningCommitments = count(array_filter($todayAgenda, fn (array $occurrence) => $occurrence['event']->learning_lesson_id !== null));
        $goals = $user->personalGoals()->where('status', 'active')->withCount(['milestones as milestones_total', 'milestones as milestones_completed' => fn ($query) => $query->whereNotNull('completed_at')])->orderBy('target_date')->limit(5)->get();
        $projectCount = Project::query()->whereHas('workspace', fn ($workspace) => $workspace->accessibleTo($user->id))->where('status', 'active')->count();
        $projects = Project::query()->whereHas('workspace', fn ($workspace) => $workspace->accessibleTo($user->id))
            ->where('status', 'active')->with('workspace:id,name')->orderBy('name')->limit(5)->get();
        $activities = Activity::query()->whereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id))
            ->with('project:id,name')->orderByDesc('occurred_at')->limit(8)->get();
        $openTasks = $taskScope(Task::query())->whereNot('status', 'done')->count();
        $completedToday = Task::query()->whereBetween('completed_at', [$todayStartUtc, $todayEndUtc])->tap($taskScope)->count();
        $conflicts = $this->conflictCount($todayAgenda);
        $recommendationCount = $user->coachRecommendations()->where('status', 'pending')->count();

        return view('dashboard.index', compact('user', 'timezone', 'today', 'priorityTasks', 'overdueTasks', 'upcomingTasks', 'todayAgenda', 'nextActivity', 'routines', 'routineCompleted', 'learningSessions', 'learningCommitments', 'goals', 'projectCount', 'projects', 'activities', 'openTasks', 'completedToday', 'conflicts', 'recommendationCount'));
    }

    private function conflictCount(array $occurrences): int
    {
        $count = 0;
        foreach ($occurrences as $index => $occurrence) {
            foreach (array_slice($occurrences, $index + 1) as $other) {
                if ($other['starts_at'] >= $occurrence['ends_at']) {
                    break;
                }
                if ($other['event']->id !== $occurrence['event']->id) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
