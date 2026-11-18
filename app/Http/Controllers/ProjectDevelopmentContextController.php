<?php

namespace App\Http\Controllers;

use App\Services\DevelopmentContextService;
use App\Support\ProjectAccess;
use Illuminate\Contracts\View\View;

class ProjectDevelopmentContextController extends Controller
{
    public function show(int $project, int $task, DevelopmentContextService $contextService): View
    {
        $project = ProjectAccess::find($project);
        $task = $project->tasks()->with(['milestone', 'taskImport.version.document'])->findOrFail($task);
        $sections = $contextService->build($project, $task);

        return view('projects.development-context', compact('project', 'task', 'sections'));
    }
}
