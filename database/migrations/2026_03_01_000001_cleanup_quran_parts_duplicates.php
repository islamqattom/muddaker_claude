<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cleans up duplicate quran_parts and adds unique constraint on part_number.
     */
    public function up(): void
    {
        // Clean up duplicate entries - keep only the first record per part_number
        DB::statement('DELETE FROM quran_parts WHERE id NOT IN (
            SELECT MIN(id) FROM quran_parts GROUP BY part_number
        )');

        // Add unique constraint on part_number
        Schema::table('quran_parts', function (Blueprint $table) {
            $table->unique('part_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quran_parts', function (Blueprint $table) {
            $table->dropUnique(['part_number']);
        });
    }
};
