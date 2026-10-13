<?php
namespace Tests\Feature;
use Tests\TestCase;
class DevFlowStructureTest extends TestCase {
    public function test_guest_cannot_access_project_pages(): void {
        $this->get('/projects')->assertRedirect(route('login'));
        $this->get('/focus')->assertRedirect(route('login'));
    }
}
