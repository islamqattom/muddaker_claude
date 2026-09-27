<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->boolean('is_completed')->default(true)->after('is_passed');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
