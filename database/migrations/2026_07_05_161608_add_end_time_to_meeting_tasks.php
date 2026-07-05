<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->time('end_time')->nullable()->after('due_time');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_tasks', function (Blueprint $table) {
            $table->dropColumn('end_time');
        });
    }
};
