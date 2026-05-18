<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Word concordance - morphology data from QUL
        Schema::create('word_concordance', function (Blueprint $table) {
            $table->id();
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->integer('word_position');
            $table->string('word_text', 100);
            $table->string('root', 50)->nullable();
            $table->string('part_of_speech', 50)->nullable(); // noun, verb, particle, etc.
            $table->string('morphology', 100)->nullable(); // detailed morphology
            $table->string('gender', 20)->nullable(); // masculine, feminine
            $table->string('number', 20)->nullable(); // singular, dual, plural
            $table->string('person', 20)->nullable(); // 1st, 2nd, 3rd
            $table->string('tense', 20)->nullable(); // past, present, imperative
            $table->string('voice', 20)->nullable(); // active, passive
            $table->string('case', 20)->nullable(); // nominative, accusative, genitive
            $table->string('lemma', 100)->nullable(); // dictionary form
            $table->text('translation')->nullable();
            $table->timestamps();

            $table->unique(['surah_number', 'ayah_number', 'word_position']);
            $table->index(['surah_number', 'ayah_number']);
            $table->index('root');
            $table->index('lemma');
            $table->index('part_of_speech');
        });

        // Mutashabihat - similar verses
        Schema::create('mutashabihat', function (Blueprint $table) {
            $table->id();
            $table->integer('source_surah');
            $table->integer('source_ayah');
            $table->integer('target_surah');
            $table->integer('target_ayah');
            $table->integer('similarity_score')->default(0); // 0-100
            $table->text('shared_phrase')->nullable();
            $table->timestamps();

            $table->index(['source_surah', 'source_ayah']);
            $table->index(['target_surah', 'target_ayah']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutashabihat');
        Schema::dropIfExists('word_concordance');
    }
};
