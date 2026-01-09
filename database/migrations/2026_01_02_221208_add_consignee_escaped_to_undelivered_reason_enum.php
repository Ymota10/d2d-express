<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY undelivered_reason ENUM(
                'refused_payment',
                'no_answer',
                'wrong_location',
                'refused_shipment',
                'consignee_escaped'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY undelivered_reason ENUM(
                'refused_payment',
                'no_answer',
                'wrong_location',
                'refused_shipment'
            ) NULL
        ");
    }
};
