<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->uuid('recurrence_group_id')->nullable()->after('meeting_minute_id');
            $table->index('recurrence_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->dropIndex(['recurrence_group_id']);
            $table->dropColumn('recurrence_group_id');
        });
    }
};