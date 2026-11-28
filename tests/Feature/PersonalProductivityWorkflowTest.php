<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\FocusItem;
use App\Models\LearningLesson;
use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Models\PersonalGoal;
use App\Models\Routine;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalProductivityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_inbox_task_and_note_are_private_and_timezone_is_saved(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $goal = PersonalGoal::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->post(route('inbox.store'), [
            'title' => 'Rencana belajar privat',
            'priority' => 'high',
            'personal_goal_id' => $goal->id,
            'tags' => 'belajar, minggu ini',
            'checklist_text' => "Buka materi\nKerjakan latihan",
        ])->assertSessionHasNoErrors();

        $task = $owner->personalTasks()->sole();
        $this->assertNull($task->project_id);
        $this->assertSame(['belajar', 'minggu ini'], $task->tags);
        $this->assertSame(['Buka materi', 'Kerjakan latihan'], array_column($task->checklist, 'title'));
        $this->actingAs($owner)->post(route('inbox.comments.store', $task), ['body' => 'Simpan referensi di sini.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Simpan referensi di sini.', $task->comments()->sole()->body);
        $this->actingAs($owner)->get(route('tasks.index', ['status' => 'open', 'view' => 'list']))->assertOk()->assertSee('Dibuat')->assertSee('Simpan referensi di sini.');
        $this->actingAs($stranger)->get(route('tasks.index', ['status' => 'open', 'view' => 'list']))->assertDontSee('Rencana belajar privat');
        $this->patch(route('inbox.update', $task), ['status' => 'done'])->assertNotFound();
        $this->post(route('inbox.comments.store', $task), ['body' => 'Tidak boleh menulis.'])->assertNotFound();

        $this->actingAs($owner)->post(route('personal-notes.store'), [
            'title' => 'Catatan privat',
            'content' => 'Hanya dapat dilihat pemilik.',
            'tags_input' => 'rahasia, belajar',
            'personal_goal_id' => $goal->id,
        ])->assertSessionHasNoErrors();

        $note = $owner->personalNotes()->sole();
        $this->assertSame(['rahasia', 'belajar'], $note->tags);
        $this->actingAs($owner)->withHeader('Accept', 'application/json')->patch(route('personal-notes.update', $note), ['is_pinned' => true])
            ->assertOk()->assertJsonPath('note.is_pinned', true);
        $this->assertTrue($note->fresh()->is_pinned);
        $this->actingAs($stranger)->patch(route('personal-notes.update', $note), ['title' => 'Diubah'])->assertNotFound();
        $this->actingAs($owner)->patch(route('settings.update'), ['timezone' => 'Asia/Jakarta'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Asia/Jakarta', $owner->fresh()->timezone);

        $this->actingAs($owner)->patch(route('settings.profile.update'), [
            'name' => 'Akun Pemilik',
            'email' => 'owner-renamed@example.test',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Akun Pemilik', $owner->fresh()->name);
        $this->assertSame('owner-renamed@example.test', $owner->fresh()->email);
        $this->patch(route('settings.profile.update'), [
            'name' => 'Akun Pemilik',
            'email' => $stranger->email,
        ])->assertJsonValidationErrors('email');
        $this->assertSame('owner-renamed@example.test', $owner->fresh()->email);
    }

    public function test_completing_a_repeating_personal_task_creates_only_the_next_occurrence(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $this->actingAs($user)->post(route('inbox.store'), [
            'title' => 'Review mingguan',
            'priority' => 'normal',
            'due_date' => today('Asia/Jakarta')->toDateString(),
            'recurrence_frequency' => 'daily',
            'recurrence_interval' => 1,
        ])->assertSessionHasNoErrors();

        $task = $user->personalTasks()->sole();
        $this->patch(route('inbox.update', $task), ['status' => 'done'])->assertSessionHasNoErrors();

        $this->assertSame(2, $user->personalTasks()->count());
        $nextTask = $user->personalTasks()->where('id', '!=', $task->id)->sole();
        $this->assertSame('todo', $nextTask->status);
        $this->assertSame(today('Asia/Jakarta')->addDay()->toDateString(), $nextTask->due_date->toDateString());
        $this->assertSame(2, $nextTask->recurrence_index);
    }

    public function test_calendar_marks_a_single_repeating_occurrence_complete_and_keeps_the_series(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $start = CarbonImmutable::now('Asia/Jakarta')->addHours(2)->startOfHour();
        $event = $user->calendarEvents()->create([
            'title' => 'Latihan bahasa',
            'starts_at' => $start->utc(),
            'ends_at' => $start->addHour()->utc(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'planned',
            'recurrence_rule' => ['frequency' => 'daily', 'interval' => 1],
            'recurrence_series_id' => (string) Str::uuid(),
        ]);

        $this->actingAs($user)->get(route('calendar.index', ['date' => $start->toDateString(), 'view' => 'day']))
            ->assertOk()->assertSee('Latihan bahasa');
        $this->withHeader('Accept', 'application/json')->post(route('calendar.events.complete', $event), [
            'original_start' => $start->utc()->format('Y-m-d H:i:s'),
            'actual_minutes' => 35,
        ])->assertOk()->assertJsonPath('status', 'completed');

        $this->assertSame('planned', $event->fresh()->status);
        $this->assertSame('completed', $event->exceptions()->sole()->action);
        $this->assertSame(35, $event->exceptions()->sole()->actual_duration_minutes);
        $this->get(route('calendar.index', ['date' => $start->toDateString(), 'view' => 'day']))
            ->assertOk()->assertSee('Selesai')->assertDontSee('Tandai selesai');

        $oneOff = CalendarEvent::factory()->for($user, 'owner')->create();
        $replacementStart = $start->addDay()->setTime(14, 0);
        $this->patch(route('calendar.events.update', $oneOff), [
            'title' => 'Sesi dipindahkan',
            'description' => 'Perubahan waktu',
            'starts_at' => $replacementStart->format('Y-m-d\\TH:i'),
            'ends_at' => $replacementStart->addHour()->format('Y-m-d\\TH:i'),
            'timezone' => 'Asia/Jakarta',
            'is_flexible' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame('Sesi dipindahkan', $oneOff->fresh()->title);
        $this->assertSame($replacementStart->utc()->getTimestamp(), $oneOff->fresh()->starts_at->getTimestamp());
    }

    public function test_core_personal_pages_render_and_routine_check_in_is_idempotent(): void
    {
        $user = User::factory()->create();
        $goal = PersonalGoal::factory()->for($user, 'owner')->create();
        $routine = Routine::factory()->for($user, 'owner')->create(['personal_goal_id' => $goal->id]);
        $path = LearningPath::factory()->for($user, 'owner')->create(['personal_goal_id' => $goal->id]);
        $module = LearningModule::factory()->for($path, 'path')->create();
        LearningLesson::factory()->for($module, 'module')->create([
            'title' => 'Dasar pemecahan masalah',
            'status' => 'competency_demonstrated',
            'competency_evidence' => 'Menyelesaikan latihan sendiri',
        ]);

        $this->actingAs($user);
        foreach (['dashboard', 'my-day.index', 'tasks.index', 'routines.index', 'goals.index', 'learning.index', 'personal-notes.index', 'calendar.index', 'weekly-review.index', 'smart-coach.index', 'settings.index'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->withHeader('Accept', 'application/json')->post(route('routines.check-in', $routine), ['status' => 'completed', 'activity_level' => 'minimum'])
            ->assertOk()->assertJsonPath('status', 'completed');
        $this->post(route('routines.check-in', $routine), ['status' => 'completed', 'note' => 'Sesi singkat'])
            ->assertSessionHasNoErrors();
        $this->assertCount(1, $routine->checkIns()->get());
        $this->assertSame('completed', $routine->checkIns()->sole()->status);
        $skippedRoutine = Routine::factory()->for($user, 'owner')->create();
        $this->post(route('routines.check-in', $skippedRoutine), ['status' => 'skipped', 'note' => 'Jadwal berubah'])
            ->assertSessionHasNoErrors();
        $this->assertSame('skipped', $skippedRoutine->checkIns()->sole()->status);
    }

    public function test_calendar_async_task_scheduling_returns_the_rendered_event_card(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withHeader('Accept', 'application/json')->post(route('calendar.events.store'), [
            'title' => 'Fokus menulis',
            'starts_at' => '2026-11-28T10:00',
            'ends_at' => '2026-11-28T11:00',
            'timezone' => 'Asia/Jakarta',
        ])->assertCreated()
            ->assertJsonPath('event.title', 'Fokus menulis')
            ->assertJsonPath('event.id', fn ($id) => is_int($id))
            ->assertJsonPath('event.html', fn ($html) => str_contains($html, 'Fokus menulis'));
    }

    public function test_task_hub_has_open_and_all_status_filters_and_legacy_inbox_redirects_to_open(): void
    {
        $user = User::factory()->create();
        $user->personalTasks()->create(['title' => 'Tugas terbuka', 'priority' => 'normal', 'status' => 'todo']);
        $user->personalTasks()->create(['title' => 'Tugas selesai', 'priority' => 'normal', 'status' => 'done']);

        $this->actingAs($user)->get(route('inbox.index', ['q' => 'Tugas']))
            ->assertRedirect(route('tasks.index', ['q' => 'Tugas', 'status' => 'open']));

        $this->get(route('tasks.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee('Tugas terbuka')
            ->assertDontSee('Tugas selesai')
            ->assertViewHas('status', 'open')
            ->assertSee('Papan status')
            ->assertSee('data-task-dropzone="todo"', false);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Tugas terbuka')
            ->assertSee('Tugas selesai')
            ->assertViewHas('status', 'all')
            ->assertSee('Belum selesai')
            ->assertSee('Semua status')
            ->assertDontSee('Kotak masuk')
            ->assertSee('data-task-dropzone="todo"', false);

        $this->get(route('tasks.index', ['view' => 'list']))
            ->assertOk()
            ->assertSee('Daftar tugas')
            ->assertDontSee('data-task-dropzone="todo"', false);
    }

    public function test_my_day_priorities_and_scheduled_items_use_real_user_data_in_time_order(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $task = $user->personalTasks()->create([
            'title' => 'Tinjau pull request',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => $today->toDateString(),
            'scheduled_at' => $today->setTime(9, 30)->utc(),
        ]);
        FocusItem::query()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'focus_date' => $today->toDateString(),
        ]);
        $user->calendarEvents()->create([
            'title' => 'Stand-up',
            'starts_at' => $today->setTime(8, 30)->utc(),
            'ends_at' => $today->setTime(9, 0)->utc(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'planned',
        ]);

        $this->actingAs($user)->get(route('my-day.index'))
            ->assertOk()
            ->assertViewHas('priorityTasks', fn ($tasks) => $tasks->contains('id', $task->id))
            ->assertViewHas('agendaItems', fn ($items) => $items->pluck('model.title')->all() === ['Stand-up', 'Tinjau pull request']);
    }
}
