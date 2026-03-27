<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('pages_memorized')->default(0);
            $table->integer('pages_reviewed')->default(0);
            $table->integer('minutes_practiced')->default(0);
            $table->integer('verses_recited')->default(0);
            $table->boolean('goal_completed')->default(false);
            $table->boolean('streak_continued')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
