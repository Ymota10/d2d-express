<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['shipper_id']);

            // Add new foreign key pointing to users table
            $table->foreign('shipper_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key to users
            $table->dropForeign(['shipper_id']);

            // Restore the original foreign key to shippers
            $table->foreign('shipper_id')->references('id')->on('shippers')->onDelete('set null');
        });
    }
};
