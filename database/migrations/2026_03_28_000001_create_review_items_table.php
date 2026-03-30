<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('surah');
            $table->unsignedSmallInteger('verse_start');
            $table->unsignedSmallInteger('verse_end');
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->decimal('ease_factor', 3, 2)->default(2.50);
            $table->unsignedInteger('interval')->default(1);
            $table->unsignedInteger('repetitions')->default(0);
            $table->date('next_review_date')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'next_review_date']);
            $table->index(['student_id', 'surah', 'verse_start', 'verse_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_items');
    }
};
