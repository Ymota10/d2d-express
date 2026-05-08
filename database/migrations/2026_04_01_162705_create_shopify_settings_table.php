<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_settings', function (Blueprint $table) {
            $table->id();

            // Auto sync (true / false)
            $table->boolean('auto_sync')->default(false);

            // Fulfillment option (Manual / Automatic)
            $table->enum('fulfillment_option', ['Manual', 'Automatic'])
                ->default('Manual');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_settings');
    }
};
