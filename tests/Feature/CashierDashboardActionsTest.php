<?php

namespace Tests\Feature;

use App\Livewire\Fnb\CashierDashboard;
use App\Models\FnbOrder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashierDashboardActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        return User::create([
            'role_id' => $role->id,
            'username' => $roleName.'_user',
            'email' => $roleName.'@example.com',
            'password' => 'password',
        ]);
    }

    protected function makeOrder(User $owner): FnbOrder
    {
        // Minimal order without items for status/payment tests
        return FnbOrder::create([
            'user_id' => $owner->id,
            'status' => FnbOrder::STATUS_PENDING,
            'payment_status' => FnbOrder::PAYMENT_UNPAID,
            'service_type' => FnbOrder::SERVICE_IN_ROOM,
            'total_amount' => 0,
        ]);
    }

    public function test_cashier_can_update_status_and_mark_paid(): void
    {
        $cashier = $this->makeUser('cashier');
        $owner = $this->makeUser('users');
        $order = $this->makeOrder($owner);

        $this->actingAs($cashier);

        Livewire::test(CashierDashboard::class)
            ->call('setStatus', $order->id, FnbOrder::STATUS_PREPARING)
            ->call('markPaid', $order->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fnb_orders', [
            'id' => $order->id,
            'status' => FnbOrder::STATUS_PREPARING,
            'payment_status' => FnbOrder::PAYMENT_PAID,
        ]);
    }

    public function test_non_cashier_cannot_update_status(): void
    {
        $user = $this->makeUser('users');
        $owner = $this->makeUser('users');
        $order = $this->makeOrder($owner);

        $this->actingAs($user);

        Livewire::test(CashierDashboard::class)
            ->call('setStatus', $order->id, FnbOrder::STATUS_READY)
            ->assertForbidden();
    

    public function test_non_cashier_cannot_mark_paid(): void
    {
        $user = $this->makeUser('users');
        $owner = $this->makeUser('users');
        $order = $this->makeOrder($owner);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Fnb\CashierDashboard::class)
            ->call('markPaid', $order->id)
            ->assertForbidden();
    }

}
