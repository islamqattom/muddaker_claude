<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warning_rule_id')->constrained('warning_rules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'dismissed'])->default('pending');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->json('attendance_data')->nullable();
            $table->json('recitation_data')->nullable();
            $table->json('test_data')->nullable();
            $table->integer('triggered_value')->unsigned()->default(0);
            $table->integer('triggered_threshold')->unsigned()->default(0);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'warning_rule_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_drafts');
    }
};
