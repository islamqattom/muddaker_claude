<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iman_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->morphs('owner');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
        });

        Schema::create('iman_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iman_program_id')->constrained('iman_programs')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['check', 'number'])->default('check');
            $table->unsignedInteger('target_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['iman_program_id', 'sort_order']);
        });

        Schema::create('iman_daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iman_program_id')->constrained('iman_programs')->cascadeOnDelete();
            $table->foreignId('iman_criterion_id')->constrained('iman_criteria')->cascadeOnDelete();
            $table->date('entry_date');
            $table->boolean('is_checked')->default(false);
            $table->unsignedInteger('value')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['iman_criterion_id', 'entry_date'], 'iman_daily_unique');
            $table->index(['iman_program_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iman_daily_entries');
        Schema::dropIfExists('iman_criteria');
        Schema::dropIfExists('iman_programs');
    }
};
