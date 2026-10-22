<?php

namespace App\Http\Controllers;

use App\Models\DocumentTaskImport;
use App\Models\Project;
use App\Services\DocumentTaskParser;
use App\Services\DocumentTaskPlanner;
use App\Support\ProjectAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentTaskImportController extends Controller
{
    public function create(int $project): View
    {
        $project = ProjectAccess::find($project);
        $project->load('workspace');

        return view('projects.task-import-create', compact('project'));
    }

    public function store(Request $request, int $project, DocumentTaskParser $parser, DocumentTaskPlanner $planner): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $data = $request->validate([
            'version_id' => ['nullable', 'integer'],
            'mode' => ['sometimes', 'required', Rule::in(['automatic', 'review'])],
            'title' => ['nullable', 'string', 'max:200'],
            'content_markdown' => ['nullable', 'string', 'max:200000'],
            'document_file' => ['nullable', 'file', 'max:200', 'extensions:txt,md,markdown', 'mimetypes:text/plain,text/markdown,text/x-markdown,application/octet-stream'],
        ]);
        $sourceCount = (int) $request->filled('version_id') + (int) $request->filled('content_markdown') + (int) $request->hasFile('document_file');
        if ($sourceCount !== 1) {
            throw ValidationException::withMessages(['content_markdown' => 'Pilih satu sumber: isi dokumen atau unggah satu berkas teks/Markdown.']);
        }
        $version = null;
        if ($request->filled('version_id')) {
            $document = $project->documents()->whereHas('versions', fn ($query) => $query->whereKey($data['version_id']))->firstOrFail();
            $version = $document->versions()->findOrFail($data['version_id']);
            $content = $version->content_markdown;
        } else {
            $content = $request->hasFile('document_file')
                ? $request->file('document_file')->getContent()
                : $data['content_markdown'];
        }
        $content = trim(str_replace(["\r\n", "\r", "\xEF\xBB\xBF"], ["\n", "\n", ''], $content));
        if (! mb_check_encoding($content, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $content) || mb_strlen($content) > 200000) {
            throw ValidationException::withMessages(['content_markdown' => 'Gunakan dokumen UTF-8 berupa teks biasa atau Markdown, maksimal 200.000 karakter.']);
        }
        $drafts = $parser->parse($content);
        $hash = hash('sha256', $content);
        $import = DB::transaction(function () use ($project, $version, $content, $drafts, $hash, $data, $request): DocumentTaskImport {
            Project::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
            $existing = $project->taskImports()->where('source_hash', $hash)->first();
            if ($existing) {
                return $existing;
            }
            if ($version === null) {
                $document = $project->documents()->create(['title' => $data['title'] ?? 'Dokumen tugas']);
                $version = $document->versions()->create(['version_number' => 1, 'content_markdown' => $content]);
            }

            return $project->taskImports()->create([
                'user_id' => $request->user()->id, 'prd_version_id' => $version->id,
                'source_hash' => $hash, 'draft_tasks' => $drafts,
            ]);
        });

        if (($data['mode'] ?? 'review') === 'automatic') {
            $import = $planner->createTasks($project, $import, $request->user()->id);

            return redirect()->route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'plan' => $import->id])
                ->with('success', $import->created_count.' tugas sudah dibuat sesuai urutan dokumen.');
        }

        return redirect()->route('task-imports.show', [$project, $import]);
    }

    public function show(int $project, int $import): View
    {
        $project = ProjectAccess::find($project);
        $project->load('workspace');
        $import = $project->taskImports()->with('version.document')->findOrFail($import);
        $participants = $project->workspace->participants();
        $tasks = $import->committed_at ? $import->tasks()->orderBy('source_index')->get() : collect();

        return view('projects.task-import-review', compact('project', 'import', 'participants', 'tasks'));
    }

    public function update(Request $request, int $project, int $import, DocumentTaskPlanner $planner): RedirectResponse
    {
        $project = ProjectAccess::find($project);
        $import = $project->taskImports()->findOrFail($import);
        if ($import->committed_at !== null) {
            return redirect()->route('task-imports.show', [$project, $import])->with('success', 'Tugas dari draf ini sudah dibuat.');
        }
        $data = $request->validate([
            'selected' => ['required', 'array', 'min:1', 'max:50'],
            'selected.*' => ['required', 'integer', 'distinct', Rule::in(array_keys($import->draft_tasks))],
            'tasks' => ['required', 'array', 'max:50'],
            'tasks.*' => ['required', 'array:title,description,priority,due_date,assignee_id,group,checklist_text'],
            'tasks.*.title' => ['required', 'string', 'max:200'],
            'tasks.*.description' => ['nullable', 'string', 'max:4000'],
            'tasks.*.priority' => ['required', Rule::in(['low', 'normal', 'high'])],
            'tasks.*.due_date' => ['nullable', 'date_format:Y-m-d'],
            'tasks.*.assignee_id' => ['nullable', 'integer'],
            'tasks.*.group' => ['nullable', 'string', 'max:180'],
            'tasks.*.checklist_text' => ['nullable', 'string', 'max:25050'],
        ], ['selected.required' => 'Pilih minimal satu tugas untuk dibuat.']);
        $planner->createTasks($project, $import, $request->user()->id, $data['selected'], $data['tasks']);

        return redirect()->route('task-imports.show', [$project, $import])->with('success', 'Tugas terpilih berhasil dibuat.');
    }
}
