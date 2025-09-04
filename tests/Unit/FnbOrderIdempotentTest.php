<?php

namespace Tests\Unit;

use App\Models\FnbOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FnbOrderIdempotentTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'tester2',
            'email' => 'tester2@example.com',
            'password' => 'password',
        ]);
    }

    public function test_mark_paid_is_idempotent(): void
    {
        $user = $this->createUser();
        $order = FnbOrder::create([
            'user_id' => $user->id,
            'status' => FnbOrder::STATUS_PENDING,
            'payment_status' => FnbOrder::PAYMENT_UNPAID,
            'service_type' => FnbOrder::SERVICE_IN_ROOM,
            'total_amount' => 0,
        ]);

        $first = $order->markPaid();
        $this->assertTrue($first);
        $this->assertEquals(FnbOrder::PAYMENT_PAID, $order->payment_status);

        $second = $order->markPaid();
        $this->assertFalse($second);
        $this->assertEquals(FnbOrder::PAYMENT_PAID, $order->fresh()->payment_status);
    }
}
