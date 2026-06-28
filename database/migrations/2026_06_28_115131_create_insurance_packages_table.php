<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('name_ar')->nullable();

            // Percentage charged on shipment value
            $table->decimal('percentage', 5, 2)->default(0);

            // Minimum insurance fee
            $table->decimal('minimum_fee', 10, 2)->default(0);

            // Maximum compensation amount
            $table->decimal('max_compensation', 12, 2)->default(0);

            // Coverage
            $table->boolean('covers_loss')->default(true);
            $table->boolean('covers_damage')->default(false);

            // Allow enabling/disabling packages
            $table->boolean('is_active')->default(true);

            // Default package (No Insurance)
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_packages');
    }
};
