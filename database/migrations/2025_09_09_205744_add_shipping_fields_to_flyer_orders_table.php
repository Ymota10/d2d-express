<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flyer_orders', function (Blueprint $table) {
            $table->text('receiver_address')->nullable()->after('customer_phone');
            $table->unsignedBigInteger('area_id')->nullable()->after('receiver_address');
            $table->unsignedBigInteger('city_id')->nullable()->after('area_id');
            $table->decimal('delivery_cost', 10, 2)->default(0)->after('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('flyer_orders', function (Blueprint $table) {
            $table->dropColumn(['receiver_address', 'area_id', 'city_id', 'delivery_cost']);
        });
    }
};
