<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_home_page_sends_visitors_to_the_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
