<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Default
        User::updateOrCreate(
            ['email' => 'admin@bengkel.com'],
            [
                'name' => 'Administrator Bengkel',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Kasir Default
        User::updateOrCreate(
            ['email' => 'kasir@bengkel.com'],
            [
                'name' => 'Kasir Bengkel',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );

        // Kasir Non-aktif
        User::updateOrCreate(
            ['email' => 'nonaktif@bengkel.com'],
            [
                'name' => 'Kasir Nonaktif',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'is_active' => false,
            ]
        );
    }
}
