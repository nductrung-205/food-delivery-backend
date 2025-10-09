<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin mặc định
        User::create([
            'fullname' => 'Nguyen Van Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('123456'),
            'role'     => User::ROLE_ADMIN,
        ]);

        // User mặc định
        User::create([
            'fullname' => 'Nguyen Van User',
            'email'    => 'user@example.com',
            'password' => Hash::make('123456'),
            'role'     => User::ROLE_USER,
        ]);

        User::create([
            'fullname' => 'Nguyen Van User1',
            'email'    => 'user1@example.com',
            'password' => Hash::make('123456'),
            'role'     => User::ROLE_USER,
        ]);
    }
}
