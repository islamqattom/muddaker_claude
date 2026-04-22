<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->integer('ayah_number');
            $table->text('text');
            $table->integer('page_number');
            $table->integer('juz_number');
            $table->timestamps();

            $table->index(['juz_number']);
            $table->index(['page_number']);
            $table->unique(['surah_id', 'ayah_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayahs');
    }
};
