<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StructuredDocumentPlanTest extends TestCase
{
    use RefreshDatabase;

    private function markdown(): string
    {
        return <<<'MD'
---
title: Website tim
---
# PRD Website
## Tujuan
- Membantu pelanggan berbelanja
## Tahap 1: Persiapan
1. Siapkan `setup_env`
   - Buat konfigurasi database
   - Jalankan migrasi
2. Siapkan CI
## Tahap 2: Implementasi
### Buat halaman masuk
- Validasi email
- Uji kredensial salah
## Referensi
- https://example.test/panduan
MD;
    }

    public function test_markdown_upload_automatically_creates_a_grouped_ordered_todo_list_with_checklists(): void
    {
        $project = Project::factory()->create();
        $legacy = Task::factory()->for($project)->create(['title' => 'Tugas lama']);
        $response = $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), [
            'title' => 'Rencana website', 'mode' => 'automatic',
            'document_file' => UploadedFile::fake()->createWithContent('project.md', $this->markdown()),
        ]);
        $import = $project->taskImports()->sole();
        $response->assertSessionHasNoErrors()->assertRedirect(route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'plan' => $import->id]));
        $tasks = $import->tasks()->orderBy('sort_order')->get();
        $this->assertSame(['Siapkan setup_env', 'Siapkan CI', 'Buat halaman masuk'], $tasks->pluck('title')->all());
        $this->assertSame([0, 1, 2], $tasks->pluck('source_index')->all());
        $this->assertSame([$legacy->id + 1, $legacy->id + 2, $legacy->id + 3], $tasks->pluck('sort_order')->all());
        $this->assertSame(['Tahap 1: Persiapan', 'Tahap 1: Persiapan', 'Tahap 2: Implementasi'], $tasks->map(fn (Task $task) => $task->milestone->title)->all());
        $this->assertSame(['Buat konfigurasi database', 'Jalankan migrasi'], array_column($tasks[0]->checklist, 'title'));
        $this->assertSame([false, false], array_column($tasks[0]->checklist, 'done'));
        $this->assertSame(['Validasi email', 'Uji kredensial salah'], array_column($tasks[2]->checklist, 'title'));
        $this->assertSame($this->markdown(), $import->version->content_markdown);
        $this->assertSame('draft', $import->version->status);
        $this->assertSame(3, $import->created_count);
        $this->assertNotNull($import->committed_at);
        $this->assertSame('Tugas lama', $legacy->fresh()->title);
        $this->get($response->headers->get('Location'))->assertOk()->assertSeeInOrder(['Siapkan setup_env', 'Siapkan CI', 'Buat halaman masuk'])->assertDontSee('Tugas lama')->assertDontSee('Membantu pelanggan berbelanja');
        $this->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list']))->assertSeeInOrder(['Tugas lama', 'Siapkan setup_env', 'Siapkan CI', 'Buat halaman masuk']);
        $this->assertDatabaseCount('milestones', 2);
    }

    public function test_reupload_and_review_confirmation_cannot_duplicate_an_automatic_plan(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user);
        $data = ['content_markdown' => $this->markdown(), 'mode' => 'automatic'];
        $this->post(route('task-imports.store', $project), $data)->assertSessionHasNoErrors();
        $import = $project->taskImports()->sole();
        $this->post(route('task-imports.store', $project), $data)->assertSessionHasNoErrors();
        $this->patch(route('task-imports.update', [$project, $import]), [])->assertRedirect();
        $this->assertDatabaseCount('tasks', 3);
        $this->assertDatabaseCount('document_task_imports', 1);
        $this->assertDatabaseCount('project_documents', 1);
        $this->assertDatabaseCount('milestones', 2);
    }

    public function test_manual_review_keeps_source_order_and_allows_group_and_checklist_edits(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['content_markdown' => $this->markdown(), 'mode' => 'review'])->assertRedirect()->assertSessionHasNoErrors();
        $import = $project->taskImports()->sole();
        $this->assertDatabaseCount('tasks', 0);
        $this->patch(route('task-imports.update', [$project, $import]), ['selected' => [2, 0], 'tasks' => [
            2 => ['title' => 'Halaman masuk', 'priority' => 'normal', 'group' => 'Fitur akun', 'checklist_text' => "Email wajib valid\nSandi wajib diisi"],
            0 => ['title' => 'Atur lingkungan', 'priority' => 'high', 'group' => 'Persiapan', 'checklist_text' => 'Migrasi berhasil'],
        ]])->assertSessionHasNoErrors();
        $tasks = $import->tasks()->orderBy('sort_order')->get();
        $this->assertSame(['Atur lingkungan', 'Halaman masuk'], $tasks->pluck('title')->all());
        $this->assertSame([0, 2], $tasks->pluck('source_index')->all());
        $this->assertSame(['Migrasi berhasil'], array_column($tasks[0]->checklist, 'title'));
        $this->assertSame('Persiapan', $tasks[0]->milestone->title);
        $this->assertSame($this->markdown(), $import->version->content_markdown);
    }

    public function test_explicit_steps_keep_code_as_description_and_separate_same_named_tasks_in_different_groups(): void
    {
        $project = Project::factory()->create();
        $content = "# Rencana\n## Tahap 1: Backend\n### Langkah 1: Uji API\n- Periksa respons\n```sh\nphp artisan test\n```\n## Tahap 2: Frontend\n### Langkah 1: Uji API\n- Periksa tampilan";
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['mode' => 'automatic', 'content_markdown' => $content])->assertSessionHasNoErrors();
        $tasks = $project->tasks()->orderBy('sort_order')->get();
        $this->assertCount(2, $tasks);
        $this->assertSame(['Langkah 1: Uji API', 'Langkah 1: Uji API'], $tasks->pluck('title')->all());
        $this->assertStringContainsString('php artisan test', $tasks[0]->description);
        $this->assertCount(1, $tasks[0]->checklist);
        $this->assertNotSame($tasks[0]->milestone_id, $tasks[1]->milestone_id);
    }

    public function test_invalid_modes_context_only_documents_and_oversized_checklists_create_nothing(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user);
        $this->post(route('task-imports.store', $project), ['mode' => 'invalid', 'content_markdown' => '- Tugas'])->assertSessionHasErrors('mode');
        $this->post(route('task-imports.store', $project), ['mode' => 'automatic', 'content_markdown' => "# Produk\n## Tujuan\n- Meningkatkan penjualan\n## Referensi\n- Panduan"])->assertSessionHasErrors('content_markdown');
        $content = "## Tahap 1: Setup\n### Konfigurasi\n".implode("\n", array_map(fn ($i) => '- Poin '.$i, range(1, 51)));
        $this->post(route('task-imports.store', $project), ['mode' => 'automatic', 'content_markdown' => $content])->assertSessionHasErrors('content_markdown');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('document_task_imports', 0);
        $this->assertDatabaseCount('milestones', 0);
    }

    public function test_checklist_changes_are_persisted_independently_and_require_project_access(): void
    {
        $task = Task::factory()->create(['checklist' => [['title' => 'Jalankan migrasi', 'done' => false]]]);
        $project = $task->project;
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $this->actingAs($member)->patch(route('task-checklist.update', [$project, $task, 0]), ['done' => true])->assertSessionHasNoErrors();
        $this->assertTrue($task->fresh()->checklist[0]['done']);
        $this->assertSame('todo', $task->fresh()->status);
        $this->patch(route('task-checklist.update', [$project, $task, 0]), ['done' => true])->assertSessionHasNoErrors();
        $this->get(route('tasks.show', [$project, $task]))->assertOk()->assertSee('1/1 selesai');
        $this->patch(route('task-checklist.update', [$project, $task, 0]), ['done' => false])->assertSessionHasNoErrors();
        $this->assertFalse($task->fresh()->checklist[0]['done']);
        $this->patch(route('task-checklist.update', [$project, $task, 9]), ['done' => true])->assertNotFound();
        $this->patch(route('task-checklist.update', [$project, $task, 0]), ['done' => 'malicious'])->assertSessionHasErrors('done');
        $this->actingAs(User::factory()->create())->patch(route('task-checklist.update', [$project, $task, 0]), ['done' => true])->assertNotFound();
        $this->assertFalse($task->fresh()->checklist[0]['done']);
    }

    public function test_plan_filters_cannot_read_other_projects_or_uncommitted_drafts(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $import = $other->taskImports()->create(['user_id' => $other->workspace->user_id, 'source_hash' => str_repeat('a', 64), 'draft_tasks' => [], 'committed_at' => now()]);
        $this->actingAs($project->workspace->user)->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'plan' => $import->id]))->assertNotFound();
        $this->post(route('task-imports.store', $project), ['content_markdown' => '- Siapkan proyek', 'mode' => 'review']);
        $draft = $project->taskImports()->sole();
        $this->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'plan' => $draft->id]))->assertNotFound();
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_automatic_upload_accepts_one_thousand_tasks_without_truncation_and_keeps_page_order(): void
    {
        $project = Project::factory()->create();
        $content = implode("\n", array_map(fn ($number) => '- Tugas '.$number, range(1, 1000)));
        $response = $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), [
            'mode' => 'automatic', 'document_file' => UploadedFile::fake()->createWithContent('large-plan.md', $content),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $import = $project->taskImports()->sole();
        $this->assertSame(1000, $import->created_count);
        $this->assertDatabaseCount('tasks', 1000);
        $this->assertSame('Tugas 1', $import->tasks()->orderBy('sort_order')->first()->title);
        $this->assertSame('Tugas 1000', $import->tasks()->orderByDesc('sort_order')->first()->title);
        $this->get($response->headers->get('Location'))->assertOk()->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 60 && $tasks->first()->title === 'Tugas 1' && $tasks->last()->title === 'Tugas 60');
        $this->get(route('projects.show', ['project' => $project, 'section' => 'tasks', 'view' => 'list', 'plan' => $import->id, 'page' => 17]))->assertOk()->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 40 && $tasks->first()->title === 'Tugas 961' && $tasks->last()->title === 'Tugas 1000');
    }

    public function test_large_review_is_paginated_and_confirming_from_page_two_creates_every_task_once(): void
    {
        $project = Project::factory()->create();
        $content = implode("\n", array_map(fn ($number) => '- Tugas '.$number, range(1, 75)));
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['mode' => 'review', 'content_markdown' => $content])->assertSessionHasNoErrors();
        $import = $project->taskImports()->sole();
        $url = route('task-imports.show', [$project, $import]);
        $this->get($url)->assertOk()->assertSee('Buat semua 75 tugas')->assertViewHas('draftPages', fn ($drafts) => $drafts->count() === 25 && $drafts->first()['title'] === 'Tugas 1' && $drafts->last()['title'] === 'Tugas 25');
        $this->get($url.'?page=2')->assertOk()->assertViewHas('draftPages', fn ($drafts) => $drafts->first()['title'] === 'Tugas 26' && $drafts->last()['title'] === 'Tugas 50');
        $this->assertDatabaseCount('tasks', 0);
        $this->patch(route('task-imports.update', [$project, $import]), ['confirm_all' => '0'])->assertSessionHasErrors('confirm_all');
        $this->assertDatabaseCount('tasks', 0);
        $this->patch(route('task-imports.update', [$project, $import]), ['confirm_all' => '1'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('tasks', 75);
        $this->assertSame(75, $import->fresh()->created_count);
        $this->patch(route('task-imports.update', [$project, $import]), ['confirm_all' => '1'])->assertRedirect();
        $this->assertDatabaseCount('tasks', 75);
        $this->get($url)->assertOk()->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 60);
        $this->get($url.'?page=2')->assertOk()->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 15 && $tasks->last()->title === 'Tugas 75');
    }

    public function test_file_parser_errors_mark_the_upload_instead_of_the_empty_text_field(): void
    {
        $project = Project::factory()->create();
        $content = implode("\n", array_map(fn ($number) => '- Tugas '.$number, range(1, 1001)));
        $this->actingAs($project->workspace->user)->from(route('task-imports.create', $project))->post(route('task-imports.store', $project), [
            'mode' => 'automatic', 'document_file' => UploadedFile::fake()->createWithContent('too-many.md', $content),
        ])->assertSessionHasErrors('document_file')->assertSessionDoesntHaveErrors('content_markdown');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('document_task_imports', 0);
    }
}
