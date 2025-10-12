<?php

namespace Database\Seeders;

use App\Models\Flyer;
use Illuminate\Database\Seeder;

class FlyerSeeder extends Seeder
{
    public function run(): void
    {
        $flyers = [
            ['name' => 'Small flyers',  'size' => '30 x 25', 'price' => 2.5, 'pack_size' => 10],
            ['name' => 'Medium flyers', 'size' => '40 x 35', 'price' => 3.0, 'pack_size' => 10],
            ['name' => 'Large flyers',  'size' => '50 x 45', 'price' => 4.0, 'pack_size' => 10],
            ['name' => 'X-Large flyers', 'size' => '60 x 50', 'price' => 5.0, 'pack_size' => 10],
        ];

        foreach ($flyers as $flyer) {
            Flyer::create($flyer);
        }
    }
}
