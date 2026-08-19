<?php

namespace Database\Seeders;

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
        // Note: role column does not exist yet - will be added in Story 1.3
        User::factory()->create([
            'name' => 'IIUM Admin',
            'email' => 'admin@iium.edu.my',
            'password' => bcrypt('password'), // Default password for testing
        ]);
    }
}
