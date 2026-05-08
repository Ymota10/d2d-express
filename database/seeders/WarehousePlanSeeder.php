<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WarehousePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // RENT PLANS
            [
                'name' => 'Warehouse Rent Basic',
                'billing_type' => 'rent',
                'monthly_price' => 2500,
                'order_fee' => null,
            ],
            [
                'name' => 'Warehouse Rent Standard',
                'billing_type' => 'rent',
                'monthly_price' => 2000,
                'order_fee' => null,
            ],
            [
                'name' => 'Warehouse Rent Premium',
                'billing_type' => 'rent',
                'monthly_price' => 1500,
                'order_fee' => null,
            ],

            // PER ORDER PLANS
            [
                'name' => 'Per Order Basic',
                'billing_type' => 'per_order',
                'monthly_price' => null,
                'order_fee' => 25,
            ],
            [
                'name' => 'Per Order Standard',
                'billing_type' => 'per_order',
                'monthly_price' => null,
                'order_fee' => 20,
            ],
            [
                'name' => 'Per Order Premium',
                'billing_type' => 'per_order',
                'monthly_price' => null,
                'order_fee' => 15,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\WarehousePlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
