<?php

namespace Tests\Feature;

use App\Models\LearningAssessment;
use App\Models\LearningLesson;
use App\Models\LearningModule;
use App\Models\LearningPath;
use App\Models\LearningSession;
use App\Models\PersonalGoal;
use App\Models\PersonalNote;
use App\Models\Routine;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StitchScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_detail_and_preferences_screens_render_from_owned_records(): void
    {
        $user = User::factory()->create();
        $goal = PersonalGoal::factory()->for($user, 'owner')->create();
        $routine = Routine::factory()->for($user, 'owner')->create(['personal_goal_id' => $goal->id]);
        $path = LearningPath::factory()->for($user, 'owner')->create(['personal_goal_id' => $goal->id]);
        $module = LearningModule::factory()->for($path, 'path')->create();
        $lesson = LearningLesson::factory()->for($module, 'module')->create();
        $task = Task::factory()->for($user, 'owner')->create(['project_id' => null, 'personal_goal_id' => $goal->id]);
        $note = PersonalNote::factory()->for($user, 'owner')->create([
            'personal_goal_id' => $goal->id,
            'task_id' => $task->id,
            'learning_lesson_id' => $lesson->id,
        ]);
        LearningSession::factory()->for($user, 'owner')->for($path, 'path')->create(['learning_lesson_id' => $lesson->id]);
        LearningAssessment::factory()->for($user, 'owner')->for($lesson, 'lesson')->create();

        $this->actingAs($user);

        foreach ([
            route('onboarding.welcome'),
            route('onboarding.setup'),
            route('personal-tasks.show', $task),
            route('routines.show', $routine),
            route('goals.show', $goal),
            route('learning.paths.show', $path),
            route('learning.modules.show', [$path, $module]),
            route('learning.lessons.show', $lesson),
            route('learning.sessions.index'),
            route('learning.assessments.index'),
            route('personal-notes.show', $note),
            route('settings.integrations'),
            route('notifications.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_private_detail_screens_do_not_expose_another_users_records(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $goal = PersonalGoal::factory()->for($owner, 'owner')->create();

        $this->actingAs($anotherUser)->get(route('goals.show', $goal))->assertNotFound();
    }
}
