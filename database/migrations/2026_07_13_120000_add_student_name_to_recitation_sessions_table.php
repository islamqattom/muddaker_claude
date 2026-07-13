<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->change();
            $table->string('student_name', 255)->nullable()->after('student_id');
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->index('student_name');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropIndex(['student_name']);
            $table->dropColumn('student_name');
            $table->foreignId('student_id')->nullable(false)->change();
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
        });
    }
};
