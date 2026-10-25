<?php

namespace Tests\Feature;

use App\Models\DocumentTaskImport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DocumentTaskImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_preview_keeps_source_and_creates_only_reviewed_tasks_once(): void
    {
        $project = Project::factory()->create();
        $owner = $project->workspace->user;
        $member = User::factory()->create();
        $project->workspace->members()->attach($member);
        $content = "# Produk\n## Akun\n- Halaman masuk\n  - Validasi email\n- Halaman profil\n- Halaman masuk";
        $response = $this->actingAs($owner)->post(route('task-imports.store', $project), ['title' => 'PRD akun', 'content_markdown' => $content]);
        $import = $project->taskImports()->sole();
        $response->assertRedirect(route('task-imports.show', [$project, $import]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('tasks', 0);
        $this->assertCount(2, $import->draft_tasks);
        $this->assertSame('Halaman masuk', $import->draft_tasks[0]['title']);
        $this->assertSame("Bagian: Akun\n- Validasi email", $import->draft_tasks[0]['description']);
        $this->assertSame($content, $import->version->content_markdown);
        $this->assertSame('draft', $import->version->status);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Belum ada tugas yang dibuat.');
        $payload = ['selected' => [0], 'tasks' => [0 => ['title' => 'Bangun halaman masuk', 'description' => 'Email wajib valid.', 'priority' => 'high', 'due_date' => '2027-02-10', 'assignee_id' => $member->id]]];
        $this->actingAs($member)->patch(route('task-imports.update', [$project, $import]), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('tasks', ['project_id' => $project->id, 'title' => 'Bangun halaman masuk', 'status' => 'todo', 'priority' => 'high', 'assignee_id' => $member->id, 'document_task_import_id' => $import->id, 'source_index' => 0]);
        $this->assertSame(1, $import->fresh()->created_count);
        $this->assertNotNull($import->fresh()->committed_at);
        $this->patch(route('task-imports.update', [$project, $import]), $payload)->assertRedirect();
        $this->assertDatabaseCount('tasks', 1);
        $this->post(route('task-imports.store', $project), ['content_markdown' => $content])->assertRedirect(route('task-imports.show', [$project, $import]));
        $this->assertDatabaseCount('document_task_imports', 1);
        $this->assertDatabaseCount('project_documents', 1);
        $this->get(route('task-imports.show', [$project, $import]))->assertOk()->assertSee('Tugas berhasil dibuat');
        $this->get(route('tasks.show', [$project, $project->tasks()->sole()]))->assertOk()->assertSee('PRD akun');
    }

    public function test_existing_prd_version_can_be_converted_without_mutating_approval_or_content(): void
    {
        $project = Project::factory()->create();
        $version = $project->documents()->create(['title' => 'PRD tersimpan'])->versions()->create(['version_number' => 1, 'content_markdown' => "## Masuk\nValidasi akun.\n## Keluar\nHapus sesi.", 'status' => 'approved']);
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['version_id' => $version->id])->assertRedirect()->assertSessionHasNoErrors();
        $import = $project->taskImports()->sole();
        $this->assertSame($version->id, $import->prd_version_id);
        $this->assertSame(['Masuk', 'Keluar'], array_column($import->draft_tasks, 'title'));
        $this->assertSame('approved', $version->fresh()->status);
        $this->assertDatabaseCount('prd_versions', 1);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_plain_text_upload_works_without_publishing_or_storing_the_file(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['document_file' => UploadedFile::fake()->createWithContent('requirements.md', "- Buat pendaftaran\n- Buat keluar")])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(['Buat pendaftaran', 'Buat keluar'], array_column($project->taskImports()->sole()->draft_tasks, 'title'));
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_empty_unsupported_binary_and_oversized_documents_are_rejected_without_writes(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user);
        $this->post(route('task-imports.store', $project), [])->assertSessionHasErrors('content_markdown');
        $this->post(route('task-imports.store', $project), ['document_file' => UploadedFile::fake()->createWithContent('bad.pdf', '%PDF-1.4 fake')])->assertSessionHasErrors('document_file');
        $this->post(route('task-imports.store', $project), ['document_file' => UploadedFile::fake()->createWithContent('bad.txt', "secret\x00binary")])->assertSessionHasErrors('document_file');
        $this->post(route('task-imports.store', $project), ['document_file' => UploadedFile::fake()->create('big.txt', 201, 'text/plain')])->assertSessionHasErrors('document_file');
        $this->post(route('task-imports.store', $project), ['content_markdown' => '- Example', 'document_file' => UploadedFile::fake()->createWithContent('good.txt', '- Other')])->assertSessionHasErrors('content_markdown');
        $this->assertDatabaseCount('document_task_imports', 0);
        $this->assertDatabaseCount('project_documents', 0);
    }

    public function test_large_documents_are_not_silently_truncated_into_partial_tasks(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['content_markdown' => implode("\n", array_map(fn ($i) => '- Tugas '.$i, range(1, 1001)))])->assertSessionHasErrors('content_markdown');
        $this->post(route('task-imports.store', $project), ['content_markdown' => '- '.str_repeat('a', 201)])->assertSessionHasErrors('content_markdown');
        $this->assertDatabaseCount('document_task_imports', 0);
    }

    public function test_invalid_confirmation_leaves_all_tasks_uncreated(): void
    {
        $import = DocumentTaskImport::factory()->create();
        $project = $import->project;
        $other = User::factory()->create();
        $this->actingAs($project->workspace->user);
        $this->patch(route('task-imports.update', [$project, $import]), ['selected' => [], 'tasks' => []])->assertSessionHasErrors('selected');
        $this->patch(route('task-imports.update', [$project, $import]), ['selected' => [9], 'tasks' => [9 => ['title' => 'Injected', 'priority' => 'normal']]])->assertSessionHasErrors('selected.0');
        $this->patch(route('task-imports.update', [$project, $import]), ['selected' => [0], 'tasks' => [0 => ['title' => 'Injected', 'priority' => 'normal', 'assignee_id' => $other->id]]])->assertSessionHasErrors('tasks.0.assignee_id');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertNull($import->fresh()->committed_at);
    }

    public function test_cross_project_sources_imports_and_tasks_remain_private(): void
    {
        $first = Project::factory()->create();
        $second = Project::factory()->create();
        $import = DocumentTaskImport::factory()->for($second)->create();
        $version = $second->documents()->create(['title' => 'Secret'])->versions()->create(['version_number' => 1, 'content_markdown' => '- Secret task']);
        $this->actingAs($first->workspace->user);
        $this->get(route('task-imports.create', $second))->assertNotFound();
        $this->post(route('task-imports.store', $second), ['content_markdown' => '- Injected'])->assertNotFound();
        $this->post(route('task-imports.store', $first), ['version_id' => $version->id])->assertNotFound();
        $this->get(route('task-imports.show', [$first, $import]))->assertNotFound();
        $this->patch(route('task-imports.update', [$first, $import]), [])->assertNotFound();
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_untrusted_document_text_is_escaped_on_review_and_task_pages(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['content_markdown' => '- <script>alert(1)</script>'])->assertRedirect();
        $import = $project->taskImports()->sole();
        $this->get(route('task-imports.show', [$project, $import]))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_invalid_assignee_in_later_selected_task_rolls_back_the_entire_confirmation(): void
    {
        $project = Project::factory()->create();
        $outsider = User::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['content_markdown' => "- Masuk\n- Keluar"])->assertSessionHasNoErrors();
        $import = $project->taskImports()->sole();
        $this->patch(route('task-imports.update', [$project, $import]), ['selected' => [0, 1], 'tasks' => [
            0 => ['title' => 'Masuk', 'priority' => 'normal'],
            1 => ['title' => 'Keluar', 'priority' => 'normal', 'assignee_id' => $outsider->id],
        ]])->assertSessionHasErrors('tasks.1.assignee_id');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertNull($import->fresh()->committed_at);
    }

    public function test_code_examples_do_not_become_task_candidates(): void
    {
        $project = Project::factory()->create();
        $this->actingAs($project->workspace->user)->post(route('task-imports.store', $project), ['content_markdown' => "```md\n- Contoh bukan tugas\n```"])->assertSessionHasErrors('content_markdown');
        $this->assertDatabaseCount('document_task_imports', 0);
        $this->post(route('task-imports.store', $project), ['content_markdown' => "Uji halaman masuk.\n\nUji halaman profil."])->assertSessionHasNoErrors();
        $this->assertSame(['Uji halaman masuk.', 'Uji halaman profil.'], array_column($project->taskImports()->sole()->draft_tasks, 'title'));
    }
}
