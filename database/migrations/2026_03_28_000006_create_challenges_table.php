<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenger_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('challenged_id')->constrained('students')->onDelete('cascade');
            $table->unsignedTinyInteger('surah');
            $table->unsignedSmallInteger('verse_start');
            $table->unsignedSmallInteger('verse_end');
            $table->date('target_date');
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'failed'])->default('pending');
            $table->foreignId('winner_id')->nullable()->constrained('students')->onDelete('set null');
            $table->timestamps();

            $table->index(['challenger_id', 'status']);
            $table->index(['challenged_id', 'status']);
            $table->index(['target_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
