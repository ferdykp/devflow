<?php

namespace App\Http\Controllers;

use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, int $project, int $task): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $task = $project->tasks()->findOrFail($task);
        $data = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $task->comments()->create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('tasks.show', [$project, $task])->with('success', 'Komentar ditambahkan.');
    }

    public function destroy(Request $request, int $project, int $task, int $comment): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $task = $project->tasks()->findOrFail($task);
        $comment = $task->comments()->findOrFail($comment);
        abort_unless($comment->user_id === $request->user()->id || $project->workspace->user_id === $request->user()->id, 403);
        $comment->delete();

        return redirect()->route('tasks.show', [$project, $task])->with('success', 'Komentar dihapus.');
    }
}
