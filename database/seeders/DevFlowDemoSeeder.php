<?php

namespace Database\Seeders;

use App\Models\{User, Workspace, Project, FocusItem};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Idempotent demo records for UI exploration. This is NOT real GitHub/AI data.
 * Run explicitly: php artisan db:seed --class=DevFlowDemoSeeder
 */
class DevFlowDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! filter_var(env('DEVFLOW_ALLOW_PRODUCTION_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Demo seeding is disabled in production.');
        }

        DB::transaction(function (): void {
            // Avoid overwriting a real user's password if the email already exists.
            $email = 'demo@devflow.local';
            $user = User::query()->firstWhere('email', $email);
            if (! $user) {
                $user = User::query()->create([
                    'name' => 'DevFlow Demo',
                    'email' => $email,
                    'password' => Hash::make('Devdemo123.'),
                    'email_verified_at' => now(),
                ]);
            }

            $specimens = [
                [
                    'workspace' => 'Product Lab',
                    'name' => 'DevFlow',
                    'description' => 'A focused command workspace for software development.',
                    'goal' => 'Resume work quickly and keep changes aligned with approved scope.',
                    'status' => 'active',
                    'priority' => 'high',
                    'milestones' => ['Workspace foundation', 'Developer workflows', 'Integrations'],
                    'tasks' => [
                        ['Refine command navigation', 'in_progress', 'high', 0],
                        ['Verify mobile keyboard navigation', 'in_review', 'high', 0],
                        ['Add project resume evidence', 'todo', 'normal', 1],
                        ['Test cross-project authorization', 'done', 'high', 1],
                        ['Connect a real GitHub App', 'blocked', 'normal', 2],
                        ['Polish scope review copy', 'todo', 'low', 1],
                    ],
                    'notes' => ['Navigation decisions' => 'Navigation rail holds global destinations. Project pages use contextual navigation.', 'Next session' => 'Review keyboard focus and screen-reader labels before polishing micro-interactions.'],
                    'ideas' => [['Offline read-only project snapshots', 'research'], ['VS Code extension integration', 'future'], ['Optional issue import', 'someday']],
                    'baseline' => ['in' => 'Projects, tasks, notes, daily focus, PRD versions and GitHub activity.', 'out' => 'Billing, CRM, time tracking and automatic PRD approvals.'],
                    'prd' => true,
                ],
                [
                    'workspace' => 'Product Lab',
                    'name' => 'Ledger',
                    'description' => 'A personal finance tracking application.',
                    'goal' => 'Capture transactions quickly and review spending patterns.',
                    'status' => 'active',
                    'priority' => 'high',
                    'milestones' => ['Core transactions', 'Reports', 'Messaging'],
                    'tasks' => [
                        ['Add expense category filter', 'in_progress', 'high', 0],
                        ['Check transaction export', 'todo', 'normal', 1],
                        ['Verify mobile form validation', 'done', 'normal', 0],
                        ['Draft WhatsApp OTP flow', 'todo', 'high', 2],
                        ['Waiting for messaging provider setup', 'blocked', 'normal', 2],
                    ],
                    'notes' => ['Implementation note' => 'Keep financial inputs validated server-side and avoid storing secrets in browser state.'],
                    'ideas' => [['Predictive spending analysis', 'research'], ['Receipt recognition', 'future']],
                    'baseline' => null,
                    'prd' => false,
                ],
                [
                    'workspace' => 'Client Work',
                    'name' => 'GrowPOS',
                    'description' => 'Multi-tenant point of sale and inventory workflows.',
                    'goal' => 'Ship stable cashier and reporting workflows.',
                    'status' => 'active',
                    'priority' => 'normal',
                    'milestones' => ['Tenant management', 'Checkout', 'Reports'],
                    'tasks' => [
                        ['Review tenant isolation', 'done', 'high', 0],
                        ['Improve cashier checkout states', 'in_progress', 'high', 1],
                        ['Add stock adjustment report', 'todo', 'normal', 2],
                        ['Audit export permissions', 'todo', 'high', 2],
                    ],
                    'notes' => ['Release checklist' => 'Verify tenant boundary, permissions, queue and database backups before release.'],
                    'ideas' => [['Self-service onboarding wizard', 'future'], ['Barcode workflow experiments', 'someday']],
                    'baseline' => ['in' => 'Cashier, catalog, inventory and reporting.', 'out' => 'Marketplace and payroll.'],
                    'prd' => true,
                ],
                [
                    'workspace' => 'Experiments',
                    'name' => 'Signal Lab',
                    'description' => 'Audio waveform and frequency analysis experiment.',
                    'goal' => 'Explore microphone signal processing and visualization.',
                    'status' => 'paused',
                    'priority' => 'low',
                    'milestones' => ['Research', 'Prototype'],
                    'tasks' => [['Read microphone permissions documentation', 'todo', 'normal', 0], ['Prototype FFT viewer', 'todo', 'low', 1]],
                    'notes' => ['Research log' => 'A browser-based audio analyzer could show waveform, FFT spectrum and measured levels.'],
                    'ideas' => [['Speech-to-text overlay', 'research']],
                    'baseline' => null,
                    'prd' => false,
                ],
                [
                    'workspace' => 'Experiments',
                    'name' => 'Portfolio Archive',
                    'description' => 'Past portfolio concept kept for reference.',
                    'goal' => 'Preserve completed design decisions.',
                    'status' => 'archived',
                    'priority' => 'low',
                    'milestones' => ['Initial release'],
                    'tasks' => [['Publish landing page', 'done', 'normal', 0]],
                    'notes' => ['Archive note' => 'Read-only reference project.'],
                    'ideas' => [],
                    'baseline' => null,
                    'prd' => false,
                ],
            ];

            $projectCount = 0;
            foreach ($specimens as $row) {
                $workspace = Workspace::query()->firstOrCreate(['user_id' => $user->id, 'name' => $row['workspace']]);
                $project = $workspace->projects()->firstOrCreate(['name' => $row['name']], [
                    'description' => $row['description'],
                    'goal' => $row['goal'],
                    'status' => $row['status'],
                    'priority' => $row['priority'],
                    'target_date' => now()->addWeeks(6)->toDateString(),
                ]);
                $projectCount++;
                $milestones = [];
                foreach ($row['milestones'] as $offset => $name) {
                    $milestones[] = $project->milestones()->firstOrCreate(['title' => $name], [
                        'description' => 'Demo milestone for ' . $row['name'],
                        'due_date' => now()->addWeeks($offset + 2)->toDateString(),
                    ]);
                }
                foreach ($row['tasks'] as $index => [$title, $status, $priority, $milestoneIndex]) {
                    $project->tasks()->firstOrCreate(['title' => $title], [
                        'milestone_id' => $milestones[$milestoneIndex]->id,
                        'description' => 'Sample development task used for the DevFlow interface preview.',
                        'status' => $status,
                        'priority' => $priority,
                        'weight' => $priority === 'high' ? 3 : 1,
                        'due_date' => now()->addDays($index * 2 + 1)->toDateString(),
                        'completed_at' => $status === 'done' ? now()->subDays(2) : null,
                    ]);
                }
                foreach ($row['notes'] as $title => $content) {
                    $project->notes()->firstOrCreate(['title' => $title], ['user_id' => $user->id, 'content' => $content]);
                }
                foreach ($row['ideas'] as [$title, $bucket]) {
                    $project->backlogItems()->firstOrCreate(['title' => $title], [
                        'description' => 'Demo idea — not part of the active scope.',
                        'bucket' => $bucket,
                        'status' => 'proposed',
                        'source' => 'demo',
                    ]);
                }
                if ($row['baseline'] !== null) {
                    $project->baseline()->firstOrCreate([], [
                        'in_scope' => $row['baseline']['in'],
                        'out_of_scope' => $row['baseline']['out'],
                    ]);
                }
                if ($row['prd']) {
                    $document = $project->documents()->firstOrCreate(['title' => 'Product Requirements']);
                    $markdown = "# {$row['name']} — Product Requirements\n\n## Goal\n{$row['goal']}\n\n## Key workflows\n{$row['description']}\n\n## Scope\n" . ($row['baseline']['in'] ?? 'Project planning only.');
                    $document->versions()->firstOrCreate(['version_number' => 1], [
                        'status' => 'approved',
                        'content_markdown' => $markdown,
                        'content_hash' => hash('sha256', $markdown),
                        'approved_by' => $user->id,
                        'approved_at' => now()->subDays(12),
                    ]);
                    $document->versions()->firstOrCreate(['version_number' => 2], [
                        'status' => 'draft',
                        'content_markdown' => $markdown . "\n\n## Proposed update\nReview the upcoming iteration.",
                        'content_hash' => hash('sha256', $markdown . "\n\n## Proposed update\nReview the upcoming iteration."),
                    ]);
                }
                // Demo activities are never represented as authenticated GitHub events.
                foreach (['Task list reviewed', 'Milestone planning updated', 'Project notes updated'] as $index => $title) {
                    $project->activities()->firstOrCreate(['source' => 'demo', 'source_key' => 'seed-' . $index], [
                        'type' => 'note',
                        'title' => '[Demo] ' . $title,
                        'description' => 'Illustrative development activity; not synchronized from GitHub.',
                        'occurred_at' => now()->subHours(3 + $index * 18),
                        'metadata' => ['demo' => true],
                    ]);
                }
                if ($row['baseline'] !== null) {
                    $pending = $project->reviews()->firstOrCreate(['title' => 'Consider new external integration'], [
                        'description' => 'A future integration suggested during development.',
                        'classification' => 'uncertain',
                        'status' => 'pending',
                        'reasoning' => 'Review against the approved baseline before implementing.',
                    ]);
                    $resolved = $project->reviews()->firstOrCreate(['title' => 'Add unrelated billing module'], [
                        'description' => 'An optional billing capability outside the current focus.',
                        'classification' => 'out_of_scope',
                        'status' => 'resolved',
                        'reasoning' => 'Billing is not part of the approved scope.',
                    ]);
                    DB::table('scope_decisions')->updateOrInsert(
                        ['scope_review_id' => $resolved->id],
                        [
                            'user_id' => $user->id,
                            'decision' => 'move_to_backlog',
                            'reason' => 'Keep core workflow focused.',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    $project->events()->firstOrCreate(['action' => 'demo_scope_review'], [
                        'user_id' => $user->id,
                        'metadata' => ['demo' => true, 'scope_review_id' => $resolved->id],
                    ]);
                }
            }
            // Maximum three current focus tasks, all from an active project.
            $tasks = $user->workspaces()->with('projects.tasks')->get()->flatMap(
                fn($w) => $w->projects->where('status', 'active')->flatMap(
                    fn($p) => $p->tasks->whereIn('status', ['todo', 'in_progress', 'in_review'])->where('priority', 'high')
                )
            )->take(3);
            foreach ($tasks as $task) {
                FocusItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'focus_date' => today()->toDateString(),
                ]);
            }
            $this->command?->info("Demo created: {$projectCount} projects. Login: demo@devflow.local / DevFlowDemo2026! (local only)");
        });
    }
}
