<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weak_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('surah');
            $table->unsignedSmallInteger('verse_start');
            $table->unsignedSmallInteger('verse_end');
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('hesitation_count')->default(0);
            $table->unsignedInteger('attempt_count')->default(0);
            $table->decimal('error_rate', 5, 3)->default(0);
            $table->decimal('hesitation_rate', 5, 3)->default(0);
            $table->decimal('priority_score', 5, 3)->default(0);
            $table->boolean('is_remediated')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'is_remediated', 'priority_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weak_areas');
    }
};
