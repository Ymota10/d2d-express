<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('flextock_courier_name')->nullable();
            $table->string('courier_tracking_number')->nullable();
            $table->text('courier_tracking_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'flextock_courier_name',
                'courier_tracking_number',
                'courier_tracking_url',
            ]);
        });
    }
};
