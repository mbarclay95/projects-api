<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_health_check_returns_a_successful_response(): void
    {
        $response = $this->get('/api/health-check');

        $response->assertStatus(200);
    }
}
