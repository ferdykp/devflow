<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Support\ProjectAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    private function rules(Project $project, bool $updating = false): array
    {
        return [
            'title' => [$updating ? 'sometimes' : 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'status' => ['sometimes', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high'])],
            'weight' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'milestone_id' => ['sometimes', 'nullable', Rule::exists('milestones', 'id')->where('project_id', $project->id)],
            'assignee_id' => ['sometimes', 'nullable', 'integer', Rule::in($project->workspace->participants()->modelKeys())],
        ];
    }

    public function show(int $project, int $task): View
    {
        $project = ProjectAccess::find($project);
        $project->load('workspace');
        $task = $project->tasks()->with(['assignee', 'milestone', 'taskImport.version.document'])->findOrFail($task);
        $comments = $task->comments()->with('user')->latest()->paginate(20);
        $participants = $project->workspace->participants();
        $milestones = $project->milestones()->orderBy('id')->get();

        return view('projects.task-details', compact('project', 'task', 'comments', 'participants', 'milestones'));
    }

    public function store(Request $request, int $project): RedirectResponse
    {
        $projectModel = ProjectAccess::find($project);
        DB::transaction(function () use ($request, $projectModel): void {
            $workspace = Workspace::query()->accessibleTo($request->user()->id)->lockForUpdate()->findOrFail($projectModel->workspace_id);
            $projectModel->setRelation('workspace', $workspace);
            $data = $request->validate($this->rules($projectModel));
            if (($data['status'] ?? 'todo') === 'done') {
                $data['completed_at'] = now();
            }
            $projectModel->tasks()->create($data);
        });

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function update(Request $request, int $project, int $task): RedirectResponse|JsonResponse
    {
        $projectModel = ProjectAccess::find($project);
        $taskModel = DB::transaction(function () use ($request, $projectModel, $task): Task {
            $workspace = Workspace::query()->accessibleTo($request->user()->id)->lockForUpdate()->findOrFail($projectModel->workspace_id);
            $projectModel->setRelation('workspace', $workspace);
            $taskModel = $projectModel->tasks()->lockForUpdate()->findOrFail($task);
            $data = $request->validate($this->rules($projectModel, true));
            if (array_key_exists('status', $data) && $data['status'] !== $taskModel->status) {
                $data['completed_at'] = $data['status'] === 'done' ? now() : null;
            }
            $taskModel->update($data);

            return $taskModel;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil diperbarui.',
                'task' => [
                    'id' => $taskModel->id,
                    'status' => $taskModel->status,
                ],
            ]);
        }

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(int $project, int $task): RedirectResponse
    {
        ProjectAccess::find($project)->tasks()->findOrFail($task)->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
