<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('current_streak')->default(0)->after('memorized_parts');
            $table->integer('longest_streak')->default(0)->after('current_streak');
            $table->date('last_memorization_date')->nullable()->after('longest_streak');
            $table->float('accuracy_rate')->default(0)->after('last_memorization_date');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['current_streak', 'longest_streak', 'last_memorization_date', 'accuracy_rate']);
        });
    }
};
