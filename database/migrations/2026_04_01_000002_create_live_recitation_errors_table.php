<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_recitation_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_recitation_sessions')->onDelete('cascade');
            $table->unsignedSmallInteger('surah');
            $table->unsignedSmallInteger('verse_number');
            $table->unsignedSmallInteger('verse_index');
            $table->string('expected_text', 500);
            $table->string('detected_text', 500);
            $table->decimal('similarity', 5, 2);
            $table->enum('error_type', ['missing', 'mismatch', 'extra', 'unclear'])->default('mismatch');
            $table->json('word_level_errors')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['live_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_recitation_errors');
    }
};
