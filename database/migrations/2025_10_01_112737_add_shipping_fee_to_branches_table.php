<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('delivery_cost', 8, 2)->default(0)->after('name');
            $table->decimal('return_cost', 8, 2)->default(0)->after('delivery_cost');
            $table->decimal('replacement_partial_delivery_cost', 8, 2)->default(0)->after('return_cost');
            $table->decimal('overweight_cost', 8, 2)->default(0)->after('replacement_partial_delivery_cost');
            $table->decimal('refund_cost', 8, 2)->default(0)->after('overweight_cost');
            $table->decimal('exchange_cost', 8, 2)->default(0)->after('refund_cost');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_cost',
                'return_cost',
                'replacement_partial_delivery_cost',
                'overweight_cost',
                'refund_cost',
                'exchange_cost',
            ]);
        });
    }
};
