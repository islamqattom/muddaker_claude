<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafsil_position_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('juz_number');
            $table->unsignedTinyInteger('difficulty');
            $table->foreignId('mafsil_position_id')->constrained('mafsil_positions')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafsil_position_usages');
    }
};