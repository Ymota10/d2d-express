<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('id');
            $table->string('external_order_id')->nullable()->unique()->after('source');
            $table->json('external_payload')->nullable()->after('external_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['external_order_id']);
            $table->dropColumn([
                'source',
                'external_order_id',
                'external_payload',
            ]);
        });
    }
};
