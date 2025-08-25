<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        // Membuat 20 user palsu menggunakan factory
        for ($i = 0; $i < 20; $i++) {
            User::create([
                'username' => $faker->name,
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Kata sandi default untuk semua user adalah 'password'
                'foto' => '/fotos/profile.jpg', // Nilai foto statis sesuai permintaan
                'role_id' => 2, // Asumsi: roles_id 1 untuk admin, 2 untuk user biasa. Silakan sesuaikan.
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
