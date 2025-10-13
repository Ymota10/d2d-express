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
            'management' => 'admin',
            'phone' => '01000000000',
            'national_id' => '12345678901234',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(),
        ]);

        // Add this line to run your AreasAndCitiesSeeder
        $this->call([
            AreasAndCitiesSeeder::class,
            BranchShippingFeesSeeder::class,

        ]);
    }
}
