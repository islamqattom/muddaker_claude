<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->string('mahd_name', 255)->nullable()->after('student_name');
            $table->decimal('error_score', 5, 2)->nullable()->default(3)->after('mahd_name');
            $table->decimal('hesitation_score', 5, 2)->nullable()->default(0.33)->after('error_score');
            $table->decimal('fail_score', 5, 2)->nullable()->default(60)->after('hesitation_score');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropColumn(['mahd_name', 'error_score', 'hesitation_score', 'fail_score']);
        });
    }
};
