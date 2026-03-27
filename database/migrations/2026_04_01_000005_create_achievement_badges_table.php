<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_badges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar');
            $table->text('description_en');
            $table->string('icon')->default('fa-medal');
            $table->string('color')->default('#B4802C');
            $table->string('type'); // memorization, streak, accuracy, milestone
            $table->json('criteria');
            $table->timestamps();
        });

        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable();

            $table->unique(['student_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievement_badges');
    }
};
