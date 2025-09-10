<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FnbCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_add_to_cart(): void
    {
        $resp = $this->withSession(['_token' => 't'])
            ->post('/fnb/cart/add', [
                'menu_item_id' => 1,
                'qty' => 1,
                '_token' => 't',
            ]);
        $resp->assertRedirect('/login');
    }

    public function test_authenticated_user_can_add_item_to_cart(): void
    {
        $user = User::factory()->create();

        $cat = MenuCategory::create([
            'name' => 'Drinks',
            'description' => 'Hot and cold beverages',
            'is_active' => true,
        ]);
        $item = MenuItem::create([
            'menu_category_id' => $cat->id,
            'name' => 'Coffee',
            'price' => 15000,
            'is_active' => true,
            'is_popular' => false,
        ]);

        $resp = $this->withSession(['_token' => 't'])
            ->actingAs($user)
            ->post('/fnb/cart/add', [
                'menu_item_id' => $item->id,
                'qty' => 2,
                '_token' => 't',
            ]);

        $resp->assertOk();
        $resp->assertJson(['ok' => true]);

        $cart = session('fnb_cart');
        $this->assertIsArray($cart);
        $this->assertSame(2, $cart[$item->id] ?? 0);
    }
}
