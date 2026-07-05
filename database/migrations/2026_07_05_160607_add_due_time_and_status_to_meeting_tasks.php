<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->time('due_time')->nullable()->after('due_date');
            $table->string('assignee_type', 20)->default('user')->after('assignee_id');
        });

        DB::statement("ALTER TABLE meeting_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'partially_done', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE meeting_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->dropColumn(['due_time', 'assignee_type']);
        });
    }
};
