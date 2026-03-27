<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('page_number'); // 1-604
            $table->integer('verse_number')->nullable(); // verse within page
            $table->string('surah_name')->nullable();
            $table->text('note')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'page_number']);
            $table->index(['student_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_bookmarks');
    }
};
