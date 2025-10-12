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
        Schema::create('shippers', function (Blueprint $table) {
            $table->id();
            $table->enum('management', ['Normal Shipper', 'Integrated Shipper']);
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('national_id')->nullable();
            $table->foreignId('city_id');
            $table->text('address')->nullable();
            $table->string('password');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); // City reference
            $table->string('profile_photo')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippers');
    }
};