<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('location');
            $table->json('attendees');
            $table->json('absent');
            $table->text('agenda')->nullable();
            $table->text('decisions')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'approved', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('meeting_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }
};