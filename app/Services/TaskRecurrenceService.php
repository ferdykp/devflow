<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskHistoryEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaskRecurrenceService
{
    /**
     * Create the next occurrence after a task is completed.
     */
    public function createNextOccurrence(Task $task, User $actor): ?Task
    {
        return DB::transaction(function () use ($task, $actor): ?Task {
            $lockedTask = Task::query()->lockForUpdate()->findOrFail($task->id);
            $rule = $lockedTask->recurrence_rule;
            if ($lockedTask->status !== 'done' || ! $rule || ! $lockedTask->due_date) {
                return null;
            }

            $nextDate = $this->nextDate($lockedTask->due_date->toImmutable(), $rule);
            if (! $nextDate || (isset($rule['until']) && $nextDate->toDateString() > $rule['until'])) {
                return null;
            }

            $seriesId = $lockedTask->recurrence_series_id ?: (string) Str::uuid();
            $nextIndex = (int) ($lockedTask->recurrence_index ?? 1) + 1;
            $existingOccurrence = Task::query()->where('recurrence_series_id', $seriesId)->where('recurrence_index', $nextIndex)->first();
            if ($existingOccurrence) {
                return $existingOccurrence;
            }

            $nextTask = $lockedTask->replicate([
                'id', 'created_at', 'updated_at', 'completed_at', 'document_task_import_id', 'source_index', 'sort_order', 'checklist',
            ]);
            $nextTask->forceFill([
                'status' => 'todo',
                'completed_at' => null,
                'due_date' => $nextDate->toDateString(),
                'scheduled_at' => null,
                'recurrence_series_id' => $seriesId,
                'recurrence_index' => $nextIndex,
                'checklist' => null,
            ])->save();
            TaskHistoryEntry::query()->create([
                'task_id' => $nextTask->id,
                'user_id' => $actor->id,
                'action' => 'recurrence_created',
                'to_status' => 'todo',
                'metadata' => ['source_task_id' => $lockedTask->id, 'series_id' => $seriesId],
            ]);

            return $nextTask;
        });
    }

    private function nextDate(CarbonImmutable $currentDate, array $rule): ?CarbonImmutable
    {
        $frequency = $rule['frequency'] ?? null;
        $interval = max(1, (int) ($rule['interval'] ?? 1));
        $anchor = CarbonImmutable::parse($rule['start_date'] ?? $currentDate->toDateString());

        if ($frequency === 'daily') {
            return $currentDate->addDays($interval);
        }

        if ($frequency === 'monthly') {
            return $currentDate->addMonthsNoOverflow($interval);
        }

        if ($frequency === 'weekly') {
            $days = array_map('intval', $rule['days'] ?? [$currentDate->dayOfWeekIso]);
            for ($offset = 1; $offset <= ($interval * 7) + 7; $offset++) {
                $candidate = $currentDate->addDays($offset);
                $weekIndex = intdiv(max(0, (int) $anchor->startOfWeek()->diffInDays($candidate->startOfWeek(), false)), 7);
                if ($weekIndex % $interval === 0 && in_array($candidate->dayOfWeekIso, $days, true)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
