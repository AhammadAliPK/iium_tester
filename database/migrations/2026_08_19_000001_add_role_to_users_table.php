<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add role column with default 'tester' for existing rows
            $table->string('role')->default(UserRole::TESTER->value)->after('email');
        });

        // Backfill the seeded admin user to have admin role
        \DB::table('users')
            ->where('email', 'admin@iium.edu.my')
            ->update(['role' => UserRole::ADMIN->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
