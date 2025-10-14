<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flyer_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flyer_id')->constrained()->cascadeOnDelete(); // which flyer
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2); // quantity × flyer.price
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('pending'); // pending / paid / delivered
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flyer_orders');
    }
};
