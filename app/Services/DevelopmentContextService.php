<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;

class DevelopmentContextService
{
    /**
     * @return list<array{key: string, label: string, source: string, source_url: string, content: string}>
     */
    public function build(Project $project, Task $task): array
    {
        $project->loadMissing(['workspace', 'baseline.prdVersion.document']);
        $task->loadMissing(['milestone', 'taskImport.version.document']);

        $sourceText = null;
        if ($task->taskImport !== null && $task->source_index !== null) {
            $sourceText = data_get($task->taskImport->draft_tasks, $task->source_index.'.source_text');
        }

        $sections = [
            $this->section('project', 'Proyek', 'Proyek', route('projects.show', ['project' => $project, 'section' => 'overview']), [
                'Nama: '.$project->name,
                'Tujuan: '.($project->goal ?: 'Belum ditentukan.'),
                'Deskripsi: '.($project->description ?: 'Belum ditentukan.'),
                'Status: '.$project->status,
                'Ruang kerja: '.$project->workspace->name,
                'Terakhir diperbarui: '.($project->updated_at?->format('Y-m-d H:i') ?? 'Tidak tersedia.'),
            ]),
            $this->section('task', 'Tugas', 'Tugas', route('tasks.show', [$project, $task]), [
                'Judul: '.$task->title,
                'Status: '.$task->status,
                'Prioritas: '.$task->priority,
                'Deskripsi: '.($task->description ?: 'Belum ada deskripsi.'),
                'Tenggat: '.($task->due_date?->toDateString() ?? 'Tidak ditentukan.'),
                'Terakhir diperbarui: '.($task->updated_at?->format('Y-m-d H:i') ?? 'Tidak tersedia.'),
            ]),
        ];

        if ($task->milestone !== null) {
            $sections[] = $this->section('milestone', 'Tahapan', 'Tahapan proyek', route('projects.show', ['project' => $project, 'section' => 'milestones']), [
                'Tahapan: '.$task->milestone->title,
                'Deskripsi: '.($task->milestone->description ?: 'Belum ada deskripsi.'),
                'Target: '.($task->milestone->due_date?->toDateString() ?? 'Tidak ditentukan.'),
            ]);
        }

        $sections[] = $this->section('checklist', 'Checklist & kriteria penerimaan', 'Checklist tugas', route('tasks.show', [$project, $task]), array_merge([
            filled($project->baseline?->definition_of_done)
                ? 'Definition of Done proyek: '.$project->baseline->definition_of_done
                : 'Definition of Done proyek yang eksplisit belum tercatat.',
            'Checklist tugas:',
        ], array_map(
            fn (array $item): string => (($item['done'] ?? false) ? '[x] ' : '[ ] ').($item['title'] ?? ''),
            $task->checklist ?? [],
        )));

        if ($sourceText !== null || $task->taskImport?->version !== null) {
            $version = $task->taskImport?->version;
            $sections[] = $this->section('requirement', 'Sumber kebutuhan', 'Dokumen & PRD', route('projects.show', ['project' => $project, 'section' => 'prd']), [
                'Dokumen: '.($version?->document?->title ?? 'Dokumen sumber'),
                'Versi: '.($version ? 'v'.$version->version_number.' ('.$version->status.')' : 'Tidak tersedia'),
                'Bagian sumber: '.($sourceText ?: 'Posisi sumber task #'.(($task->source_index ?? 0) + 1)),
            ]);
        }

        $history = $task->history()->with('user')->limit(5)->get()->reverse()->map(
            fn ($entry): string => implode(' · ', array_filter([
                $entry->created_at?->format('Y-m-d H:i'),
                $entry->user?->name,
                $entry->action,
                $entry->from_status && $entry->to_status ? $entry->from_status.' → '.$entry->to_status : null,
            ])),
        )->all();
        if ($history !== []) {
            $sections[] = $this->section('task_history', 'Riwayat tugas', 'Detail tugas', route('tasks.show', [$project, $task]), $history);
        }

        $baseline = $project->baseline;
        if ($baseline !== null && (filled($baseline->in_scope) || filled($baseline->out_of_scope)
            || filled($baseline->constraints) || filled($baseline->assumptions) || filled($baseline->success_criteria)
            || filled($baseline->definition_of_done))) {
            $sections[] = $this->section('scope', 'Batas proyek', 'Cakupan', route('projects.show', ['project' => $project, 'section' => 'scope']), array_filter([
                filled($baseline->in_scope) ? 'Termasuk: '.$baseline->in_scope : null,
                filled($baseline->out_of_scope) ? 'Tidak termasuk: '.$baseline->out_of_scope : null,
                filled($baseline->constraints) ? 'Technical constraints: '.$baseline->constraints : null,
                filled($baseline->assumptions) ? 'Assumptions: '.$baseline->assumptions : null,
                filled($baseline->success_criteria) ? 'Kriteria keberhasilan: '.$baseline->success_criteria : null,
                filled($baseline->definition_of_done) ? 'Definition of Done: '.$baseline->definition_of_done : null,
                $baseline->prdVersion ? 'Acuan PRD: '.$baseline->prdVersion->document->title.' v'.$baseline->prdVersion->version_number.' ('.$baseline->prdVersion->status.')' : 'Acuan: baseline manual, tanpa versi PRD tertaut.',
            ]));
        }

        $decisions = $project->reviews()->with(['decisions.user', 'decisions.prdVersion.document'])->where('status', 'resolved')->latest()->limit(3)->get()
            ->flatMap(fn ($review) => $review->decisions->map(fn ($decision): string => implode(' · ', array_filter([
                $review->title,
                $decision->decision,
                $decision->reason,
                $decision->evidence ? 'Bukti: '.$decision->evidence : null,
                $decision->prdVersion ? 'Acuan: '.$decision->prdVersion->document->title.' v'.$decision->prdVersion->version_number : null,
                $decision->user?->name,
                $decision->created_at?->format('Y-m-d'),
            ]))))
            ->all();
        if ($decisions !== []) {
            $sections[] = $this->section('decisions', 'Keputusan terbaru', 'Cakupan', route('projects.show', ['project' => $project, 'section' => 'scope']), $decisions);
        }

        $notes = $project->notes()->latest()->limit(3)->get()->map(fn ($note): string => trim($note->title.' — '.($note->content ?? '')))->all();
        if ($notes !== []) {
            $sections[] = $this->section('notes', 'Catatan proyek', 'Catatan proyek', route('projects.show', ['project' => $project, 'section' => 'notes']), $notes);
        }

        $activities = $project->activities()->latest('occurred_at')->limit(5)->get()->map(fn ($activity): string => implode(' · ', array_filter([
            $activity->occurred_at?->format('Y-m-d H:i'),
            $activity->title,
            $activity->description,
        ])))->all();
        if ($activities !== []) {
            $sections[] = $this->section('activity', 'Aktivitas terbaru', 'Aktivitas', route('projects.show', ['project' => $project, 'section' => 'activity']), $activities);
        }

        return $sections;
    }

    /**
     * @param  array<int, string|null>  $lines
     * @return array{key: string, label: string, source: string, source_url: string, content: string}
     */
    private function section(string $key, string $label, string $source, string $sourceUrl, array $lines): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'source' => $source,
            'source_url' => $sourceUrl,
            'content' => $this->redact(implode("\n", array_filter($lines, 'is_string'))),
        ];
    }

    private function redact(string $content): string
    {
        $patterns = [
            '/-----BEGIN [A-Z ]*PRIVATE KEY-----[\s\S]*?-----END [A-Z ]*PRIVATE KEY-----/u' => '[REDACTED PRIVATE KEY]',
            '/\b(?:gh[pousr]_[A-Za-z0-9]{20,}|sk-[A-Za-z0-9_-]{20,}|AKIA[A-Z0-9]{16})\b/u' => '[REDACTED TOKEN]',
            '/\b(Bearer\s+)[A-Za-z0-9._~+\/-]+=*/iu' => '$1[REDACTED]',
            '/\b(api[_-]?key|client[_-]?secret|secret|token|password|passwd|authorization)\b(\s*[:=]\s*)(["\']?)[^\s,;"\']+/iu' => '$1$2[REDACTED]',
            '/(https?:\/\/)[^\s\/:@]+:[^\s\/@]+@/iu' => '$1[REDACTED]@',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content) ?? $content;
        }

        return mb_substr($content, 0, 12000);
    }
}
