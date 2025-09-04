<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FnbCartQuickAddTest extends TestCase
{
    use RefreshDatabase;

    protected function user(): User
    {
        $role = Role::create(['name' => 'users']);
        return User::create([
            'role_id' => $role->id,
            'username' => 'u1',
            'email' => 'u1@example.com',
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

    public function test_quick_add_updates_session_and_returns_totals(): void
    {
        $user = $this->user();
        $item = $this->item();

        $this->actingAs($user);

        $r1 = $this->postJson(route('fnb.cart.add'), ['menu_item_id' => $item->id]);
        $r1->assertOk()->assertJsonStructure(['ok','qty','items']);
        $this->assertEquals(1, session('fnb_cart')[$item->id] ?? 0);

        $r2 = $this->postJson(route('fnb.cart.add'), ['menu_item_id' => $item->id, 'qty' => 2]);
        $r2->assertOk()->assertJson(['ok' => true]);
        $this->assertEquals(3, session('fnb_cart')[$item->id] ?? 0);
        $this->assertEquals(3, $r2->json('qty'));
        $this->assertEquals(1, $r2->json('items'));
    }

    public function test_quick_add_requires_auth(): void
    {
        $item = $this->item();
        // For JSON unauthenticated, Laravel typically returns 401
        $this->postJson(route('fnb.cart.add'), ['menu_item_id' => $item->id])
            ->assertStatus(401);
    }
}
