<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ijazahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('quran_part_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->date('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'quran_part_id', 'supervisor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ijazahs');
    }
};