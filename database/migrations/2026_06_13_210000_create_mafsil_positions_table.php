<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafsil_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('juz_number');
            $table->unsignedSmallInteger('position_number');
            $table->unsignedSmallInteger('list_order')->default(0);
            $table->foreignId('start_ayah_id')->nullable()->constrained('ayahs');
            $table->foreignId('end_ayah_id')->nullable()->constrained('ayahs');
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['juz_number', 'list_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafsil_positions');
    }
};
