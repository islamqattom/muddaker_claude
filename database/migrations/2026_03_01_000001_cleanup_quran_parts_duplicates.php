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
        // Clean up duplicate entries using a temp table approach for MySQL compatibility
        DB::statement('CREATE TEMPORARY TABLE quran_parts_min_ids AS
            SELECT MIN(id) as min_id FROM quran_parts GROUP BY part_number');

        DB::statement('DELETE FROM quran_parts WHERE id NOT IN (
            SELECT min_id FROM quran_parts_min_ids
        )');

        DB::statement('DROP TEMPORARY TABLE IF EXISTS quran_parts_min_ids');

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
