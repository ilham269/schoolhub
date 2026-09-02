<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@schoolhub.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@schoolhub.com');
        $this->command->info('Password: password');
    }
}
