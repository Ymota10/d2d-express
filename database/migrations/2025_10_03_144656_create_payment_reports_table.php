<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // who created the report
            $table->json('order_ids'); // store related orders
            $table->decimal('total_cod', 12, 2)->default(0);
            $table->decimal('total_delivery_cost', 12, 2)->default(0);
            $table->decimal('extra_fees', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reports');
    }
};
