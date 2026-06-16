<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tajweed_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedTinyInteger('score_percentage')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('category_filter')->nullable();
            $table->json('questions_payload')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });

        Schema::create('tajweed_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('tajweed_quiz_attempts')->cascadeOnDelete();
            $table->foreignId('tajweed_rule_id')->nullable()->constrained('tajweed_rule_definitions')->nullOnDelete();
            $table->string('question_text');
            $table->text('correct_answer');
            $table->text('student_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('time_taken_seconds')->nullable();
            $table->timestamps();

            $table->index('attempt_id');
        });

        Schema::create('recitation_tajweed_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recitation_page_id')->constrained('recitation_pages')->cascadeOnDelete();
            $table->foreignId('tajweed_rule_id')->nullable()->constrained('tajweed_rule_definitions')->nullOnDelete();
            $table->string('category')->nullable();
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('severity')->default(1);
            $table->unsignedInteger('occurrences')->default(1);
            $table->string('source')->default('supervisor');
            $table->timestamps();

            $table->index('recitation_page_id');
            $table->index('tajweed_rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recitation_tajweed_errors');
        Schema::dropIfExists('tajweed_quiz_answers');
        Schema::dropIfExists('tajweed_quiz_attempts');
    }
};
