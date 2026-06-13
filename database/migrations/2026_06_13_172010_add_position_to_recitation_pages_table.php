<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->unsignedTinyInteger('position')->nullable()->after('page_number')
                ->comment('موضع داخل الجزء في اختبار المفصل (1=أول، 2=وسط، 3=آخر)');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_pages', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
