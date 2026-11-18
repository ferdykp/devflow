<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class PrdVersionImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_content_cannot_be_changed_but_status_can_be_superseded(): void
    {
        $project = Project::factory()->create();
        $document = $project->documents()->create(['title' => 'PRD']);
        $version = $document->versions()->create([
            'version_number' => 1,
            'status' => 'approved',
            'content_markdown' => '# Approved',
            'content_hash' => hash('sha256', '# Approved'),
        ]);

        try {
            $version->content_markdown = '# Changed';
            $version->save();
            $this->fail('Approved PRD content should be immutable.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('immutable', $exception->getMessage());
        }

        $version->refresh()->update(['status' => 'superseded']);
        $this->assertSame('superseded', $version->fresh()->status);
        $this->assertSame('# Approved', $version->fresh()->content_markdown);
    }

    public function test_approved_prd_version_cannot_be_deleted_through_eloquent(): void
    {
        $document = Project::factory()->create()->documents()->create(['title' => 'PRD']);
        $version = $document->versions()->create(['version_number' => 1, 'status' => 'approved', 'content_markdown' => '# Approved']);

        $this->expectException(LogicException::class);
        $version->delete();
    }
}
