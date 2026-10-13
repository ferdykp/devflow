<?php

namespace App\Http\Controllers;

use App\Services\Resume\ProjectResumeService;
use App\Support\ProjectAccess;
use Illuminate\Contracts\View\View;

class ProjectResumeController extends Controller
{
    public function show(int $project, ProjectResumeService $resumeService): View
    {
        $project = ProjectAccess::find($project);
        $resume = $resumeService->build($project);

        return view('projects.resume', compact('project', 'resume'));
    }
}
