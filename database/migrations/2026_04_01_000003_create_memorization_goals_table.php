<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorization_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('period', ['daily', 'weekly']);
            $table->integer('target_pages');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('target_days_per_week')->nullable();
            $table->time('daily_reminder_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorization_goals');
    }
};
