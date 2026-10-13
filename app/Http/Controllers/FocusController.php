<?php

namespace App\Http\Controllers;

use App\Models\FocusItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FocusController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $selected = FocusItem::query()
            ->where('user_id', $userId)
            ->whereDate('focus_date', today())
            ->pluck('task_id')->all();

        $tasks = Task::query()
            ->whereHas('project.workspace', fn ($query) => $query->where('user_id', $userId))
            ->whereHas('project', fn ($query) => $query->where('status', 'active'))
            ->whereNotIn('status', ['done', 'blocked'])
            ->with('project')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(30)->get();

        return view('focus.index', compact('tasks', 'selected'));
    }

    public function toggle(Request $request, int $task): RedirectResponse
    {
        $userId = $request->user()->id;
        $task = Task::query()
            ->whereHas('project.workspace', fn ($query) => $query->where('user_id', $userId))
            ->whereHas('project', fn ($query) => $query->where('status', 'active'))
            ->whereNotIn('status', ['done', 'blocked'])
            ->findOrFail($task);

        $result = DB::transaction(function () use ($userId, $task) {
            // Serialize concurrent focus changes for this user on MySQL.
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            $focusQuery = FocusItem::query()
                ->where('user_id', $userId)
                ->where('task_id', $task->id)
                ->whereDate('focus_date', today());

            if ($focusQuery->exists()) {
                $focusQuery->delete();
                return 'removed';
            }

            if (FocusItem::query()->where('user_id', $userId)
                ->whereDate('focus_date', today())->count() >= 3) {
                return 'limit';
            }

            FocusItem::query()->create([
                'user_id' => $userId,
                'task_id' => $task->id,
                'focus_date' => today(),
            ]);

            return 'added';
        });

        return back()->with(
            $result === 'limit' ? 'error' : 'success',
            $result === 'limit' ? 'Choose up to three focus tasks.' : "Today's focus updated."
        );
    }
}
