<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->foreignId('supervisor_id')->nullable()->change();
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreign('supervisor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable(false)->change();
        });

        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->foreign('supervisor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
