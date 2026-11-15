<?php

namespace App\Http\Controllers;

use App\Models\LearningAssessment;
use App\Models\LearningLesson;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalDetailController extends Controller
{
    public function task(Request $request, int $task): View
    {
        $user = $request->user();
        $taskModel = Task::query()->whereKey($task)
            ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $user->id)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id)))
            ->with(['project:id,name', 'personalGoal:id,title', 'learningLesson:id,title', 'assignee:id,name', 'history.user:id,name'])
            ->firstOrFail();

        return view('tasks.show', ['task' => $taskModel]);
    }

    public function routine(Request $request, int $routine): View
    {
        $routineModel = $request->user()->routines()->with(['goal:id,title', 'learningPath:id,title', 'checkIns' => fn ($query) => $query->latest('occurred_on')->limit(30)])
            ->findOrFail($routine);

        return view('routines.show', ['routine' => $routineModel]);
    }

    public function goal(Request $request, int $goal): View
    {
        $goalModel = $request->user()->personalGoals()->with([
            'milestones', 'progressEntries', 'tasks' => fn ($query) => $query->whereNull('project_id')->orderBy('due_date'),
            'routines:id,title,personal_goal_id,status', 'learningPaths:id,title,personal_goal_id,status', 'notes:id,title,personal_goal_id,updated_at',
        ])->findOrFail($goal);

        return view('goals.show', ['goal' => $goalModel]);
    }

    public function learningPath(Request $request, int $path): View
    {
        $pathModel = $request->user()->learningPaths()->with(['goal:id,title', 'modules.lessons' => fn ($query) => $query->withCount(['sessions', 'assessments'])])
            ->findOrFail($path);

        return view('learning.path', ['path' => $pathModel]);
    }

    public function learningModule(Request $request, int $path, int $module): View
    {
        $pathModel = $request->user()->learningPaths()->with('goal:id,title')->findOrFail($path);
        $moduleModel = $pathModel->modules()->with(['lessons' => fn ($query) => $query->withCount(['sessions', 'assessments'])])->findOrFail($module);

        return view('learning.module', ['path' => $pathModel, 'module' => $moduleModel]);
    }

    public function lesson(Request $request, int $lesson): View
    {
        $lessonModel = LearningLesson::query()->whereHas('module.path', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->with(['module.path', 'sessions' => fn ($query) => $query->where('owner_id', $request->user()->id), 'assessments' => fn ($query) => $query->where('owner_id', $request->user()->id), 'materials' => fn ($query) => $query->where('owner_id', $request->user()->id)])
            ->findOrFail($lesson);
        $adjacentLessons = LearningLesson::query()->whereHas('module.path', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->whereHas('module', fn ($query) => $query->where('learning_path_id', $lessonModel->module->learning_path_id))
            ->with('module:id,title,learning_path_id')->orderBy('learning_module_id')->orderBy('id')->get();
        $lessonIndex = $adjacentLessons->search(fn (LearningLesson $item) => $item->id === $lessonModel->id);

        return view('learning.lesson', [
            'lesson' => $lessonModel,
            'previousLesson' => $lessonIndex > 0 ? $adjacentLessons[$lessonIndex - 1] : null,
            'nextLesson' => $lessonIndex < $adjacentLessons->count() - 1 ? $adjacentLessons[$lessonIndex + 1] : null,
        ]);
    }

    public function learningSessions(Request $request): View
    {
        $sessions = $request->user()->learningSessions()->with(['path:id,title', 'lesson:id,title,learning_module_id'])->latest('occurred_on')->paginate(25);

        return view('learning.sessions', compact('sessions'));
    }

    public function learningAssessments(Request $request): View
    {
        $assessments = LearningAssessment::query()->where('owner_id', $request->user()->id)
            ->with(['lesson:id,title,learning_module_id', 'lesson.module:id,title,learning_path_id', 'lesson.module.path:id,title'])
            ->latest('assessed_on')->paginate(25);
        $reviewLessons = LearningLesson::query()->whereNotNull('next_review_at')->where('next_review_at', '<=', now())
            ->whereHas('module.path', fn ($query) => $query->where('owner_id', $request->user()->id)->where('status', 'active'))
            ->with('module.path:id,title')->orderBy('next_review_at')->limit(20)->get();

        return view('learning.assessments', compact('assessments', 'reviewLessons'));
    }

    public function note(Request $request, int $note): View
    {
        $user = $request->user();
        $noteModel = $user->personalNotes()->with(['goal:id,title', 'task:id,title', 'project:id,name', 'lesson:id,title'])->findOrFail($note);
        $goals = $user->personalGoals()->where('status', '!=', 'archived')->orderBy('title')->get(['id', 'title']);
        $projects = Project::query()->whereHas('workspace', fn ($workspace) => $workspace->accessibleTo($user->id))->orderBy('name')->get(['id', 'name']);
        $tasks = Task::query()->where(fn ($query) => $query->where('owner_id', $user->id)->whereNull('project_id')->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($user->id)))
            ->orderBy('title')->limit(100)->get(['id', 'title']);
        $lessons = LearningLesson::query()->whereHas('module.path', fn ($path) => $path->where('owner_id', $user->id))->orderBy('title')->get(['id', 'title']);

        return view('notes.edit', compact('goals', 'projects', 'tasks', 'lessons') + ['note' => $noteModel]);
    }
}
