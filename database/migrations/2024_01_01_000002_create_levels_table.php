<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // المستوى الأول، الثاني...
            $table->integer('level_number');
            $table->integer('new_memorization_pages'); // عدد صفحات الحفظ الجديد
            $table->integer('review_pages'); // عدد صفحات المراجعة
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
