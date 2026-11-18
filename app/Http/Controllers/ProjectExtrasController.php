<?php

namespace App\Http\Controllers;

use App\Models\PrdVersion;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectExtrasController extends Controller
{
    public function note(Request $r, int $project)
    {
        $p = ProjectAccess::find($project);
        $d = $r->validate(['title' => ['required', 'string', 'max:180'], 'content' => ['nullable', 'string', 'max:15000']]);
        $p->notes()->create($d + ['user_id' => $r->user()->id]);

        return back()->with('success', 'Catatan berhasil disimpan.');
    }

    public function milestone(Request $r, int $project)
    {
        $p = ProjectAccess::find($project);
        $p->milestones()->create($r->validate(['title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:2000'], 'due_date' => ['nullable', 'date']]));

        return back()->with('success', 'Tahapan berhasil dibuat.');
    }

    public function backlog(Request $r, int $project)
    {
        $p = ProjectAccess::find($project);
        $p->backlogItems()->create($r->validate(['title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:3000'], 'bucket' => ['required', Rule::in(['future', 'research', 'someday'])]]) + ['source' => 'manual']);

        return back()->with('success', 'Ide berhasil disimpan untuk nanti.');
    }

    public function baseline(Request $r, int $project): RedirectResponse
    {
        $p = ProjectAccess::find($project);
        $d = $r->validate([
            'in_scope' => ['nullable', 'string', 'max:15000'],
            'out_of_scope' => ['nullable', 'string', 'max:15000'],
            'constraints' => ['nullable', 'string', 'max:10000'],
            'assumptions' => ['nullable', 'string', 'max:10000'],
            'success_criteria' => ['nullable', 'string', 'max:10000'],
            'definition_of_done' => ['nullable', 'string', 'max:10000'],
            'prd_version_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if (isset($d['prd_version_id'])) {
            abort_unless(PrdVersion::query()->whereKey($d['prd_version_id'])
                ->where('status', 'approved')->whereHas('document', fn ($q) => $q->where('project_id', $p->id))->exists(), 422,
                'Pilih versi PRD yang disetujui pada proyek ini.');
        }
        $p->baseline()->updateOrCreate(['project_id' => $p->id], $d);

        return back()->with('success', 'Baseline cakupan berhasil disimpan.');
    }
}
