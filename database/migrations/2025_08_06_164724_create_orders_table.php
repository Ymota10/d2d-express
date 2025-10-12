<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Sender Info
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->text('sender_address')->nullable();

            // Receiver Info (Consignee)
            $table->string('receiver_mobile_1')->nullable();
            $table->string('receiver_mobile_2')->nullable();
            $table->string('receiver_name')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->text('receiver_address')->nullable();
            $table->decimal('delivery_cost', 10, 2)->nullable();

            // Shipment Data
            $table->string('item_name')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('order_id')->nullable();
            $table->string('flyer_no')->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->enum('service_type', ['normal_cod', 'replacement', 'refund', 'same_day_delivery'])->default('normal_cod');
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity')->nullable();

            // Foreign Keys
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
