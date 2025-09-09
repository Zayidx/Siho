<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class FnbSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuCategory::count() > 0) {
            return;
        }
        $cats = [
            'Makanan', 'Minuman', 'Dessert',
        ];
        $idMap = [];
        foreach ($cats as $c) {
            $idMap[$c] = MenuCategory::create(['name' => $c, 'is_active' => true])->id;
        }
        $items = [
            ['cat' => 'Makanan', 'name' => 'Nasi Goreng Spesial', 'price' => 45000, 'popular' => true],
            ['cat' => 'Makanan', 'name' => 'Mie Goreng Ayam', 'price' => 38000, 'popular' => true],
            ['cat' => 'Minuman', 'name' => 'Es Teh Manis', 'price' => 15000, 'popular' => true],
            ['cat' => 'Minuman', 'name' => 'Kopi Susu', 'price' => 28000, 'popular' => false],
            ['cat' => 'Dessert', 'name' => 'Puding Coklat', 'price' => 25000, 'popular' => false],
            ['cat' => 'Dessert', 'name' => 'Cheesecake', 'price' => 30000, 'popular' => true],
        ];
        foreach ($items as $it) {
            MenuItem::create([
                'menu_category_id' => $idMap[$it['cat']],
                'name' => $it['name'],
                'price' => $it['price'],
                'is_active' => true,
                'is_popular' => $it['popular'],
            ]);
        }
    }
}
