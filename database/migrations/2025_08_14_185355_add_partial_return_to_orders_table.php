<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to include 'partial_return'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pickup_request',
            'warehouse_received',
            'out_for_delivery',
            'success_delivery',
            'partial_return',
            'time_scheduled',
            'undelivered',
            'returned_to_warehouse',
            'returned_to_shipper'
        ) DEFAULT 'pickup_request'");
    }

    public function down(): void
    {
        // Remove 'partial_return' from the enum if rolled back
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pickup_request',
            'warehouse_received',
            'out_for_delivery',
            'success_delivery',
            'time_scheduled',
            'undelivered',
            'returned_to_warehouse',
            'returned_to_shipper'
        ) DEFAULT 'pickup_request'");
    }
};
