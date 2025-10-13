<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optional: Create default test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'management' => 'admin', // 👈 add this line
        ]);

        // Add this line to run your AreasAndCitiesSeeder
        $this->call([
            AreasAndCitiesSeeder::class,
            BranchShippingFeesSeeder::class,

        ]);
    }
}
