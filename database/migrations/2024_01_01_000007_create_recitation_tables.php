<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recitation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', [
                'new_memorization',    // حفظ جديد
                'review',              // مراجعة
                'unmastered',          // حفظ غير متقن
                'recitation',          // سرد
                'mafsil'               // مفصل
            ]);
            $table->foreignId('part_id')->nullable()->constrained('quran_parts');
            $table->integer('start_page')->nullable();
            $table->integer('end_page')->nullable();
            $table->integer('total_pages')->nullable();
            $table->integer('total_errors')->default(0);
            $table->integer('total_hesitations')->default(0);
            $table->enum('status', ['in_progress', 'passed', 'failed'])->default('in_progress');
            // للمفصل
            $table->integer('mafsil_type')->nullable(); // 3,6,10,15,20,25,30
            $table->decimal('max_score', 5, 2)->nullable(); // 90, 80, 75
            $table->decimal('final_score', 5, 2)->nullable();
            // للحفظ غير المتقن
            $table->integer('unmastered_pages_count')->nullable(); // 5,10,15,20
            $table->date('session_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recitation_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('recitation_sessions')->onDelete('cascade');
            $table->foreignId('page_id')->nullable()->constrained('quran_pages');
            $table->integer('page_number');
            $table->integer('errors')->default(0);
            $table->integer('hesitations')->default(0);
            $table->enum('color_status', ['green', 'orange', 'red', 'black', 'pending'])->default('pending');
            // green=أخضر(ممتاز), orange=برتقالي(جيد), red=أحمر(ضعيف), black=أسود(راسب)
            $table->boolean('is_passed')->default(true);
            $table->decimal('page_score', 5, 2)->nullable(); // للمفصل
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recitation_pages');
        Schema::dropIfExists('recitation_sessions');
    }
};
