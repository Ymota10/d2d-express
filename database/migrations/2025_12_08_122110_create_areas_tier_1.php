<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('areas_tier_1', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade'); // City reference
            $table->string('name'); // Area name
            $table->string('name_ar')->nullable(); // Arabic name
            $table->decimal('delivery_cost', 10, 2)->default(0.00); // Delivery cost
            $table->decimal('return_cost', 10, 2)->default(0.00); // Return cost
            $table->decimal('replacement_partial_delivery_cost', 10, 2)->default(0.00); // Replacement cost
            $table->decimal('overweight_cost', 10, 2)->default(0.00); // Overweight cost
            $table->decimal('refund_cost', 10, 2)->default(0.00); // Refund cost
            $table->decimal('exchange_cost', 10, 2)->default(0.00); // Exchange cost
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('areas_tier_1');
    }
};
