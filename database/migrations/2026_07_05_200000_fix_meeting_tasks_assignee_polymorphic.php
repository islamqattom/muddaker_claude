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
            if (!Schema::hasColumn('meeting_tasks', 'assignee_type')) {
                $table->string('assignee_type', 20)->default('user')->after('assignee_id');
            }
        });

        $fkExists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'meeting_tasks'
               AND COLUMN_NAME = 'assignee_id'
               AND REFERENCED_TABLE_NAME = 'users'
               AND REFERENCED_COLUMN_NAME = 'id'
             LIMIT 1"
        );

        if ($fkExists && !empty($fkExists->CONSTRAINT_NAME)) {
            $constraint = $fkExists->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `meeting_tasks` DROP FOREIGN KEY `{$constraint}`");
        }

        $indexes = collect(DB::select("SHOW INDEX FROM `meeting_tasks`"))->pluck('Key_name')->unique();
        if (!$indexes->contains('meeting_tasks_assignee_type_assignee_id_index')) {
            DB::statement('CREATE INDEX `meeting_tasks_assignee_type_assignee_id_index` ON `meeting_tasks` (`assignee_type`, `assignee_id`)');
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `meeting_tasks`"))->pluck('Key_name')->unique();
        if ($indexes->contains('meeting_tasks_assignee_type_assignee_id_index')) {
            DB::statement('DROP INDEX `meeting_tasks_assignee_type_assignee_id_index` ON `meeting_tasks`');
        }

        $fkExists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'meeting_tasks'
               AND COLUMN_NAME = 'assignee_id'
               AND REFERENCED_TABLE_NAME IS NULL
             LIMIT 1"
        );

        if (!$fkExists) {
            Schema::table('meeting_tasks', function (Blueprint $table) {
                $table->foreign('assignee_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
