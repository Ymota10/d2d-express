<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_reports', function (Blueprint $table) {

            $table->decimal('total_open_package_fees', 10, 2)->default(0)->after('total_delivery_cost');

            $table->decimal('total_insurance_fees', 10, 2)->default(0)->after('total_open_package_fees');

        });
    }

    public function down(): void
    {
        Schema::table('payment_reports', function (Blueprint $table) {

            $table->dropColumn([
                'total_open_package_fees',
                'total_insurance_fees',
            ]);

        });
    }
};
