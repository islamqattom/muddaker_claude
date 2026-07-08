<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fkExists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'meeting_tasks'
               AND COLUMN_NAME = 'meeting_minute_id'
               AND REFERENCED_TABLE_NAME = 'meeting_minutes'
             LIMIT 1"
        );

        if ($fkExists && !empty($fkExists->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `meeting_tasks` DROP FOREIGN KEY `{$fkExists->CONSTRAINT_NAME}`");
        }

        DB::statement("ALTER TABLE `meeting_tasks` MODIFY `meeting_minute_id` BIGINT UNSIGNED NULL");

        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->foreign('meeting_minute_id')
                ->references('id')->on('meeting_minutes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $fkExists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'meeting_tasks'
               AND COLUMN_NAME = 'meeting_minute_id'
               AND REFERENCED_TABLE_NAME = 'meeting_minutes'
             LIMIT 1"
        );

        if ($fkExists && !empty($fkExists->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `meeting_tasks` DROP FOREIGN KEY `{$fkExists->CONSTRAINT_NAME}`");
        }

        DB::statement("ALTER TABLE `meeting_tasks` MODIFY `meeting_minute_id` BIGINT UNSIGNED NOT NULL");

        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->foreign('meeting_minute_id')
                ->references('id')->on('meeting_minutes')
                ->cascadeOnDelete();
        });
    }
};
