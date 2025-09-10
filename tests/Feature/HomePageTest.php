<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $resp = $this->get('/');
        $resp->assertStatus(200);
        $resp->assertSee('Laravel', escape: false);
    }
}
