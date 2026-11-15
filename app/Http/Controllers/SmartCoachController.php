<?php

namespace App\Http\Controllers;

use App\Models\CoachRecommendation;
use App\Models\FocusItem;
use App\Models\Task;
use App\Services\SmartCoachService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SmartCoachController extends Controller
{
    public function index(Request $request, SmartCoachService $coach): View
    {
        $coach->refresh($request->user());
        $recommendations = $request->user()->coachRecommendations()->where('status', 'pending')->latest()->get();
        $history = $request->user()->coachRecommendations()->whereIn('status', ['accepted', 'dismissed'])->latest()->limit(20)->get();

        return view('smart-coach.index', compact('recommendations', 'history'));
    }

    public function confirm(Request $request, int $recommendation): RedirectResponse
    {
        $recommendationModel = $request->user()->coachRecommendations()->where('status', 'pending')->findOrFail($recommendation);
        $action = $recommendationModel->proposed_action ?? [];
        $redirect = match ($action['type'] ?? null) {
            'prioritize_tasks' => $this->prioritizeTasks($request, $recommendationModel, $action),
            'open_inbox' => route('tasks.index', ['status' => 'open']),
            'open_learning' => route('learning.index'),
            'open_routines' => route('routines.index'),
            'open_weekly_review' => route('weekly-review.index', ['week' => $action['week'] ?? null]),
            'open_calendar' => route('calendar.index', ['date' => $action['date'] ?? today()->toDateString()]),
            default => route('smart-coach.index'),
        };
        $recommendationModel->update(['status' => 'accepted', 'pending_key' => null, 'confirmed_at' => now()]);

        return redirect($redirect)->with('success', 'Saran dikonfirmasi. Tinjau konteksnya sebelum membuat perubahan lanjutan.');
    }

    public function dismiss(Request $request, int $recommendation): RedirectResponse
    {
        $request->user()->coachRecommendations()->where('status', 'pending')->findOrFail($recommendation)
            ->update(['status' => 'dismissed', 'pending_key' => null, 'dismissed_at' => now()]);

        return back()->with('success', 'Saran ditutup.');
    }

    private function prioritizeTasks(Request $request, CoachRecommendation $recommendation, array $action): string
    {
        $taskIds = array_slice(array_values(array_unique(array_map('intval', $action['task_ids'] ?? []))), 0, 3);
        DB::transaction(function () use ($request, $taskIds): void {
            $user = $request->user();
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $focusDate = today($user->timezone ?: 'Asia/Jakarta')->toDateString();
            $existing = FocusItem::query()->where('user_id', $user->id)->whereDate('focus_date', $focusDate)->count();
            $available = max(0, 3 - $existing);
            $userId = $user->id;
            $tasks = Task::query()->whereIn('id', $taskIds)->whereNotIn('status', ['done', 'blocked'])
                ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                    ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))
                ->limit($available)->get();
            foreach ($tasks as $task) {
                FocusItem::query()->firstOrCreate(['user_id' => $userId, 'task_id' => $task->id, 'focus_date' => $focusDate]);
            }
        });

        return route('my-day.index');
    }
}
