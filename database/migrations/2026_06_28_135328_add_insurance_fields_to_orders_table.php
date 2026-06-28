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
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('insurance_package_id')
                ->nullable()
                ->after('users_id')
                ->constrained('insurance_packages')
                ->nullOnDelete();

            $table->decimal('insurance_fee', 10, 2)
                ->default(0)
                ->after('delivery_cost');

            $table->decimal('insured_amount', 10, 2)
                ->default(0)
                ->after('insurance_fee');
        });
    }
};
