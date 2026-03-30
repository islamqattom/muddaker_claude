<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recitation_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('quality');
            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['student_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recitation_reviews');
    }
};
