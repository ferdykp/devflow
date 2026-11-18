<?php

namespace App\Http\Controllers;

use App\Models\PrdVersion;
use App\Services\ProjectNotificationService;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeRequestController extends Controller
{
    public function store(Request $request, int $project, ProjectNotificationService $notifications): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'scope_review_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $review = null;
        if (isset($data['scope_review_id'])) {
            $review = $project->reviews()->with('decisions')->findOrFail($data['scope_review_id']);
            abort_unless($review->status === 'resolved'
                && $review->decisions->contains('decision', 'approve_proposal'), 422,
                'Selesaikan dan setujui peninjauan sebelum membuat Change Request.');
        }

        $sourceVersionId = $review !== null
            ? $review->prd_version_id
            : $project->baseline?->prd_version_id;
        if ($sourceVersionId !== null) {
            PrdVersion::query()->whereKey($sourceVersionId)
                ->whereIn('status', ['approved', 'superseded'])
                ->whereHas('document', fn ($query) => $query->where('project_id', $project->id))
                ->firstOrFail();
        }

        $changeRequest = $project->changeRequests()->create([
            ...$data,
            'source_prd_version_id' => $sourceVersionId,
            'requested_by' => $request->user()->id,
        ]);
        $notifications->notifyParticipants($project, $request->user()->id, 'change_request.created', 'Change Request baru', $changeRequest->title, route('projects.show', ['project' => $project, 'section' => 'scope'], false));

        return back()->with('success', 'Change Request dicatat. Persetujuan ini belum mengubah PRD atau membuat tugas.');
    }

    public function decide(Request $request, int $project, int $changeRequest, ProjectNotificationService $notifications): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,deferred'],
            'decision_reason' => ['required', 'string', 'max:3000'],
        ]);
        $changeRequest = DB::transaction(function () use ($project, $changeRequest, $request, $data) {
            $changeRequest = $project->changeRequests()->lockForUpdate()->findOrFail($changeRequest);
            abort_unless($changeRequest->status === 'proposed', 409, 'Change Request ini sudah diputuskan.');
            $changeRequest->update([
                'status' => $data['status'],
                'decision_reason' => $data['decision_reason'],
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ]);

            return $changeRequest;
        });
        $notifications->notifyParticipants($project, $request->user()->id, 'change_request.decided', 'Keputusan Change Request', $changeRequest->title.' — '.$data['status'], route('projects.show', ['project' => $project, 'section' => 'scope'], false));

        return back()->with('success', 'Keputusan Change Request tersimpan. Persetujuan PRD tetap merupakan langkah terpisah.');
    }

    public function draftRevision(Request $request, int $project, int $changeRequest): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate(['content_markdown' => ['required', 'string', 'max:200000']]);

        DB::transaction(function () use ($project, $changeRequest, $request, $data): void {
            $changeRequest = $project->changeRequests()->lockForUpdate()->findOrFail($changeRequest);
            abort_unless($changeRequest->status === 'approved', 409, 'Setujui Change Request sebelum menyiapkan draf PRD.');
            abort_if($changeRequest->draft_prd_version_id !== null, 409, 'Draf PRD untuk permintaan ini sudah dibuat.');

            $sourceVersion = $changeRequest->sourcePrdVersion;
            if ($sourceVersion !== null) {
                $document = $sourceVersion->document()->lockForUpdate()->firstOrFail();
            } else {
                $document = $project->documents()->create(['title' => 'PRD — '.$changeRequest->title]);
            }

            $lastVersion = (int) $document->versions()->max('version_number');
            $draft = $document->versions()->create([
                'version_number' => $lastVersion + 1,
                'content_markdown' => $data['content_markdown'],
                'status' => 'draft',
            ]);
            $changeRequest->update(['draft_prd_version_id' => $draft->id]);
            $project->events()->create([
                'user_id' => $request->user()->id,
                'action' => 'change_request.prd_draft_created',
                'metadata' => ['change_request_id' => $changeRequest->id, 'prd_version_id' => $draft->id],
            ]);
        });

        return back()->with('success', 'Draf revisi PRD dibuat dan menunggu persetujuan tersendiri.');
    }
}
