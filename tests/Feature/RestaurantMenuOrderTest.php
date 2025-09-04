<?php

namespace Tests\Feature;

use App\Livewire\Public\RestaurantMenu;
use App\Models\FnbOrder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantMenuOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'guest1',
            'email' => 'guest1@example.com',
            'password' => 'password',
        ]);
    }

    public function test_user_can_checkout_simple_order(): void
    {
        $user = $this->createUser();
        $cat = MenuCategory::create(['name' => 'Makanan']);
        $item = MenuItem::create([
            'menu_category_id' => $cat->id,
            'name' => 'Nasi Goreng',
            'price' => 40000,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(RestaurantMenu::class)
            ->call('addToCart', $item->id)
            ->set('serviceType', FnbOrder::SERVICE_IN_ROOM)
            ->set('roomNumber', '101')
            ->call('checkout')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fnb_orders', [
            'user_id' => $user->id,
            'service_type' => FnbOrder::SERVICE_IN_ROOM,
            'room_number' => '101',
            'status' => FnbOrder::STATUS_PENDING,
            'payment_status' => FnbOrder::PAYMENT_UNPAID,
            'total_amount' => 40000,
        ]);

        $order = FnbOrder::first();
        $this->assertDatabaseHas('fnb_order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $item->id,
            'qty' => 1,
            'line_total' => 40000,
        ]);
    }
}

