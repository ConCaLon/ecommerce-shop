<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Basic smoke test to ensure the application boots correctly.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The home page should load successfully (even with empty database).
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
