<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The API health endpoint should respond with 200.
     */
    public function test_health_endpoint_is_up(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}

