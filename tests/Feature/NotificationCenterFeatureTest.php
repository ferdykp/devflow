<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Notifications\WorkspaceActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_comments_create_private_in_app_notifications_that_can_be_read_and_dismissed(): void
    {
        $project = Project::factory()->create();
        $author = $project->workspace->user;
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $task = $project->tasks()->create(['title' => 'Review login flow']);

        $this->actingAs($author)->post(route('task-comments.store', [$project, $task]), ['body' => 'Please review the fallback path.'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $member->id,
            'type' => 'App\\Notifications\\WorkspaceActivityNotification',
        ]);
        $this->assertSame(0, $author->unreadNotifications()->count());
        $notification = $member->fresh()->unreadNotifications()->firstOrFail();

        $this->actingAs($member)->get(route('notifications.index'))->assertOk()
            ->assertSee('Pusat notifikasi')->assertSee('Review login flow');
        $this->actingAs($member)->patch(route('notifications.read', $notification->id))
            ->assertRedirect(route('tasks.show', [$project, $task]));
        $this->assertSame(0, $member->fresh()->unreadNotifications()->count());

        $this->actingAs($member)->withHeader('Accept', 'application/json')->delete(route('notifications.dismiss', $notification->id))
            ->assertOk()->assertJsonPath('message', 'Notifikasi dihapus.');
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_users_cannot_read_or_dismiss_another_users_notifications(): void
    {
        $recipient = User::factory()->create();
        $otherUser = User::factory()->create();
        $recipient->notify(new WorkspaceActivityNotification(
            'test.event', 'Private', 'Private notice', route('dashboard'),
        ));
        $notification = $recipient->notifications()->firstOrFail();

        $this->actingAs($otherUser)->patch(route('notifications.read', $notification->id))->assertNotFound();
        $this->delete(route('notifications.dismiss', $notification->id))->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_read_all_only_updates_the_authenticated_users_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify(new WorkspaceActivityNotification('one', 'One', 'One', route('dashboard')));
        $otherUser->notify(new WorkspaceActivityNotification('two', 'Two', 'Two', route('dashboard')));

        $this->actingAs($user)->withHeader('Accept', 'application/json')->patch(route('notifications.read-all'))
            ->assertOk()->assertJsonPath('message', 'Semua notifikasi ditandai sudah dibaca.');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $otherUser->fresh()->unreadNotifications()->count());
    }
}
