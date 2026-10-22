<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_reports', function (Blueprint $table) {
            $table->decimal('transfer_fee', 10, 2)
                ->default(0)
                ->after('total_insurance_fees');
        });
    }

    public function down(): void
    {
        Schema::table('payment_reports', function (Blueprint $table) {
            $table->dropColumn('transfer_fee');
        });
    }
};
