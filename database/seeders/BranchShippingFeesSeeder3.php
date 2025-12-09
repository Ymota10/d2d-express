<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchShippingFeesSeeder3 extends Seeder
{
    public function run(): void
    {
        DB::table('branch_shipping_fees_track_2')->insert([

            // 1 - Alexandria  zone 2
            [
                'city_id' => 1,
                'branch_id' => 4,
                'delivery_cost' => 70,
                'return_cost' => 55.86,
                'replacement_partial_delivery_cost' => 61.56,
                // 'overweight_cost' => 5,
                'refund_cost' => 61.56,
                'exchange_cost' => 78.66,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 2 - Aswan  zone 5
            [
                'city_id' => 2,
                'branch_id' => 4,
                'delivery_cost' => 88.92,
                'return_cost' => 83.22,
                'replacement_partial_delivery_cost' => 88.92,
                // 'overweight_cost' => 5,
                'refund_cost' => 88.92,
                'exchange_cost' => 106.02,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 3 - Asyut zone 4
            [
                'city_id' => 3,
                'branch_id' => 4,
                'delivery_cost' => 78.66,
                'return_cost' => 72.96,
                'replacement_partial_delivery_cost' => 78.66,
                // 'overweight_cost' => 7,
                'refund_cost' => 78.66,
                'exchange_cost' => 95.76,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 4 - Beheira zone 2
            [
                'city_id' => 4,
                'branch_id' => 4,
                'delivery_cost' => 61.56,
                'return_cost' => 55.86,
                'replacement_partial_delivery_cost' => 61.56,
                // 'overweight_cost' => 5,
                'refund_cost' => 61.56,
                'exchange_cost' => 78.66,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 5 - Beni Suef zone 4
            [
                'city_id' => 5,
                'branch_id' => 4,
                'delivery_cost' => 78.66,
                'return_cost' => 72.96,
                'replacement_partial_delivery_cost' => 78.66,
                // 'overweight_cost' => 7,
                'refund_cost' => 78.66,
                'exchange_cost' => 95.76,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 6 - Cairo zone 1
            [
                'city_id' => 6,
                'branch_id' => 4,
                'delivery_cost' => 50,
                'return_cost' => 30,
                'replacement_partial_delivery_cost' => 50,
                // 'overweight_cost' => 5,
                'refund_cost' => 50,
                'exchange_cost' => 50,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 7 - Dakahlia zone 3
            [
                'city_id' => 7,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 8 - Damietta zone 3
            [
                'city_id' => 8,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 9 - Faiyum zone 4
            [
                'city_id' => 9,
                'branch_id' => 4,
                'delivery_cost' => 78.66,
                'return_cost' => 72.96,
                'replacement_partial_delivery_cost' => 78.66,
                // 'overweight_cost' => 7,
                'refund_cost' => 78.66,
                'exchange_cost' => 95.76,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 10 - Gharbia zone 3
            [
                'city_id' => 10,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 11 - Giza zone 1
            [
                'city_id' => 11,
                'branch_id' => 4,
                'delivery_cost' => 50,
                'return_cost' => 30,
                'replacement_partial_delivery_cost' => 50,
                // 'overweight_cost' => 5,
                'refund_cost' => 50,
                'exchange_cost' => 50,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 12 - Ismailia zone 3
            [
                'city_id' => 12,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 13 - Kafr El Sheikh zone 3
            [
                'city_id' => 13,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 14 - Luxor zone 5
            [
                'city_id' => 14,
                'branch_id' => 4,
                'delivery_cost' => 88.92,
                'return_cost' => 83.22,
                'replacement_partial_delivery_cost' => 88.92,
                // 'overweight_cost' => 5,
                'refund_cost' => 88.92,
                'exchange_cost' => 106.02,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 15 - Matrouh zone 5
            [
                'city_id' => 15,
                'branch_id' => 4,
                'delivery_cost' => 88.92,
                'return_cost' => 83.22,
                'replacement_partial_delivery_cost' => 88.92,
                // 'overweight_cost' => 5,
                'refund_cost' => 88.92,
                'exchange_cost' => 106.02,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 16 - Minya zone 4
            [
                'city_id' => 16,
                'branch_id' => 4,
                'delivery_cost' => 78.66,
                'return_cost' => 72.96,
                'replacement_partial_delivery_cost' => 78.66,
                // 'overweight_cost' => 7,
                'refund_cost' => 78.66,
                'exchange_cost' => 95.76,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 17 - Monufia zone 3
            [
                'city_id' => 17,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 18 - New Valley zone 7
            [
                'city_id' => 18,
                'branch_id' => 4,
                'delivery_cost' => 108.3,
                'return_cost' => 102.6,
                'replacement_partial_delivery_cost' => 108.3,
                // 'overweight_cost' => 9,
                'refund_cost' => 108.3,
                'exchange_cost' => 125.4,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 19 - North Sinai zone 7
            [
                'city_id' => 19,
                'branch_id' => 4,
                'delivery_cost' => 108.3,
                'return_cost' => 102.6,
                'replacement_partial_delivery_cost' => 108.3,
                // 'overweight_cost' => 9,
                'refund_cost' => 108.3,
                'exchange_cost' => 125.4,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 20 - Port Said zone 3
            [
                'city_id' => 20,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 21 - Qalyubia zone 3
            [
                'city_id' => 21,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 22 - Qena zone 5
            [
                'city_id' => 22,
                'branch_id' => 4,
                'delivery_cost' => 88.92,
                'return_cost' => 83.22,
                'replacement_partial_delivery_cost' => 88.92,
                // 'overweight_cost' => 5,
                'refund_cost' => 88.92,
                'exchange_cost' => 106.02,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 23 - Red Sea zone 5
            [
                'city_id' => 23,
                'branch_id' => 4,
                'delivery_cost' => 88.92,
                'return_cost' => 83.22,
                'replacement_partial_delivery_cost' => 88.92,
                // 'overweight_cost' => 5,
                'refund_cost' => 88.92,
                'exchange_cost' => 106.02,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 24 - Sharqia zone 3
            [
                'city_id' => 24,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 25 - Sohag zone 4
            [
                'city_id' => 25,
                'branch_id' => 4,
                'delivery_cost' => 78.66,
                'return_cost' => 72.96,
                'replacement_partial_delivery_cost' => 78.66,
                // 'overweight_cost' => 7,
                'refund_cost' => 78.66,
                'exchange_cost' => 95.76,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 26 - South Sinai zone 7
            [
                'city_id' => 26,
                'branch_id' => 4,
                'delivery_cost' => 108.3,
                'return_cost' => 102.6,
                'replacement_partial_delivery_cost' => 108.3,
                // 'overweight_cost' => 9,
                'refund_cost' => 108.3,
                'exchange_cost' => 125.4,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],

            // 27 - Suez zone 3
            [
                'city_id' => 27,
                'branch_id' => 4,
                'delivery_cost' => 67.26,
                'return_cost' => 61.56,
                'replacement_partial_delivery_cost' => 67.26,
                // 'overweight_cost' => 6,
                'refund_cost' => 67.26,
                'exchange_cost' => 84.36,
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],
        ]);
    }
}
