<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add day columns to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('day')->nullable()->after('date'); // اليوم
        });

        // Add day columns to recitation_sessions table
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->string('day')->nullable()->after('session_date'); // اليوم
        });

        // Add day columns to warnings table
        Schema::table('warnings', function (Blueprint $table) {
            $table->string('issue_day')->nullable()->after('issue_date'); // اليوم
            $table->string('from_day')->nullable()->after('from_date'); // اليوم
            $table->string('to_day')->nullable()->after('to_date'); // اليوم
        });
    }

    public function down(): void
    {
        Schema::table('warnings', function (Blueprint $table) {
            $table->dropColumn(['issue_day', 'from_day', 'to_day']);
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropColumn('day');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('day');
        });
    }
};
