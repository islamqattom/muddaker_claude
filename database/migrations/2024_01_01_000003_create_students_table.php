<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('level_id')->constrained()->onDelete('restrict');
            $table->string('student_number')->unique();
            $table->string('national_id')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->integer('memorized_parts')->default(0); // عدد الأجزاء المحفوظة
            $table->integer('current_part')->default(1); // الجزء الحالي
            $table->integer('current_page')->default(1); // الصفحة الحالية
            $table->date('enrollment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
