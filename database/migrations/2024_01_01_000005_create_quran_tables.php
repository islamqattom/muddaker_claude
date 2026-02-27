<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_parts', function (Blueprint $table) {
            $table->id();
            $table->integer('part_number'); // 1-30
            $table->string('name'); // اسم الجزء
            $table->integer('pages_count')->default(20);
            $table->integer('start_page');
            $table->integer('end_page');
            $table->timestamps();
        });

        Schema::create('quran_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('quran_parts')->onDelete('cascade');
            $table->integer('page_number'); // 1-604
            $table->integer('page_in_part'); // رقم الصفحة داخل الجزء
            $table->string('surah_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_pages');
        Schema::dropIfExists('quran_parts');
    }
};
