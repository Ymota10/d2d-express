<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->string('sku')->after('warehouse_item_id')->nullable();
            $table->string('product_name')->after('sku')->nullable();
            $table->foreignId('user_id')->after('product_name')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['sku', 'product_name', 'user_id']);
        });
    }
};
