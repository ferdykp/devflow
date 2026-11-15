<?php

namespace App\Http\Controllers;

use App\Models\LearningLesson;
use App\Models\Project;
use App\Models\Task;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalNoteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $showArchived = $request->boolean('archived');
        $notes = $request->user()->personalNotes()->with(['goal:id,title', 'task:id,title', 'project:id,name', 'lesson:id,title'])
            ->when($showArchived, fn ($query) => $query->whereNotNull('archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->when($search !== '', fn ($query) => $query->where(function ($notes) use ($search): void {
                $notes->where('title', 'like', '%'.$search.'%')->orWhere('content', 'like', '%'.$search.'%')
                    ->orWhere('collection', 'like', '%'.$search.'%')->orWhere('tags', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('is_pinned')->orderByDesc('updated_at')->paginate(20)->withQueryString();
        $goals = $request->user()->personalGoals()->where('status', '!=', 'archived')->orderBy('title')->get(['id', 'title']);
        $projects = Project::query()->whereHas('workspace', fn ($workspace) => $workspace->accessibleTo($request->user()->id))->orderBy('name')->get(['id', 'name']);
        $tasks = Task::query()->where(fn ($query) => $query->where('owner_id', $request->user()->id)->whereNull('project_id')->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($request->user()->id)))
            ->orderBy('title')->limit(100)->get(['id', 'title']);
        $lessons = LearningLesson::query()->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id))->orderBy('title')->get(['id', 'title']);

        return view('notes.index', compact('notes', 'goals', 'projects', 'tasks', 'lessons', 'search', 'showArchived'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules($request, false));
        $data['tags'] = $this->normalizeTags($data['tags_input'] ?? $data['tags'] ?? null);
        unset($data['tags_input']);
        if (isset($data['task_id'])) {
            $this->accessibleTask($request, (int) $data['task_id']);
        }
        if (isset($data['project_id'])) {
            ProjectAccess::find((int) $data['project_id']);
        }
        if (isset($data['learning_lesson_id'])) {
            LearningLesson::query()->whereKey($data['learning_lesson_id'])->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id))->firstOrFail();
        }
        $request->user()->personalNotes()->create($data);

        return back()->with('success', 'Catatan pribadi disimpan.');
    }

    public function update(Request $request, int $note): RedirectResponse
    {
        $noteModel = $request->user()->personalNotes()->findOrFail($note);
        $data = $request->validate($this->rules($request, true));
        if (array_key_exists('tags_input', $data)) {
            $data['tags'] = $this->normalizeTags($data['tags_input']);
            unset($data['tags_input']);
        }
        if (array_key_exists('is_pinned', $data)) {
            $data['is_pinned'] = (bool) $data['is_pinned'];
        }
        if (array_key_exists('archived', $data)) {
            $data['archived_at'] = $data['archived'] ? now() : null;
            unset($data['archived']);
        }
        if (! empty($data['task_id'])) {
            $this->accessibleTask($request, (int) $data['task_id']);
        }
        if (! empty($data['project_id'])) {
            ProjectAccess::find((int) $data['project_id']);
        }
        if (! empty($data['learning_lesson_id'])) {
            LearningLesson::query()->whereKey($data['learning_lesson_id'])->whereHas('module.path', fn ($path) => $path->where('owner_id', $request->user()->id))->firstOrFail();
        }
        $noteModel->update($data);

        return back()->with('success', 'Catatan diperbarui.');
    }

    public function destroy(Request $request, int $note): RedirectResponse
    {
        $request->user()->personalNotes()->findOrFail($note)->delete();

        return back()->with('success', 'Catatan dihapus.');
    }

    private function rules(Request $request, bool $updating): array
    {
        return [
            'title' => [$updating ? 'sometimes' : 'required', 'string', 'max:200'],
            'content' => ['sometimes', 'nullable', 'string', 'max:100000'],
            'collection' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tags_input' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'tags' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'personal_goal_id' => ['sometimes', 'nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
            'task_id' => ['sometimes', 'nullable', 'integer'],
            'project_id' => ['sometimes', 'nullable', 'integer'],
            'learning_lesson_id' => ['sometimes', 'nullable', 'integer'],
            'is_pinned' => ['sometimes', 'boolean'],
            'archived' => ['sometimes', 'boolean'],
        ];
    }

    /** @return list<string> */
    private function normalizeTags(?string $tags): array
    {
        return array_values(array_unique(array_filter(array_map('trim', explode(',', $tags ?? '')))));
    }

    private function accessibleTask(Request $request, int $taskId): Task
    {
        $userId = $request->user()->id;

        return Task::query()->whereKey($taskId)->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
            ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))->firstOrFail();
    }
}
