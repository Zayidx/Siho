<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role; // Pastikan Anda sudah membuat model Role

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        // Buat role superadmin
        Role::create(['name' => 'superadmin']);

        // Buat role users
        Role::create(['name' => 'users']);
        // Opsional: akun resto dan spa/pool/gym
        Role::firstOrCreate(['name' => 'resto']);
        Role::firstOrCreate(['name' => 'spa']);
    }
}
