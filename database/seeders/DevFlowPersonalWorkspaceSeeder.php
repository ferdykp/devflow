<?php

namespace Database\Seeders;

use App\Models\CoachRecommendation;
use App\Models\DailyReflection;
use App\Models\GoalMilestone;
use App\Models\GoalProgressEntry;
use App\Models\LearningAssessment;
use App\Models\LearningSession;
use App\Models\PersonalNote;
use App\Models\RoutineCheckIn;
use App\Models\User;
use App\Models\WeeklyReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DevFlowPersonalWorkspaceSeeder extends Seeder
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

        if (! $user) {
            throw new RuntimeException('Run DevFlowDemoSeeder before DevFlowPersonalWorkspaceSeeder.');
        }

        DB::transaction(function () use ($user): void {
            $timezone = $user->timezone ?: 'Asia/Jakarta';
            $today = today($timezone);

            $goal = $user->personalGoals()->firstOrCreate(['title' => 'Bangun ritme belajar backend'], [
                'description' => 'Belajar konsisten sambil menerapkan materi pada proyek nyata.',
                'category' => 'learning',
                'horizon' => 'medium',
                'start_date' => $today->copy()->subWeeks(3)->toDateString(),
                'target_date' => $today->copy()->addMonths(2)->toDateString(),
                'progress_mode' => 'numeric',
                'current_value' => 2,
                'target_value' => 5,
                'unit' => 'modul',
                'status' => 'active',
                'review_every_days' => 7,
                'next_review_on' => $today->copy()->addDays(3)->toDateString(),
            ]);

            foreach ([
                ['Selesaikan dasar Laravel', 'Pelajari routing, controller, dan Blade.', $today->copy()->subWeek(), true, 0],
                ['Pahami data dan relasi', 'Bangun fitur dengan Eloquent dan validasi.', $today->copy()->addWeeks(2), false, 1],
                ['Kirim fitur kecil ke proyek', 'Terapkan satu hasil belajar pada DevFlow.', $today->copy()->addMonth(), false, 2],
            ] as [$title, $description, $targetDate, $isComplete, $sortOrder]) {
                GoalMilestone::query()->firstOrCreate(
                    ['personal_goal_id' => $goal->id, 'title' => $title],
                    [
                        'description' => $description,
                        'target_date' => $targetDate->toDateString(),
                        'completed_at' => $isComplete ? $today->copy()->subDays(2) : null,
                        'sort_order' => $sortOrder,
                    ],
                );
            }

            foreach ([
                [$today->copy()->subDays(6), 1, 'Menyelesaikan latihan routing.'],
                [$today->copy()->subDays(3), 2, 'Menerapkan validasi pada form.'],
                [$today, 2, 'Menghubungkan materi dengan alur proyek.'],
            ] as [$recordedOn, $value, $evidence]) {
                GoalProgressEntry::query()->firstOrCreate(
                    ['personal_goal_id' => $goal->id, 'recorded_on' => $recordedOn->toDateString()],
                    ['value' => $value, 'evidence' => $evidence],
                );
            }

            $path = $user->learningPaths()->firstOrCreate(['title' => 'Laravel untuk Developer'], [
                'personal_goal_id' => $goal->id,
                'subject' => 'Laravel',
                'description' => 'Jalur belajar praktis dari routing hingga pengujian alur aplikasi.',
                'status' => 'active',
            ]);

            $fundamentals = $path->modules()->firstOrCreate(['title' => 'Fondasi aplikasi'], [
                'description' => 'Struktur aplikasi, routing, dan validasi.',
                'sort_order' => 0,
            ]);
            $dataModule = $path->modules()->firstOrCreate(['title' => 'Data dan alur kerja'], [
                'description' => 'Relasi data dan perubahan yang aman.',
                'sort_order' => 1,
            ]);

            $routingLesson = $fundamentals->lessons()->firstOrCreate(['title' => 'Routing dan controller'], [
                'objectives' => 'Memetakan URL ke aksi controller dan validasi request.',
                'materials_markdown' => "## Latihan\nBuat route bernama, validasi input, lalu tampilkan hasilnya pada Blade.",
                'reference_url' => 'https://laravel.com/docs/routing',
                'status' => 'competency_demonstrated',
                'competency_evidence' => 'Berhasil membuat alur form dengan validasi server-side.',
                'status_updated_at' => $today->copy()->subDays(3),
                'next_review_at' => $today->copy()->addDays(7),
                'knowledge_gap' => false,
            ]);
            $relationsLesson = $dataModule->lessons()->firstOrCreate(['title' => 'Relasi Eloquent'], [
                'objectives' => 'Membuat relasi dan mengambil data yang dibutuhkan secara efisien.',
                'materials_markdown' => "## Latihan\nBuat relasi belongs-to dan has-many untuk satu alur proyek.",
                'reference_url' => 'https://laravel.com/docs/eloquent-relationships',
                'status' => 'practice_completed',
                'competency_evidence' => 'Sedang menghubungkan tugas belajar dengan tujuan personal.',
                'status_updated_at' => $today->copy()->subDay(),
                'next_review_at' => $today->copy()->addDays(2),
                'knowledge_gap' => false,
            ]);
            $validationLesson = $dataModule->lessons()->firstOrCreate(['title' => 'Validasi dan pengujian'], [
                'objectives' => 'Menangani input tidak valid dan membuktikan perilaku penting.',
                'materials_markdown' => "## Latihan\nTambahkan validasi dan skenario gagal untuk sebuah form.",
                'reference_url' => 'https://laravel.com/docs/validation',
                'status' => 'not_started',
                'knowledge_gap' => true,
            ]);

            LearningSession::query()->firstOrCreate(
                ['owner_id' => $user->id, 'learning_path_id' => $path->id, 'learning_lesson_id' => $routingLesson->id, 'occurred_on' => $today->copy()->subDays(3)->toDateString()],
                ['session_type' => 'study', 'duration_minutes' => 35, 'planned_minutes' => 30, 'evidence' => 'Menyelesaikan latihan route bernama.'],
            );
            LearningSession::query()->firstOrCreate(
                ['owner_id' => $user->id, 'learning_path_id' => $path->id, 'learning_lesson_id' => $relationsLesson->id, 'occurred_on' => $today->toDateString()],
                ['session_type' => 'practice', 'duration_minutes' => 25, 'planned_minutes' => 30, 'evidence' => 'Membuat relasi untuk tugas personal.'],
            );
            LearningAssessment::query()->firstOrCreate(
                ['owner_id' => $user->id, 'learning_lesson_id' => $routingLesson->id, 'title' => 'Latihan routing dasar'],
                ['score' => 8, 'maximum_score' => 10, 'evidence' => 'Route bernama dan validasi berjalan sesuai skenario.', 'assessed_on' => $today->copy()->subDays(2)->toDateString()],
            );

            $personalTask = $user->personalTasks()->firstOrCreate(['title' => 'Praktikkan relasi Eloquent'], [
                'description' => 'Hubungkan tujuan belajar dengan lesson yang sedang dikerjakan.',
                'status' => 'in_progress',
                'priority' => 'high',
                'weight' => 2,
                'due_date' => $today->copy()->addDay()->toDateString(),
                'estimated_minutes' => 30,
                'personal_goal_id' => $goal->id,
                'learning_lesson_id' => $relationsLesson->id,
                'scheduled_at' => now($timezone)->addHours(2),
                'category' => 'learning',
                'tags' => ['belajar', 'laravel'],
            ]);
            $user->personalTasks()->firstOrCreate(['title' => 'Ulangi latihan validasi'], [
                'description' => 'Siapkan satu skenario validasi berhasil dan satu skenario gagal.',
                'status' => 'todo',
                'priority' => 'normal',
                'weight' => 1,
                'due_date' => $today->copy()->addDays(3)->toDateString(),
                'estimated_minutes' => 25,
                'personal_goal_id' => $goal->id,
                'learning_lesson_id' => $validationLesson->id,
                'category' => 'learning',
                'tags' => ['latihan'],
            ]);

            $routine = $user->routines()->firstOrCreate(['title' => 'Belajar backend'], [
                'description' => 'Sesi pendek yang menjaga ritme belajar tetap realistis.',
                'frequency' => 'weekly',
                'days_of_week' => [1, 2, 3, 4, 5],
                'target_per_week' => 4,
                'preferred_time' => '19:00',
                'estimated_minutes' => 30,
                'minimum_action' => 'Baca satu halaman dokumentasi.',
                'normal_action' => 'Belajar dan praktik selama 30 menit.',
                'extended_action' => 'Selesaikan latihan dan catat hasilnya.',
                'status' => 'active',
                'personal_goal_id' => $goal->id,
                'learning_path_id' => $path->id,
            ]);
            foreach ([
                [$today->copy()->subDays(4), 'completed', 'Latihan route dan controller.', 30],
                [$today->copy()->subDays(2), 'completed', 'Menerapkan validasi server-side.', 25],
                [$today, 'completed', 'Mempelajari relasi untuk tugas personal.', 25],
            ] as [$occurredOn, $status, $note, $minutes]) {
                RoutineCheckIn::query()->firstOrCreate(
                    ['routine_id' => $routine->id, 'occurred_on' => $occurredOn->toDateString()],
                    ['status' => $status, 'activity_level' => $minutes < 30 ? 'minimum' : 'normal', 'duration_minutes' => $minutes, 'note' => $note],
                );
            }

            PersonalNote::query()->firstOrCreate(['owner_id' => $user->id, 'title' => 'Catatan belajar Laravel'], [
                'personal_goal_id' => $goal->id,
                'task_id' => $personalTask->id,
                'learning_lesson_id' => $relationsLesson->id,
                'collection' => 'Belajar',
                'content' => 'Mulai dari relasi yang dibutuhkan oleh alur pengguna. Catat bukti kompetensi setelah mencoba sendiri.',
                'tags' => ['laravel', 'belajar'],
                'is_pinned' => true,
            ]);

            $calendarStart = now($timezone)->addHours(2)->startOfHour();
            $user->calendarEvents()->updateOrCreate(
                ['title' => 'Sesi fokus Laravel'],
                [
                    'event_date' => $calendarStart->toDateString(),
                    'description' => 'Sesi terjadwal untuk latihan relasi Eloquent.',
                    'starts_at' => $calendarStart,
                    'ends_at' => $calendarStart->copy()->addMinutes(45),
                    'timezone' => $timezone,
                    'all_day' => false,
                    'is_flexible' => true,
                    'status' => 'planned',
                    'task_id' => $personalTask->id,
                    'routine_id' => $routine->id,
                    'learning_lesson_id' => $relationsLesson->id,
                    'personal_goal_id' => $goal->id,
                ],
            );

            DailyReflection::query()->firstOrCreate(
                ['owner_id' => $user->id, 'reflection_date' => $today->copy()->subDay()->toDateString()],
                ['reflection' => 'Sesi singkat terasa lebih mudah dimulai. Besok lanjutkan praktik relasi.', 'incomplete_task_ids' => [$personalTask->id]],
            );
            foreach ([
                [$today->copy()->subWeek()->startOfWeek(), 'Menyelesaikan modul dasar dan menyiapkan latihan relasi.', true],
                [$today->copy()->startOfWeek(), null, false],
            ] as [$weekStartsOn, $reflection, $isReviewed]) {
                WeeklyReview::query()->firstOrCreate(
                    ['owner_id' => $user->id, 'week_starts_on' => $weekStartsOn->toDateString()],
                    [
                        'reflection' => $reflection,
                        'carry_forward_task_ids' => $isReviewed ? [] : [$personalTask->id],
                        'planned_commitments' => ['Belajar backend 4 kali', 'Menyelesaikan latihan relasi'],
                        'reviewed_at' => $isReviewed ? $weekStartsOn->copy()->addDays(6)->setTime(18, 0) : null,
                    ],
                );
            }

            foreach ([
                ['kind' => 'workload', 'title' => 'Sisakan ruang untuk fokus', 'explanation' => 'Jadwal belajar dan tugas berdekatan. Sisakan satu blok tanpa rapat untuk menyelesaikan latihan.', 'pending_key' => 'demo_workload', 'proposed_action' => ['type' => 'open_calendar']],
                ['kind' => 'routine', 'title' => 'Pertahankan sesi pendek', 'explanation' => 'Beberapa sesi belajar singkat sudah tercatat minggu ini.', 'pending_key' => 'demo_routine', 'proposed_action' => ['type' => 'open_routines']],
            ] as $recommendation) {
                CoachRecommendation::query()->firstOrCreate(
                    ['owner_id' => $user->id, 'kind' => $recommendation['kind'], 'status' => 'pending'],
                    $recommendation + ['status' => 'pending'],
                );
            }
        });
    }
}
