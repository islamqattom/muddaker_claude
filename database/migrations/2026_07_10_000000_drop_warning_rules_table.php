<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('warning_drafts');
        Schema::dropIfExists('warning_rules');
    }

    public function down(): void
    {
        Schema::create('warning_rules', function ($table) {
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

        Schema::create('warning_drafts', function ($table) {
            $table->id();
            $table->foreignId('warning_rule_id')->constrained('warning_rules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('title');
            $table->text('content');
            $table->date('issue_date');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->json('attendance_data')->nullable();
            $table->json('recitation_data')->nullable();
            $table->json('test_data')->nullable();
            $table->integer('triggered_value')->nullable();
            $table->integer('triggered_threshold')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'warning_rule_id', 'status']);
        });
    }
};
