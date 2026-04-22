<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopify_settings', function (Blueprint $table) {
            $table->string('shop_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('shopify_settings', function (Blueprint $table) {
            $table->dropColumn('shop_id');
        });
    }
};
