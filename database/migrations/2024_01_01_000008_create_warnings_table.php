<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->string('warning_number')->unique();
            $table->enum('type', ['attendance', 'recitation', 'general'])->default('general');
            $table->string('title');
            $table->text('content');
            $table->date('issue_date');
            $table->date('from_date')->nullable(); // الفترة من
            $table->date('to_date')->nullable();   // الفترة إلى
            // إحصائيات الحضور في الإنذار
            $table->integer('absent_days')->nullable();
            $table->integer('late_days')->nullable();
            $table->integer('total_days')->nullable();
            // إحصائيات التسميع في الإنذار
            $table->integer('failed_sessions')->nullable();
            $table->integer('total_sessions')->nullable();
            $table->text('additional_notes')->nullable();
            $table->boolean('is_read_by_parent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warnings');
    }
};
