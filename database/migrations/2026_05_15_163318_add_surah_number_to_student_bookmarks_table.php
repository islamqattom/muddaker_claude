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
        Schema::table('student_bookmarks', function (Blueprint $table) {
            $table->unsignedBigInteger('surah_number')->nullable()->after('verse_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_bookmarks', function (Blueprint $table) {
            $table->dropColumn('surah_number');
        });
    }
};
