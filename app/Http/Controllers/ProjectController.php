<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $workspaces = Workspace::query()->accessibleTo($request->user()->id)->with('user')->orderBy('name')->get();
        $projects = Project::whereHas('workspace', fn ($q) => $q->accessibleTo($request->user()->id))
            ->with('workspace')->withCount(['tasks', 'activities'])
            ->when($request->search, fn ($q, $v) => $q->where('name', 'like', '%'.addcslashes($v, '%_\\').'%'))
            ->latest()->paginate(12)->withQueryString();

        return view('projects.index', compact('workspaces', 'projects'));
    }

    public function store(Request $request)
    {
        $workspaceIds = Workspace::query()->accessibleTo($request->user()->id)->pluck('id');
        $data = $request->validate(['workspace_id' => ['required', 'integer', Rule::in($workspaceIds)], 'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:2000']]);
        $project = Project::create($data);

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil dibuat.');
    }

    public function show(int $project)
    {
        $project = ProjectAccess::find($project);
        $requestedSection = request()->query('section', 'overview');
        $allowedSections = ['overview', 'tasks', 'activity', 'notes', 'backlog', 'milestones', 'scope', 'prd', 'github', 'settings'];
        $section = is_string($requestedSection) && in_array($requestedSection, $allowedSections, true)
            ? $requestedSection
            : 'overview';

        // Every project section loads only the data it displays.
        $project->load('workspace');
        $tasks = null;
        $editingTask = null;
        $taskPlan = null;
        $participants = collect();
        $milestones = collect();
        $backlog = collect();
        $documents = collect();
        $repositories = collect();
        $scopeReviews = collect();
        $totalWeight = 0;
        $completedWeight = 0;

        if ($section === 'overview') {
            $project->load(['activities' => fn ($query) => $query->latest('occurred_at')->limit(5)]);
            $project->loadCount(['activities', 'tasks as open_tasks_count' => fn ($query) => $query->where('status', '!=', 'done')]);
            $tasks = $project->tasks()->where('status', '!=', 'done')
                ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
                ->orderBy('due_date')->limit(5)->get();
        } elseif ($section === 'tasks') {
            $filters = request()->validate([
                'edit' => ['nullable', 'integer', 'min:1'],
                'search' => ['nullable', 'string', 'max:200'],
                'status' => ['nullable', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'done'])],
                'priority' => ['nullable', Rule::in(['low', 'normal', 'high'])],
                'assignee' => ['nullable', Rule::in(['me', 'unassigned'])],
                'plan' => ['nullable', 'integer', 'min:1'],
            ]);
            if (request()->filled('plan')) {
                $taskPlan = $project->taskImports()->with('version.document')->whereNotNull('committed_at')->findOrFail($filters['plan']);
            }
            if (request()->filled('edit')) {
                $editingTask = $project->tasks()->findOrFail(request()->integer('edit'));
            }
            $project->loadCount('tasks');
            $tasks = $project->tasks()->with(['assignee', 'milestone'])->withCount('comments')
                ->when($taskPlan, fn ($query) => $query->where('document_task_import_id', $taskPlan->id))
                ->when($filters['search'] ?? null, fn ($query, $value) => $query->where('title', 'like', '%'.addcslashes($value, '%_\\').'%'))
                ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
                ->when($filters['priority'] ?? null, fn ($query, $value) => $query->where('priority', $value))
                ->when(($filters['assignee'] ?? null) === 'me', fn ($query) => $query->where('assignee_id', auth()->id()))
                ->when(($filters['assignee'] ?? null) === 'unassigned', fn ($query) => $query->whereNull('assignee_id'))
                ->when($taskPlan, fn ($query) => $query->orderBy('source_index'))
                ->orderByRaw('COALESCE(sort_order, id)')->orderBy('id')->paginate(60)->withQueryString();
            $participants = $project->workspace->participants();
            $milestones = $project->milestones()->orderBy('title')->get();
        } elseif ($section === 'activity') {
            $project->loadCount(['activities', 'tasks as open_tasks_count' => fn ($query) => $query->where('status', '!=', 'done')]);
            $project->load(['activities' => fn ($query) => $query->latest('occurred_at')->limit(50)]);
        } elseif ($section === 'notes') {
            $project->load(['notes' => fn ($query) => $query->latest()->limit(50)]);
        } elseif ($section === 'backlog') {
            $backlog = $project->backlogItems()->latest()->limit(50)->get();
        } elseif ($section === 'milestones') {
            $milestones = $project->milestones()->with('tasks')->get();
        } elseif ($section === 'scope') {
            $project->load('baseline');
            $scopeReviews = $project->reviews()->with(['decisions.user'])->latest()->limit(30)->get();
        } elseif ($section === 'prd') {
            $documents = $project->documents()->with('versions')->get();
            $project->load(['taskImports' => fn ($query) => $query->with('version.document')->latest()->limit(10)]);
        } elseif ($section === 'github') {
            $repositories = $project->repositories()->get();
        }

        return view('projects.show', compact(
            'project', 'section', 'tasks', 'milestones', 'backlog', 'documents',
            'repositories', 'totalWeight', 'completedWeight', 'scopeReviews', 'editingTask', 'participants', 'taskPlan'
        ));
    }

    public function update(Request $request, int $project)
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:2000'], 'goal' => ['nullable', 'string', 'max:2000'], 'status' => ['required', Rule::in(['active', 'paused', 'archived'])], 'priority' => ['required', Rule::in(['low', 'normal', 'high'])], 'target_date' => ['nullable', 'date']]);
        $project->update($data);

        return back()->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(int $project)
    {
        $project = ProjectAccess::find($project);
        $project->update(['status' => 'archived']);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diarsipkan.');
    }
}
