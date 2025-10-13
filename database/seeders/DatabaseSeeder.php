<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

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

        // Run seeders
        $this->call([
            AreasAndCitiesSeeder::class,
            BranchShippingFeesSeeder::class,
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
