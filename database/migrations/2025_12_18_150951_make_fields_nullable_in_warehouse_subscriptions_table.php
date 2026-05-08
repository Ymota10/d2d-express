<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_subscriptions', function (Blueprint $table) {
            $table->date('next_billing_date')->nullable()->change();
            $table->enum('status', ['active', 'paused', 'cancelled'])
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_subscriptions', function (Blueprint $table) {
            $table->date('next_billing_date')->nullable(false)->change();
            $table->enum('status', ['active', 'paused', 'cancelled'])
                ->default('active')
                ->nullable(false)
                ->change();
        });
    }
};
