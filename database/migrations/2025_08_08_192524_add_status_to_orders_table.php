<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pickup_request',
                'warehouse_received',
                'out_for_delivery',
                'success_delivery',
                'time_scheduled',
                'undelivered',
                'returned_to_warehouse',
                'returned_to_shipper',
            ])->default('pickup_request')->after('service_type');

            $table->enum('undelivered_reason', [
                'refused_payment',
                'no_answer',
                'wrong_location',
                'refused_shipment',
            ])->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'undelivered_reason']);
        });
    }
};
