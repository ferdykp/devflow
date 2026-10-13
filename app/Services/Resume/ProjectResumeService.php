<?php

namespace App\Services\Resume;

use App\Models\Project;

class ProjectResumeService
{
    /** Generate a factual, database-backed resume without requiring PRD, GitHub or AI. */
    public function build(Project $project): array
    {
        $open = $project->tasks()->whereNotIn('status', ['done'])->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 WHEN 'low' THEN 2 ELSE 3 END")
            ->orderBy('due_date')->orderBy('id')->limit(6)->get();
        $recent = $project->activities()->orderByDesc('occurred_at')->limit(8)->get();
        $notes = $project->notes()->latest()->limit(3)->get();
        $milestones = $project->milestones()->withCount([
            'tasks', 'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
        ])->orderBy('id')->get();
        $totalTasks = $project->tasks()->count();
        $doneTasks = $project->tasks()->where('status', 'done')->count();
        $totalWeight = $project->tasks()->sum('weight');
        $doneWeight = $project->tasks()->where('status', 'done')->sum('weight');
        $lastActivity = $recent->first();
        $latestNote = $notes->first();
        $nextTask = $open->first(fn ($task) => $task->status !== 'blocked');

        return [
            'open_tasks' => $open,
            'activities' => $recent,
            'notes' => $notes,
            'milestones' => $milestones,
            'task_count' => $totalTasks,
            'done_count' => $doneTasks,
            'progress' => $totalWeight > 0 ? round($doneWeight * 100 / $totalWeight) : null,
            'last_activity' => $lastActivity,
            'latest_note' => $latestNote,
            'next_task' => $nextTask,
            'github_connected' => $project->repositories()->exists(),
            'last_sync' => $project->repositories()->max('last_synced_at'),
            'has_prd' => $project->documents()->exists(),
        ];
    }
}
