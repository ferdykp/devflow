<?php

namespace Database\Seeders;

use App\Models\ChangeRequest;
use App\Models\DocumentTaskImport;
use App\Models\User;
use App\Notifications\WorkspaceActivityNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DevFlowProjectWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') && ! filter_var(env('DEVFLOW_ALLOW_PRODUCTION_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Demo seeding is disabled in production.');
        }

        $user = User::query()->firstWhere('email', 'demo@devflow.local');
        $workspace = $user?->workspaces()->firstWhere('name', 'Product Lab');
        $project = $workspace?->projects()->firstWhere('name', 'DevFlow');

        if (! $user || ! $workspace || ! $project) {
            throw new RuntimeException('Run DevFlowDemoSeeder before DevFlowProjectWorkflowSeeder.');
        }

        DB::transaction(function () use ($user, $workspace, $project): void {
            $teammate = User::query()->firstOrCreate(['email' => 'teammate@devflow.local'], [
                'name' => 'Dimas Developer',
                'password' => Hash::make('Devdemo123.'),
                'timezone' => $user->timezone ?: 'Asia/Jakarta',
                'email_verified_at' => now(),
            ]);
            $workspace->members()->syncWithoutDetaching([$teammate->id]);

            $document = $project->documents()->firstOrCreate(['title' => 'Product Requirements']);
            $sourceMarkdown = "# DevFlow — Product Requirements\n\n## Goal\nResume work quickly and keep changes aligned with approved scope.";
            $approvedVersion = $document->versions()->firstOrCreate(['version_number' => 1], [
                'status' => 'approved',
                'content_markdown' => $sourceMarkdown,
                'content_hash' => hash('sha256', $sourceMarkdown),
                'approved_by' => $user->id,
                'approved_at' => now()->subDays(12),
            ]);
            $draftMarkdown = "# DevFlow — Product Requirements\n\n## Goal\nResume work quickly and keep changes aligned with approved scope.\n\n## Proposed update\nAdd an offline-ready developer context package.";
            $draftVersion = $document->versions()->firstOrCreate(['version_number' => 3], [
                'status' => 'draft',
                'content_markdown' => $draftMarkdown,
                'content_hash' => hash('sha256', $draftMarkdown),
            ]);

            $baseline = $project->baseline()->firstOrCreate([], [
                'prd_version_id' => $approvedVersion->id,
                'in_scope' => 'Projects, tasks, notes, daily focus, PRD versions and GitHub activity.',
                'out_of_scope' => 'Billing, CRM, time tracking and automatic PRD approvals.',
                'constraints' => 'Developer context stays local and redacts secrets before export.',
                'assumptions' => 'The project owner approves scope changes before implementation.',
                'success_criteria' => 'A developer can resume a task from a concise, safe context package.',
                'definition_of_done' => 'The flow is tested, accessible, and linked to its approved PRD version.',
            ]);
            $baseline->fill([
                'prd_version_id' => $baseline->prd_version_id ?: $approvedVersion->id,
                'constraints' => $baseline->constraints ?: 'Developer context stays local and redacts secrets before export.',
                'assumptions' => $baseline->assumptions ?: 'The project owner approves scope changes before implementation.',
                'success_criteria' => $baseline->success_criteria ?: 'A developer can resume a task from a concise, safe context package.',
                'definition_of_done' => $baseline->definition_of_done ?: 'The flow is tested, accessible, and linked to its approved PRD version.',
            ]);
            if ($baseline->isDirty()) {
                $baseline->save();
            }

            $pendingReview = $project->reviews()->firstOrCreate(['title' => 'Consider new external integration'], [
                'prd_version_id' => $approvedVersion->id,
                'description' => 'A future integration suggested during development.',
                'classification' => 'uncertain',
                'status' => 'pending',
                'reasoning' => 'Review against the approved baseline before implementing.',
            ]);
            if (! $pendingReview->prd_version_id) {
                $pendingReview->update(['prd_version_id' => $approvedVersion->id]);
            }

            $resolvedReview = $project->reviews()->firstOrCreate(['title' => 'Add unrelated billing module'], [
                'prd_version_id' => $approvedVersion->id,
                'description' => 'An optional billing capability outside the current focus.',
                'classification' => 'out_of_scope',
                'status' => 'resolved',
                'reasoning' => 'Billing is not part of the approved scope.',
            ]);
            if (! $resolvedReview->prd_version_id) {
                $resolvedReview->update(['prd_version_id' => $approvedVersion->id]);
            }

            DB::table('scope_decisions')->updateOrInsert(
                ['scope_review_id' => $resolvedReview->id],
                [
                    'prd_version_id' => $approvedVersion->id,
                    'user_id' => $user->id,
                    'decision' => 'move_to_backlog',
                    'reason' => 'Keep core workflow focused.',
                    'evidence' => 'The approved PRD does not include a billing workflow.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            $requests = [
                [
                    'title' => 'Siapkan paket konteks developer offline',
                    'description' => 'Ekspor ringkasan tugas, keputusan, dan batasan tanpa mengirim data ke layanan eksternal.',
                    'status' => 'proposed',
                    'scope_review_id' => $pendingReview->id,
                    'decision_reason' => null,
                    'decided_at' => null,
                    'decided_by' => null,
                    'draft_prd_version_id' => null,
                ],
                [
                    'title' => 'Pisahkan keputusan perubahan dari revisi PRD',
                    'description' => 'Perubahan yang disetujui menghasilkan draf revisi yang dapat ditinjau sebelum PRD diperbarui.',
                    'status' => 'approved',
                    'scope_review_id' => null,
                    'decision_reason' => 'Perubahan mendukung proses persetujuan scope yang dapat diaudit.',
                    'decided_at' => now()->subDays(1),
                    'decided_by' => $user->id,
                    'draft_prd_version_id' => $draftVersion->id,
                ],
                [
                    'title' => 'Tambahkan modul billing ke proyek',
                    'description' => 'Usulan fitur billing yang belum masuk ruang lingkup.',
                    'status' => 'rejected',
                    'scope_review_id' => $resolvedReview->id,
                    'decision_reason' => 'Usulan ini berada di luar baseline yang disetujui.',
                    'decided_at' => now()->subDays(2),
                    'decided_by' => $user->id,
                    'draft_prd_version_id' => null,
                ],
            ];

            foreach ($requests as $request) {
                ChangeRequest::query()->firstOrCreate(
                    ['project_id' => $project->id, 'title' => $request['title']],
                    $request + ['requested_by' => $user->id, 'source_prd_version_id' => $approvedVersion->id],
                );
            }

            DocumentTaskImport::query()->firstOrCreate(
                ['project_id' => $project->id, 'source_hash' => hash('sha256', 'devflow-demo-prd-task-import-v1')],
                [
                    'user_id' => $user->id,
                    'prd_version_id' => $approvedVersion->id,
                    'draft_tasks' => [
                        ['title' => 'Review offline context export', 'description' => 'Check that local project context can be copied safely.'],
                        ['title' => 'Add context format preview', 'description' => 'Compare developer, assistant, and team context formats.'],
                    ],
                    'committed_at' => null,
                    'created_count' => 0,
                ],
            );

            $task = $project->tasks()->firstWhere('title', 'Add project resume evidence');
            if ($task) {
                if (! $task->assignee_id) {
                    $task->update(['assignee_id' => $teammate->id]);
                }

                $task->comments()->firstOrCreate(['user_id' => $user->id, 'body' => 'Tautkan bukti keputusan scope dan catatan sesi berikutnya.']);
                $task->comments()->firstOrCreate(['user_id' => $teammate->id, 'body' => 'Saya akan menambahkan bukti keputusan pada ringkasan tugas.']);
                $task->history()->firstOrCreate(
                    ['user_id' => $user->id, 'action' => 'updated', 'to_status' => $task->status],
                    ['from_status' => 'todo', 'note' => 'Prioritas dan status ditinjau saat perencanaan demo.'],
                );
            }

            $workspace->invitations()->updateOrCreate(
                ['email' => 'collaborator@devflow.local'],
                ['token_hash' => hash('sha256', 'devflow-demo-collaborator-invitation'), 'expires_at' => now()->addDays(7)],
            );

            $this->notifyOnce($user, 'demo.scope_review', 'Tinjauan scope menunggu keputusan', 'Periksa usulan integrasi terhadap baseline proyek.', route('projects.show', ['project' => $project, 'section' => 'scope'], false));
            $this->notifyOnce($user, 'demo.change_request', 'Change Request siap ditinjau', 'Paket konteks developer menunggu keputusan.', route('projects.show', ['project' => $project, 'section' => 'scope'], false));
            $this->notifyOnce($user, 'demo.task_comment', 'Komentar baru pada tugas', 'Dimas Developer menambahkan komentar pada tugas project resume evidence.', route('projects.show', ['project' => $project, 'section' => 'tasks'], false));
        });
    }

    private function notifyOnce(User $user, string $event, string $title, string $message, string $url): void
    {
        $exists = $user->notifications()
            ->where('type', WorkspaceActivityNotification::class)
            ->get()
            ->contains(fn ($notification): bool => ($notification->data['event'] ?? null) === $event);

        if (! $exists) {
            $user->notify(new WorkspaceActivityNotification($event, $title, $message, $url));
        }
    }
}
