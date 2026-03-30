<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('period', ['weekly', 'monthly']);
            $table->enum('type', ['pages', 'accuracy', 'streak', 'reviews']);
            $table->unsignedInteger('rank');
            $table->decimal('score', 10, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();

            $table->unique(['student_id', 'period', 'type']);
            $table->index(['period', 'type', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
