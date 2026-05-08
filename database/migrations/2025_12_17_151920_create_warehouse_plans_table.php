<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('billing_type', ['rent', 'per_order']);
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('order_fee', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_plans');
    }
};
