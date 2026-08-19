<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed one admin-capable user for manual auth verification
        User::query()->firstOrCreate(
            ['email' => 'admin@iium.edu.my'],
            [
                'name' => 'IIUM Admin',
                'password' => bcrypt('password'), // Default password for testing
                'role' => UserRole::ADMIN->value,
            ],
        );
    }
}
