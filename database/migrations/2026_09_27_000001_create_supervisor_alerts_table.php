<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 30);
            $table->string('reference', 40);
            $table->unsignedTinyInteger('part_number')->nullable();
            $table->unsignedTinyInteger('station')->nullable();
            $table->foreignId('recitation_session_id')->nullable()->constrained('recitation_sessions')->nullOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'type', 'reference']);
            $table->index(['supervisor_id', 'type', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_alerts');
    }
};
