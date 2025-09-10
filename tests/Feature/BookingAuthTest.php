<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_redirected_to_login_on_booking(): void
    {
        $resp = $this->get('/booking-hotel');
        $resp->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_booking(): void
    {
        $user = User::factory()->create();
        $resp = $this->actingAs($user)->get('/booking-hotel');
        $resp->assertStatus(200);
    }
}
