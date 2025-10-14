<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('city_id')
                ->constrained('cities')
                ->onDelete('cascade')
                ->after('id'); // optional: put it after id
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // First drop the foreign key
            $table->dropForeign(['city_id']);
            // Then drop the column
            $table->dropColumn('city_id');
        });
    }
};
