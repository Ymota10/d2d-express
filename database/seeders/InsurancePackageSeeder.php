<?php

namespace Database\Seeders;

use App\Models\InsurancePackage;
use Illuminate\Database\Seeder;

class InsurancePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'No Insurance',
                'name_ar' => 'بدون تأمين',
                'percentage' => 0,
                'minimum_fee' => 0,
                'max_compensation' => 5000,
                'covers_loss' => true,
                'covers_damage' => false,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Basic Package',
                'name_ar' => 'الباقة الأساسية',
                'percentage' => 1,
                'minimum_fee' => 5,
                'max_compensation' => 10000,
                'covers_loss' => true,
                'covers_damage' => true,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Safety Package',
                'name_ar' => 'باقة الأمان',
                'percentage' => 1.5,
                'minimum_fee' => 10,
                'max_compensation' => 15000,
                'covers_loss' => true,
                'covers_damage' => true,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Secured Package',
                'name_ar' => 'الباقة المؤمنة',
                'percentage' => 2,
                'minimum_fee' => 15,
                'max_compensation' => 20000,
                'covers_loss' => true,
                'covers_damage' => true,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($packages as $package) {
            InsurancePackage::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
