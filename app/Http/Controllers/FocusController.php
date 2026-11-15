<?php

namespace App\Http\Controllers;

use App\Models\FocusItem;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FocusController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $focusDate = today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString();
        $selected = FocusItem::query()
            ->where('user_id', $userId)
            ->whereDate('focus_date', $focusDate)
            ->pluck('task_id')->all();
        $focusOrder = data_get($request->user()->preferences ?? [], 'focus_order', []);
        $focusedTasks = Task::query()->whereIn('id', $selected)->with('project:id,name')->get()
            ->sortBy(fn (Task $task): int => (($position = array_search($task->id, $focusOrder, true)) === false) ? PHP_INT_MAX : $position)
            ->values();

        $tasks = Task::query()
            ->where(function ($query) use ($userId): void {
                $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                    ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId));
            })
            ->where(fn ($query) => $query->whereNull('project_id')->orWhereHas('project', fn ($project) => $project->where('status', 'active')))
            ->whereNotIn('status', ['done', 'blocked'])
            ->with('project')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(30)->get();

        return view('focus.index', compact('tasks', 'selected', 'focusedTasks'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'task_ids' => ['required', 'array', 'max:3'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $selectedIds = FocusItem::query()->where('user_id', $request->user()->id)
            ->whereDate('focus_date', today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString())
            ->pluck('task_id')->map(fn ($id): int => (int) $id)->all();
        abort_unless(count($data['task_ids']) === count($selectedIds) && array_diff($data['task_ids'], $selectedIds) === [], 422, 'Urutan prioritas tidak cocok dengan pilihan hari ini.');

        $preferences = $request->user()->preferences ?? [];
        $preferences['focus_order'] = array_values(array_map('intval', $data['task_ids']));
        $request->user()->update(['preferences' => $preferences]);

        return response()->json(['message' => 'Urutan fokus diperbarui.']);
    }

    public function toggle(Request $request, int $task): RedirectResponse
    {
        $userId = $request->user()->id;
        $task = Task::query()->whereKey($task)
            ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))
            ->whereNotIn('status', ['done', 'blocked'])
            ->where(fn ($query) => $query->whereNull('project_id')->orWhereHas('project', fn ($project) => $project->where('status', 'active')))
            ->firstOrFail();

        $result = DB::transaction(function () use ($userId, $task) {
            if ($task->project_id !== null) {
                Workspace::query()->accessibleTo($userId)->lockForUpdate()->findOrFail($task->project->workspace_id);
            }
            // Serialize concurrent focus changes for this user on MySQL.
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            $focusQuery = FocusItem::query()
                ->where('user_id', $userId)
                ->where('task_id', $task->id)
                ->whereDate('focus_date', today(auth()->user()->timezone ?: 'Asia/Jakarta')->toDateString());

            if ($focusQuery->exists()) {
                $focusQuery->delete();

                return 'removed';
            }

            if (FocusItem::query()->where('user_id', $userId)
                ->whereDate('focus_date', today(auth()->user()->timezone ?: 'Asia/Jakarta')->toDateString())->count() >= 3) {
                return 'limit';
            }

            FocusItem::query()->create([
                'user_id' => $userId,
                'task_id' => $task->id,
                'focus_date' => today(auth()->user()->timezone ?: 'Asia/Jakarta')->toDateString(),
            ]);

            return 'added';
        });

        return back()->with(
            $result === 'limit' ? 'error' : 'success',
            $result === 'limit' ? 'Pilih maksimal tiga tugas fokus.' : 'Fokus hari ini diperbarui.'
        );
    }
}
