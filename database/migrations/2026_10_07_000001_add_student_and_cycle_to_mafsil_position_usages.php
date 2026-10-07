<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mafsil_position_usages', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('supervisor_id')
                ->constrained('students')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable()->after('updated_at');

            $table->index(
                ['student_id', 'juz_number', 'difficulty', 'completed_at'],
                'mpu_student_cycle_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('mafsil_position_usages', function (Blueprint $table) {
            $table->dropIndex('mpu_student_cycle_idx');
            $table->dropConstrainedForeignId('student_id');
            $table->dropColumn('completed_at');
        });
    }
};