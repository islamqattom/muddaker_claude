<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reciters table
        Schema::create('reciters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->string('style', 50)->nullable(); // murattal, mujawwad, muallim, etc.
            $table->boolean('is_segmented')->default(false); // has word-level timestamps
            $table->string('audio_base_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Quran words - word-by-word text from QUL (KFGQPC Hafs)
        Schema::create('quran_words', function (Blueprint $table) {
            $table->id();
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->integer('word_position'); // position within ayah
            $table->text('text_uthmani'); // Uthmani script with tashkeel
            $table->text('text_imlaei')->nullable(); // Imlaei script (simplified)
            $table->text('text_indopak')->nullable();
            $table->string('transliteration', 255)->nullable();
            $table->string('translation_en', 255)->nullable();
            $table->timestamps();

            $table->unique(['surah_number', 'ayah_number', 'word_position']);
            $table->index(['surah_number', 'ayah_number']);
        });

        // Audio segments - word-level or ayah-level timestamps
        Schema::create('audio_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reciter_id')->constrained('reciters')->onDelete('cascade');
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->integer('word_position')->nullable(); // null = full ayah segment
            $table->string('audio_url', 500)->nullable();
            $table->decimal('start_time', 10, 3)->nullable(); // in seconds
            $table->decimal('end_time', 10, 3)->nullable(); // in seconds
            $table->decimal('duration', 10, 3)->nullable();
            $table->timestamps();

            $table->index(['reciter_id', 'surah_number', 'ayah_number'], 'seg_reciter_surah_ayah_idx');
            $table->index(['reciter_id', 'surah_number', 'ayah_number', 'word_position'], 'seg_word_idx');
        });

        // Mushaf page layouts - from QUL
        Schema::create('quran_page_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('layout_name', 50); // e.g. 'KFGQPC_V4', 'Indopak_15'
            $table->integer('page_number'); // 1-604
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->integer('line_number');
            $table->integer('position_in_line')->nullable();
            $table->boolean('is_new_surah')->default(false);
            $table->timestamps();

            $table->index(['layout_name', 'page_number']);
            $table->index(['layout_name', 'surah_number', 'ayah_number'], 'layout_surah_ayah_idx');
        });

        // Tajweed rules data - from QUL
        Schema::create('tajweed_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->integer('word_position');
            $table->string('rule_type', 50); // ghunna, ikhfa, idgham, etc.
            $table->integer('start_char')->nullable();
            $table->integer('end_char')->nullable();
            $table->string('rule_color', 7)->nullable(); // hex color
            $table->timestamps();

            $table->index(['surah_number', 'ayah_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tajweed_rules');
        Schema::dropIfExists('quran_page_layouts');
        Schema::dropIfExists('audio_segments');
        Schema::dropIfExists('quran_words');
        Schema::dropIfExists('reciters');
    }
};
