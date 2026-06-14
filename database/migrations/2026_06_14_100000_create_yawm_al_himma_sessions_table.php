<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yawm_al_himma_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('supervisor_name');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name');
            $table->json('parts');
            $table->json('pages_data');
            $table->integer('total_errors')->default(0);
            $table->integer('total_hesitations')->default(0);
            $table->date('session_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yawm_al_himma_sessions');
    }
};
