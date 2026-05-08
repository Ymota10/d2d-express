<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fulfillment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'packed', 'shipped', 'delivered'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillment_orders');
    }
};
