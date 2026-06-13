<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->foreignId('mafsil_position_id')->nullable()->after('page_id')->constrained('mafsil_positions');
            $table->string('position_range', 100)->nullable()->after('position');
            $table->unsignedSmallInteger('page_number_in_mushaf')->nullable()->after('position_range');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mafsil_position_id');
            $table->dropColumn(['position_range', 'page_number_in_mushaf']);
        });
    }
};
