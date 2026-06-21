<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM(
                'pickup_request',
                'warehouse_received',
                'out_for_delivery',
                'failed_attempt',
                'success_delivery',
                'time_scheduled',
                'undelivered',
                'partial_return',
                'returned_to_warehouse',
                'returned_to_shipper'
            ) NOT NULL DEFAULT 'pickup_request'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM(
                'pickup_request',
                'warehouse_received',
                'out_for_delivery',
                'success_delivery',
                'time_scheduled',
                'undelivered',
                'partial_return',
                'returned_to_warehouse',
                'returned_to_shipper'
            ) NOT NULL DEFAULT 'pickup_request'
        ");
    }
};
