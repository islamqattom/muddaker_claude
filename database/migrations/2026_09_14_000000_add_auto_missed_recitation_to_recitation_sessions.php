<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            // Links an auto-generated session to the attendance record that
            // triggered it. Deleting the attendance removes the auto session.
            $table->foreignId('attendance_id')
                ->nullable()
                ->after('student_id')
                ->constrained('attendances')
                ->cascadeOnDelete();

            // Marks sessions created by the system rather than a supervisor.
            $table->boolean('auto_generated')->default(false)->after('status');

            // One automatic session per attendance record (idempotency guard).
            $table->unique('attendance_id');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropUnique(['attendance_id']);
            $table->dropConstrainedForeignId('attendance_id');
            $table->dropColumn('auto_generated');
        });
    }
};
