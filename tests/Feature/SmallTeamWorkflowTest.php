<?php

namespace Tests\Feature;

use App\Models\FocusItem;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmallTeamWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_member_and_acceptance_requires_the_named_account(): void
    {
        $project = Project::factory()->create();
        $workspace = $project->workspace;
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $this->actingAs($workspace->user)->post(route('workspaces.invitations.store', $workspace), ['email' => strtoupper($member->email)])->assertRedirect(route('workspaces.members.show', $workspace))->assertSessionHasNoErrors();
        $url = session('invitation_url');
        $invitation = $workspace->invitations()->sole();
        $this->assertSame($member->email, $invitation->email);
        $this->assertNotNull($url);
        $this->assertStringNotContainsString($invitation->token_hash, $url);
        $this->assertSame(0, $workspace->members()->count());
        $this->actingAs($outsider)->post($url)->assertForbidden();
        $this->actingAs($member)->get($url)->assertOk()->assertSee('Terima undangan');
        $this->post($url)->assertRedirect(route('workspaces.members.show', $workspace));
        $this->post($url)->assertRedirect();
        $this->assertDatabaseCount('user_workspace', 1);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->get(route('projects.show', $project))->assertOk();
        $this->get(route('projects.index'))->assertSee($project->name)->assertSee($workspace->user->name);
        $this->get(route('dashboard'))->assertSee($project->name);
        $this->post(route('projects.store'), ['name' => 'Shared project', 'workspace_id' => $workspace->id])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('projects', ['workspace_id' => $workspace->id, 'name' => 'Shared project']);
    }

    public function test_expired_revoked_and_reissued_invites_do_not_grant_access(): void
    {
        $project = Project::factory()->create();
        $workspace = $project->workspace;
        $member = User::factory()->create();
        $token = Str::random(64);
        WorkspaceInvitation::factory()->for($workspace)->create(['email' => $member->email, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->subSecond()]);
        $this->actingAs($member)->post(route('invitations.accept', $token))->assertGone();
        $this->get(route('invitations.show', $token))->assertGone()->assertSee('Undangan tidak tersedia');
        $this->actingAs($workspace->user)->post(route('workspaces.invitations.store', $workspace), ['email' => $member->email]);
        $firstUrl = session('invitation_url');
        $this->post(route('workspaces.invitations.store', $workspace), ['email' => $member->email]);
        $secondUrl = session('invitation_url');
        $this->actingAs($member)->post($firstUrl)->assertNotFound();
        $invitation = $workspace->invitations()->sole();
        $this->actingAs($workspace->user)->delete(route('workspaces.invitations.destroy', [$workspace, $invitation]))->assertRedirect();
        $this->actingAs($member)->post($secondUrl)->assertNotFound();
        $this->assertDatabaseCount('user_workspace', 0);
    }

    public function test_members_cannot_manage_other_members_or_invite_and_owner_cannot_be_removed(): void
    {
        $project = Project::factory()->create();
        $workspace = $project->workspace;
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $workspace->members()->attach([$member->id, $otherMember->id]);
        $this->actingAs($member)->post(route('workspaces.invitations.store', $workspace), ['email' => 'other@example.test'])->assertNotFound();
        $this->delete(route('workspaces.members.destroy', [$workspace, $otherMember]))->assertForbidden();
        $this->actingAs($workspace->user)->delete(route('workspaces.members.destroy', [$workspace, $workspace->user]))->assertUnprocessable();
        $this->assertDatabaseCount('user_workspace', 2);
    }

    public function test_revocation_clears_assignment_focus_and_all_project_access(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $task = Task::factory()->for($project)->create(['assignee_id' => $member->id]);
        FocusItem::create(['user_id' => $member->id, 'task_id' => $task->id, 'focus_date' => today()]);
        $this->actingAs($project->workspace->user)->delete(route('workspaces.members.destroy', [$project->workspace, $member]))->assertRedirect();
        $this->assertNull($task->fresh()->assignee_id);
        $this->assertDatabaseCount('focus_items', 0);
        $this->actingAs($member)->get(route('projects.show', $project))->assertNotFound();
        $this->get(route('tasks.show', [$project, $task]))->assertNotFound();
        $this->get(route('projects.index'))->assertDontSee($project->name);
        $this->post(route('focus.toggle', $task))->assertNotFound();
        $this->assertDatabaseCount('user_workspace', 0);
    }

    public function test_assignment_only_accepts_current_workspace_participants(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $project->workspace->members()->attach($member);
        $this->actingAs($member)->post(route('tasks.store', $project), ['title' => 'Shared task', 'assignee_id' => $member->id])->assertSessionHasNoErrors();
        $task = $project->tasks()->sole();
        $this->assertSame($member->id, $task->assignee_id);
        $this->patch(route('tasks.update', [$project, $task]), ['assignee_id' => $outsider->id])->assertSessionHasErrors('assignee_id');
        $this->assertSame($member->id, $task->fresh()->assignee_id);
        $this->patch(route('tasks.update', [$project, $task]), ['assignee_id' => null])->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->assignee_id);
        $this->get(route('focus.index'))->assertSee('Shared task');
    }

    public function test_comments_are_shared_escaped_and_only_author_or_owner_can_delete(): void
    {
        $task = Task::factory()->create();
        $project = $task->project;
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $outsider = User::factory()->create();
        $project->workspace->members()->attach([$member->id, $otherMember->id]);
        $this->actingAs($member)->post(route('task-comments.store', [$project, $task]), ['body' => '<script>alert(1)</script>'])->assertRedirect(route('tasks.show', [$project, $task]));
        $comment = $task->comments()->sole();
        $this->assertSame($member->id, $comment->user_id);
        $this->actingAs($otherMember)->get(route('tasks.show', [$project, $task]))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $this->delete(route('task-comments.destroy', [$project, $task, $comment]))->assertForbidden();
        $this->actingAs($outsider)->post(route('task-comments.store', [$project, $task]), ['body' => 'Injected'])->assertNotFound();
        $this->actingAs($member)->post(route('task-comments.store', [$project, $task]), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($project->workspace->user)->delete(route('task-comments.destroy', [$project, $task, $comment]))->assertRedirect();
        $this->assertDatabaseCount('task_comments', 0);
    }

    public function test_filters_show_matching_tasks_without_cross_project_results(): void
    {
        $project = Project::factory()->create();
        $owner = $project->workspace->user;
        Task::factory()->for($project)->create(['title' => 'Login utama', 'priority' => 'high', 'status' => 'todo', 'assignee_id' => $owner->id]);
        Task::factory()->for($project)->create(['title' => 'Login selesai', 'priority' => 'normal', 'status' => 'done']);
        Task::factory()->create(['title' => 'Login rahasia', 'priority' => 'high']);
        $this->actingAs($owner)->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'search' => 'Login', 'priority' => 'high', 'status' => 'todo', 'assignee' => 'me']))->assertOk()->assertSee('Login utama')->assertDontSee('Login selesai')->assertDontSee('Login rahasia');
        $this->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'assignee' => 'unassigned']))->assertSee('Login selesai')->assertDontSee('Login utama');
    }

    public function test_used_invitation_cannot_restore_a_revoked_membership(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $token = Str::random(64);
        WorkspaceInvitation::factory()->for($project->workspace)->create(['email' => $member->email, 'token_hash' => hash('sha256', $token), 'accepted_at' => now()]);
        $this->actingAs($project->workspace->user)->delete(route('workspaces.members.destroy', [$project->workspace, $member]))->assertRedirect();
        $this->actingAs($member)->post(route('invitations.accept', $token))->assertGone();
        $this->assertDatabaseCount('user_workspace', 0);
    }

    public function test_guest_cannot_open_document_drafts_or_membership_pages(): void
    {
        $project = Project::factory()->create();
        $this->get(route('task-imports.create', $project))->assertRedirect(route('login'));
        $this->get(route('workspaces.members.show', $project->workspace))->assertRedirect(route('login'));
        $this->post(route('task-imports.store', $project), ['content_markdown' => '- Injected'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('document_task_imports', 0);
    }
}
