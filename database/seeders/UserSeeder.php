<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
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
        
        // Buat akun superadmin
        $superadminRoleId = Role::where('name', 'superadmin')->value('id') ?? 1;
        User::updateOrCreate(
            ['email' => 'faridx0236@gmail.com'],
            [
                'username' => 'faridx0236',
                'email_verified_at' => now(),
                'password' => Hash::make('indrawan0236'),
                'role_id' => $superadminRoleId,
                'foto' => null,
                'remember_token' => Str::random(10),
            ]
        );
        // Membuat 20 user palsu menggunakan factory
        for ($i = 0; $i < 20; $i++) {
            User::create([
                'username' => $faker->unique()->userName(),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Kata sandi default untuk semua user adalah 'password'
                'foto' => '/fotos/profile.jpg', // Nilai foto statis sesuai permintaan
                'role_id' => (Role::where('name', 'users')->value('id') ?? 2),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
