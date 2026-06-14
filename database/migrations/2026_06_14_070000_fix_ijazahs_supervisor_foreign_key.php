<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ijazahs', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->foreign('supervisor_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ijazahs', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->foreign('supervisor_id')
                ->references('id')->on('supervisors')
                ->onDelete('cascade');
        });
    }
};
