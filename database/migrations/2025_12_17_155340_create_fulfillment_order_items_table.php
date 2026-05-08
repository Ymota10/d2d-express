<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fulfillment_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fulfillment_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillment_order_items');
    }
};
