<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_can_view_the_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('DevFlow');
    }
}
