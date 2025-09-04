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

class RestaurantRoomNumberValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function user(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'guest1',
            'email' => 'guest1@example.com',
            'password' => 'password',
        ]);
    }

    protected function item(): MenuItem
    {
        $cat = MenuCategory::create(['name' => 'Makanan']);
        return MenuItem::create([
            'menu_category_id' => $cat->id,
            'name' => 'Nasi Goreng',
            'price' => 40000,
            'is_active' => true,
        ]);
    }

    public function test_room_number_required_when_in_room(): void
    {
        $user = $this->user();
        $item = $this->item();
        $this->actingAs($user);

        Livewire::test(RestaurantMenu::class)
            ->call('addToCart', $item->id)
            ->set('serviceType', FnbOrder::SERVICE_IN_ROOM)
            // roomNumber left empty
            ->call('checkout')
            ->assertHasErrors(['roomNumber' => 'required']);
    }

    public function test_room_number_optional_when_dine_in(): void
    {
        $user = $this->user();
        $item = $this->item();
        $this->actingAs($user);

        Livewire::test(RestaurantMenu::class)
            ->call('addToCart', $item->id)
            ->set('serviceType', FnbOrder::SERVICE_DINE_IN)
            ->set('roomNumber', '')
            ->call('checkout')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fnb_orders', [
            'user_id' => $user->id,
            'service_type' => FnbOrder::SERVICE_DINE_IN,
            'room_number' => null,
            'payment_status' => FnbOrder::PAYMENT_UNPAID,
        ]);
    }
}
