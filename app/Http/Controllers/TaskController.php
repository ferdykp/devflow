<?php

namespace App\Http\Controllers;

use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private function rules(int $project, bool $updating = false): array
    {
        return [
            'title' => [$updating ? 'sometimes' : 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'status' => ['sometimes', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high'])],
            'weight' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'milestone_id' => ['sometimes', 'nullable', Rule::exists('milestones', 'id')->where('project_id', $project)],
        ];
    }

    public function store(Request $request, int $project)
    {
        $projectModel = ProjectAccess::find($project);
        $data = $request->validate($this->rules($project));

        if (($data['status'] ?? 'todo') === 'done') {
            $data['completed_at'] = now();
        }

        $projectModel->tasks()->create($data);
        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function update(Request $request, int $project, int $task)
    {
        $projectModel = ProjectAccess::find($project);
        $taskModel = $projectModel->tasks()->findOrFail($task);
        $data = $request->validate($this->rules($project, true));

        if (array_key_exists('status', $data) && $data['status'] !== $taskModel->status) {
            $data['completed_at'] = $data['status'] === 'done' ? now() : null;
        }

        $taskModel->update($data);

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

    public function destroy(int $project, int $task)
    {
        ProjectAccess::find($project)->tasks()->findOrFail($task)->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
