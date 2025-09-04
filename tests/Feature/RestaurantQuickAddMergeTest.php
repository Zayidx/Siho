<?php

namespace Tests\Feature;

use App\Livewire\Public\RestaurantMenu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantQuickAddMergeTest extends TestCase
{
    use RefreshDatabase;

    protected function user(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => 'password',
        ]);
    }

    public function test_session_cart_is_merged_into_livewire_cart_on_mount_and_cleared(): void
    {
        $user = $this->user();
        $cat = MenuCategory::create(['name' => 'Makanan']);
        $item = MenuItem::create([
            'menu_category_id' => $cat->id,
            'name' => 'Nasi Goreng',
            'price' => 40000,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->withSession(['fnb_cart' => [$item->id => 2]]);

        Livewire::test(RestaurantMenu::class)
            ->assertSet("cart.{$item->id}.qty", 2);

        $this->assertEmpty(session('fnb_cart', []));
    }
}
