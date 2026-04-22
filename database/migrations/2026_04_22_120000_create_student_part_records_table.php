<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_part_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('part_number');
            $table->boolean('is_completed')->default(false);
            $table->integer('total_errors')->default(0);
            $table->integer('total_hesitations')->default(0);
            $table->decimal('total_score', 5, 2)->nullable();
            $table->date('last_recitation_date')->nullable();
            $table->boolean('is_manual_override')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'part_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_part_records');
    }
};