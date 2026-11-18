<?php

namespace App\Http\Controllers;

use App\Models\PrdVersion;
use App\Services\ProjectNotificationService;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScopeController extends Controller
{
    public function review(Request $request, int $project, ProjectNotificationService $notifications): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $referenceVersionId = $project->baseline !== null
            ? $project->baseline->prd_version_id
            : PrdVersion::query()
                ->where('status', 'approved')
                ->whereHas('document', fn ($query) => $query->where('project_id', $project->id))
                ->latest('approved_at')->value('id');

        $review = $project->reviews()->create($data + [
            'prd_version_id' => $referenceVersionId,
            'classification' => 'not_assessable',
            'status' => 'pending',
        ]);
        $notifications->notifyParticipants($project, $request->user()->id, 'scope_review.created', 'Peninjauan cakupan baru', $review->title, route('projects.show', ['project' => $project, 'section' => 'scope'], false));

        return back()->with('success', 'Permintaan peninjauan dikirim. Cakupan pekerjaan belum berubah.');
    }

    public function decide(Request $request, int $project, int $review, ProjectNotificationService $notifications): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve_proposal', 'reject', 'move_to_backlog'])],
            'classification' => ['required', Rule::in(['in_scope', 'out_of_scope', 'potential_out_of_scope', 'focus_misalignment', 'unplanned', 'uncertain', 'not_assessable'])],
            'reason' => ['required', 'string', 'max:3000'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'bucket' => ['required_if:decision,move_to_backlog', Rule::in(['future', 'research', 'someday'])],
        ]);

        $item = DB::transaction(function () use ($project, $review, $request, $data) {
            // A project-scoped query prevents cross-project modifications.
            $item = $project->reviews()->lockForUpdate()->findOrFail($review);
            abort_unless($item->status === 'pending', 409, 'Peninjauan ini sudah diputuskan.');
            $baseline = $project->baseline;
            $hasReference = $item->prd_version_id !== null
                || ($baseline !== null && (filled($baseline->in_scope) || filled($baseline->out_of_scope)
                    || filled($baseline->constraints) || filled($baseline->assumptions) || filled($baseline->success_criteria)
                    || filled($baseline->definition_of_done)));
            if (! $hasReference && ! in_array($data['classification'], ['uncertain', 'not_assessable'], true)) {
                throw ValidationException::withMessages([
                    'classification' => 'Tambahkan baseline atau PRD yang disetujui sebelum memberi klasifikasi pasti.',
                ]);
            }

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
                'evidence' => $data['evidence'] ?? null,
                'prd_version_id' => $item->prd_version_id,
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

            return $item;
        });

        $notifications->notifyParticipants($project, $request->user()->id, 'scope_review.decided', 'Peninjauan cakupan diputuskan', $item->title, route('projects.show', ['project' => $project, 'section' => 'scope'], false));

        return back()->with('success', match ($data['decision']) {
            'approve_proposal' => 'Usulan disetujui untuk perencanaan. PRD dan cakupan yang telah disetujui tidak berubah.',
            'reject' => 'Usulan ditolak. Cakupan pekerjaan tidak berubah.',
            default => 'Ide disimpan untuk nanti. Cakupan pekerjaan aktif tidak berubah.',
        });
    }

    public function backlog(Request $request, int $project, int $review, ProjectNotificationService $notifications): RedirectResponse
    {
        // Compatibility with existing backlog forms and bookmarks.
        $request->merge([
            'decision' => 'move_to_backlog',
            'classification' => 'not_assessable',
            'reason' => $request->input('reason') ?: 'Disimpan untuk nanti oleh pemilik proyek.',
        ]);

        return $this->decide($request, $project, $review, $notifications);
    }
}
