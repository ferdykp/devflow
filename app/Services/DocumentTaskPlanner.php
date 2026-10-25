<?php

namespace App\Services;

use App\Models\DocumentTaskImport;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentTaskPlanner
{
    /**
     * @param  list<int|string>|null  $selected
     * @param  array<int, array{title: string, description?: ?string, priority?: string, due_date?: ?string, assignee_id?: ?int, group?: ?string, checklist_text?: ?string}>  $edits
     */
    public function createTasks(Project $project, DocumentTaskImport $import, int $userId, ?array $selected = null, array $edits = []): DocumentTaskImport
    {
        return DB::transaction(function () use ($project, $import, $userId, $selected, $edits): DocumentTaskImport {
            $workspace = Workspace::query()->accessibleTo($userId)->lockForUpdate()->findOrFail($project->workspace_id);
            $import = $project->taskImports()->lockForUpdate()->findOrFail($import->id);
            if ($import->committed_at !== null) {
                return $import;
            }
            $selected ??= array_keys($import->draft_tasks);
            sort($selected, SORT_NUMERIC);
            $participantIds = $workspace->participants()->modelKeys();
            $position = (int) $project->tasks()->max(DB::raw('COALESCE(sort_order, id)'));
            $milestones = [];
            foreach ($selected as $index) {
                $draft = $import->draft_tasks[$index] ?? null;
                $task = $edits === [] ? Arr::only($draft ?? [], ['title', 'description']) : ($edits[$index] ?? null);
                if ($draft === null || $task === null) {
                    throw ValidationException::withMessages(['selected' => 'Draf tugas tidak lengkap. Muat ulang halaman dan coba lagi.']);
                }
                if (! empty($task['assignee_id']) && ! in_array((int) $task['assignee_id'], $participantIds, true)) {
                    throw ValidationException::withMessages(["tasks.$index.assignee_id" => 'Pilih penanggung jawab dari anggota ruang kerja ini.']);
                }
                $group = array_key_exists('group', $task) ? $task['group'] : ($draft['group'] ?? null);
                $checklist = array_key_exists('checklist_text', $task)
                    ? $this->checklist($task['checklist_text'] ?? '', (int) $index)
                    : ($draft['checklist'] ?? []);
                if (filled($group) && ! array_key_exists($group, $milestones)) {
                    $milestones[$group] = $project->milestones()->firstOrCreate(['title' => $group])->id;
                }
                $project->tasks()->create(Arr::only($task, ['title', 'description', 'priority', 'due_date', 'assignee_id']) + [
                    'document_task_import_id' => $import->id,
                    'source_index' => $index,
                    'sort_order' => ++$position,
                    'milestone_id' => filled($group) ? $milestones[$group] : null,
                    'checklist' => $checklist,
                    'status' => 'todo',
                ]);
            }
            $import->update(['committed_at' => now(), 'created_count' => count($selected)]);

            return $import;
        });
    }

    /** @return list<array{title: string, done: bool}> */
    private function checklist(string $text, int $index): array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), fn (string $line) => $line !== ''));
        if (count($items) > 50 || collect($items)->contains(fn (string $title) => mb_strlen($title) > 500)) {
            throw ValidationException::withMessages(["tasks.$index.checklist_text" => 'Gunakan maksimal 50 poin checklist, masing-masing 500 karakter.']);
        }

        return array_map(fn (string $title) => ['title' => $title, 'done' => false], $items);
    }
}
