<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('status', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->string('type', 15)->change();
            $table->string('status', 20)->change();
        });
    }
};
