<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskChecklistController extends Controller
{
    public function update(Request $request, int $project, int $task, int $item): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate(['done' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $project, $task, $item, $data): void {
            Workspace::query()->accessibleTo($request->user()->id)->lockForUpdate()->findOrFail($project->workspace_id);
            $task = $project->tasks()->lockForUpdate()->findOrFail($task);
            $checklist = $task->checklist ?? [];
            abort_unless(array_key_exists($item, $checklist), 404);
            $checklist[$item]['done'] = (bool) $data['done'];
            $task->update(['checklist' => $checklist]);
        });

        return back()->with('success', 'Checklist diperbarui.');
    }
}
