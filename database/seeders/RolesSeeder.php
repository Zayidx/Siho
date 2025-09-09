<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder; // Pastikan Anda sudah membuat model Role

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Idempotent role seeding
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'users']);
        Role::firstOrCreate(['name' => 'cashier']);
        // Optional business roles
        Role::firstOrCreate(['name' => 'resto']);
        Role::firstOrCreate(['name' => 'spa']);
    }
}
