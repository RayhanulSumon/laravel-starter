<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'phone' => '1111111111',
            'role' => UserRole::USER->value,
            'password' => Hash::make('password'),
        ]);
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '2222222222',
            'role' => UserRole::ADMIN->value,
            'password' => Hash::make('password'),
        ]);
        User::query()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '3333333333',
            'role' => UserRole::SUPER_ADMIN->value,
            'password' => Hash::make('password'),
        ]);
    }
}
