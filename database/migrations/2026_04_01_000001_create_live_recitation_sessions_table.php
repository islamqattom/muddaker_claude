<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_recitation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('recitation_session_id')->nullable()->constrained('recitation_sessions')->onDelete('set null');
            $table->enum('mode', ['page', 'range', 'surah'])->default('page');
            $table->integer('start_page')->nullable();
            $table->integer('end_page')->nullable();
            $table->unsignedSmallInteger('surah')->nullable();
            $table->unsignedSmallInteger('verse_start')->nullable();
            $table->unsignedSmallInteger('verse_end')->nullable();
            $table->integer('total_verses')->default(0);
            $table->integer('verses_completed')->default(0);
            $table->integer('total_errors')->default(0);
            $table->decimal('average_similarity', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_recitation_sessions');
    }
};
