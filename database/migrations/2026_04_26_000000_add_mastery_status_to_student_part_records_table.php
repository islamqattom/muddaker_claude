<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_part_records', function (Blueprint $table) {
            $table->string('mastery_status', 20)->default('غير متقن')->after('is_manual_override');
        });
    }

    public function down(): void
    {
        Schema::table('student_part_records', function (Blueprint $table) {
            $table->dropColumn('mastery_status');
        });
    }
};
