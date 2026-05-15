<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adhkar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('azkar_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->integer('count')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adhkar');
    }
};