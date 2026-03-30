<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->unsignedTinyInteger('juz_number');
            $table->unsignedSmallInteger('target_pages');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'start_date', 'end_date']);
        });

        Schema::create('group_event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('group_events')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('pages_memorized')->default(0);
            $table->unsignedSmallInteger('last_page_reached');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['event_id', 'student_id']);
        });

        Schema::create('group_event_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('group_events')->onDelete('cascade');
            $table->date('date');
            $table->unsignedSmallInteger('total_pages')->default(0);
            $table->unsignedSmallInteger('participating_students')->default(0);
            $table->decimal('completion_percentage', 5, 1)->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'date']);
            $table->index(['event_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_event_progress');
        Schema::dropIfExists('group_event_participants');
        Schema::dropIfExists('group_events');
    }
};
