<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_attempts', function (Blueprint $table) {
            $table->id();

            // Link to the main order
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Attempt number (1st, 2nd, 3rd, etc.)
            $table->unsignedTinyInteger('attempt_number')
                ->comment('1 = first attempt, 2 = second, 3 = third, etc.');

            // Status and notes
            $table->enum('status', [
                'out_for_delivery',
                'undelivered',
                'returned_to_warehouse',
                'success_delivery',
                'cancelled',
                'postponed',
            ])->comment('Status during this attempt');

            $table->text('note')
                ->nullable()
                ->comment('Reason, feedback, or notes about this attempt');

            // When attempt happened
            $table->timestamp('attempted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_attempts');
    }
};
