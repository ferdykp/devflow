<?php

namespace App\Services;

use App\Models\CoachRecommendation;
use App\Models\FocusItem;
use App\Models\LearningLesson;
use App\Models\Task;
use App\Models\User;
use App\Services\Calendar\CalendarPlanner;
use Carbon\CarbonImmutable;

class SmartCoachService
{
    /**
     * Refresh deterministic recommendations from the user's own records.
     */
    public function refresh(User $user): void
    {
        $timezone = $user->timezone ?: 'Asia/Jakarta';
        $today = today($timezone);
        $userId = $user->id;
        $tasks = Task::query()->whereNot('status', 'done')
            ->where(fn ($query) => $query->where(fn ($personal) => $personal->where('owner_id', $userId)->whereNull('project_id'))
                ->orWhereHas('project.workspace', fn ($workspace) => $workspace->accessibleTo($userId)))
            ->where(fn ($query) => $query->whereNull('project_id')->orWhereHas('project', fn ($project) => $project->where('status', 'active')))
            ->get();
        $focusedCount = FocusItem::query()->where('user_id', $user->id)->whereDate('focus_date', $today->toDateString())->count();
        if ($focusedCount === 0) {
            $priorityIds = $tasks->where('due_date', '<=', $today)->sortBy(fn ($task) => [
                $task->due_date?->timestamp ?? PHP_INT_MAX,
                ['high' => 0, 'normal' => 1, 'low' => 2][$task->priority] ?? 3,
            ])->take(3)->pluck('id')->values()->all();
            if (count($priorityIds) < 3) {
                $priorityIds = array_values(array_unique(array_merge($priorityIds, $tasks->where('priority', 'high')->pluck('id')->take(3)->all())));
                $priorityIds = array_slice($priorityIds, 0, 3);
            }
            if ($priorityIds !== []) {
                $this->pending($user, 'daily_priorities', 'Pilih tiga prioritas untuk hari ini', 'Belum ada tugas Fokus untuk hari ini. Pilih hingga tiga tugas yang sudah ada agar agenda lebih jelas.', ['type' => 'prioritize_tasks', 'task_ids' => $priorityIds]);
            }
        }

        $overdueCount = $tasks->filter(fn ($task) => $task->due_date && $task->due_date->lt($today))->count();
        if ($overdueCount > 0) {
            $this->pending($user, 'overdue_tasks', 'Tinjau tugas yang melewati tenggat', "Ada {$overdueCount} tugas terbuka yang tenggatnya lewat. Kamu bisa menunda, menjadwalkan, atau menutup tugas yang tidak lagi relevan.", ['type' => 'open_inbox']);
        }

        $lesson = LearningLesson::query()->where('next_review_at', '<=', now())
            ->whereHas('module.path', fn ($path) => $path->where('owner_id', $user->id)->where('status', 'active'))
            ->orderBy('next_review_at')->first();
        if ($lesson) {
            $this->pending($user, 'learning_review', 'Ada materi yang siap ditinjau', "Review yang kamu jadwalkan untuk “{$lesson->title}” sudah tiba. Catat sesi review atau tentukan tanggal lain.", ['type' => 'open_learning', 'lesson_id' => $lesson->id]);
        }

        $yesterday = $today->copy()->subDay();
        $missedRoutine = $user->routines()->where('status', 'active')->whereDoesntHave('checkIns', fn ($query) => $query->whereDate('occurred_on', $yesterday->toDateString()))->orderBy('title')->first();
        if ($missedRoutine) {
            $this->pending($user, 'routine_recovery', 'Lanjutkan rutinitas dengan langkah kecil', "Tidak ada catatan untuk “{$missedRoutine->title}” kemarin. Rutinitas tidak perlu dipulihkan dengan mengejar streak; pilih jadwal berikutnya yang terasa realistis.", ['type' => 'open_routines']);
        }

        $previousWeekStart = $today->copy()->startOfWeek()->subWeek()->toDateString();
        if (! $user->weeklyReviews()->whereDate('week_starts_on', $previousWeekStart)->whereNotNull('reviewed_at')->exists()) {
            $this->pending($user, 'weekly_review', 'Tinjau minggu sebelumnya', 'Catat yang selesai, yang tertunda, waktu belajar, dan apa yang ingin dibawa ke minggu ini.', ['type' => 'open_weekly_review', 'week' => $previousWeekStart]);
        }

        $from = CarbonImmutable::parse($today->toDateString(), $timezone)->startOfDay();
        $to = $from->addDays(6)->endOfDay();
        $events = $user->calendarEvents()->where('status', 'planned')->where('starts_at', '<=', $to->utc())->with('exceptions')->get();
        $occurrences = app(CalendarPlanner::class)->occurrences($events, $from, $to, $timezone);
        $hasConflict = false;
        foreach ($occurrences as $index => $occurrence) {
            foreach (array_slice($occurrences, $index + 1) as $next) {
                if ($next['starts_at'] >= $occurrence['ends_at']) {
                    break;
                }
                if ($next['event']->id !== $occurrence['event']->id) {
                    $hasConflict = true;
                    break 2;
                }
            }
        }
        if ($hasConflict) {
            $this->pending($user, 'schedule_conflict', 'Periksa blok waktu yang bertumpuk', 'Ada agenda yang waktunya beririsan minggu ini. DevFlow tidak memindahkan jadwal secara otomatis; tinjau dan pilih perubahan yang kamu inginkan.', ['type' => 'open_calendar', 'date' => $today->toDateString()]);
        }
    }

    /** @param array<string, mixed> $action */
    private function pending(User $user, string $kind, string $title, string $explanation, array $action): void
    {
        CoachRecommendation::query()->updateOrCreate(
            ['owner_id' => $user->id, 'pending_key' => $kind],
            ['kind' => $kind, 'status' => 'pending', 'title' => $title, 'explanation' => $explanation, 'proposed_action' => $action],
        );
    }
}
