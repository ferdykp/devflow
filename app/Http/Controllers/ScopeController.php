<?php

namespace App\Http\Controllers;

use App\Support\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScopeController extends Controller
{
    public function review(Request $request, int $project)
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $project->reviews()->create($data + [
            'classification' => 'not_assessable',
            'status' => 'pending',
        ]);

        return back()->with('success', 'Permintaan peninjauan dikirim. Cakupan pekerjaan belum berubah.');
    }

    public function decide(Request $request, int $project, int $review)
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve_proposal', 'reject', 'move_to_backlog'])],
            'classification' => ['required', Rule::in(['in_scope', 'out_of_scope', 'uncertain', 'not_assessable'])],
            'reason' => ['required', 'string', 'max:3000'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'bucket' => ['required_if:decision,move_to_backlog', Rule::in(['future', 'research', 'someday'])],
        ]);

        DB::transaction(function () use ($project, $review, $request, $data) {
            // A project-scoped query prevents cross-project modifications.
            $item = $project->reviews()->lockForUpdate()->findOrFail($review);
            abort_unless($item->status === 'pending', 409, 'This review has already been resolved.');

            if ($data['decision'] === 'move_to_backlog') {
                $project->backlogItems()->create([
                    'title' => $item->title,
                    'description' => $item->description,
                    'bucket' => $data['bucket'],
                    'source' => 'scope_review',
                ]);
            }

            // An approval is authorization to PLAN a change, not to silently
            // edit an immutable approved PRD or the existing scope baseline.
            $item->update([
                'classification' => $data['classification'],
                'reasoning' => $data['evidence'] ?? null,
                'status' => 'resolved',
            ]);

            $item->decisions()->create([
                'user_id' => $request->user()->id,
                'decision' => $data['decision'],
                'reason' => $data['reason'],
            ]);

            $project->events()->create([
                'user_id' => $request->user()->id,
                'action' => $data['decision'],
                'metadata' => [
                    'scope_review_id' => $item->id,
                    'classification' => $data['classification'],
                    'evidence' => $data['evidence'] ?? null,
                ],
            ]);
        });

        return back()->with('success', match ($data['decision']) {
            'approve_proposal' => 'Usulan disetujui untuk perencanaan. PRD dan cakupan yang telah disetujui tidak berubah.',
            'reject' => 'Usulan ditolak. Cakupan pekerjaan tidak berubah.',
            default => 'Ide disimpan untuk nanti. Cakupan pekerjaan aktif tidak berubah.',
        });
    }

    public function backlog(Request $request, int $project, int $review)
    {
        // Compatibility with existing backlog forms and bookmarks.
        $request->merge([
            'decision' => 'move_to_backlog',
            'classification' => 'not_assessable',
            'reason' => $request->input('reason') ?: 'Disimpan untuk nanti oleh pemilik proyek.',
        ]);

        return $this->decide($request, $project, $review);
    }
}
