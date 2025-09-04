<?php

namespace Tests\Unit;

use App\Models\FnbOrder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FnbOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => 'password',
        ]);
    }

    public function test_constants_and_allowed_values(): void
    {
        $this->assertContains(FnbOrder::STATUS_PENDING, FnbOrder::ALLOWED_STATUSES);
        $this->assertContains(FnbOrder::SERVICE_IN_ROOM, FnbOrder::ALLOWED_SERVICE_TYPES);
    }

    public function test_status_helpers_and_payment_mark(): void
    {
        $user = $this->createUser();
        $order = FnbOrder::create([
            'user_id' => $user->id,
            'status' => FnbOrder::STATUS_PENDING,
            'payment_status' => FnbOrder::PAYMENT_UNPAID,
            'service_type' => FnbOrder::SERVICE_IN_ROOM,
            'total_amount' => 0,
        ]);

        $this->assertTrue($order->isCancelable());
        $this->assertTrue($order->setStatusSafe(FnbOrder::STATUS_PREPARING));
        $this->assertFalse($order->isCancelable());

        $this->assertTrue($order->markPaid());
        $this->assertEquals(FnbOrder::PAYMENT_PAID, $order->payment_status);

        $this->assertFalse($order->setStatusSafe('invalid'));
        $this->assertNotEquals('invalid', $order->status);
    }
}

