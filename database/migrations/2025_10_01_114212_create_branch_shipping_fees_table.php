<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_shipping_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); // link to branches
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade'); // link to cities
            $table->decimal('delivery_cost', 8, 2)->default(0);
            $table->decimal('return_cost', 8, 2)->default(0);
            $table->decimal('replacement_partial_delivery_cost', 8, 2)->default(0);
            $table->decimal('overweight_cost', 8, 2)->default(0);
            $table->decimal('refund_cost', 8, 2)->default(0);
            $table->decimal('exchange_cost', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'city_id']); // prevent duplicate fees for same branch + city
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_shipping_fees');
    }
};
