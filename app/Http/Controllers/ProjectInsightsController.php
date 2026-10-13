<?php

namespace App\Http\Controllers;

use App\Services\AI\ProjectInsights;
use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectInsightsController extends Controller
{
    public function show(int $project, ProjectInsights $insights)
    {
        $project = ProjectAccess::find($project);
        return view('projects.insights', ['project' => $project, 'ready' => $insights->ready(), 'result' => null]);
    }

    public function analyze(Request $request, int $project, ProjectInsights $insights)
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'mode' => ['required', Rule::in(['scope', 'progress'])],
            'proposal' => ['nullable', 'string', 'max:3000', 'required_if:mode,scope'],
            'consent' => ['accepted'],
        ]);
        $result = $insights->analyze($project, $data['mode'], $data['proposal'] ?? null);
        return view('projects.insights', ['project' => $project, 'ready' => $insights->ready(), 'result' => $result]);
    }
}
