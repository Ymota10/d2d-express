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
        Schema::table('users', function (Blueprint $table) {
            // شيل الـ unique constraint من email
            $table->dropUnique('users_email_unique');

            // شيل الـ unique constraint من phone
            $table->dropUnique('users_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // رجّع الـ unique constraints
            $table->unique('email');
            $table->unique('phone');
        });
    }
};
