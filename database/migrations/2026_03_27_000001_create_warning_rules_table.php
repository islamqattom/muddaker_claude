<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['attendance', 'recitation', 'general']);
            $table->enum('condition_type', ['absent_days', 'late_days', 'failed_sessions', 'not_recitate_count']);
            $table->integer('threshold')->unsigned();
            $table->integer('period_days')->unsigned()->default(30);
            $table->json('level_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_rules');
    }
};
