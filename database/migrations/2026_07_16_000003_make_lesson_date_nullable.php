<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE lessons MODIFY COLUMN lesson_date DATE NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE lessons SET lesson_date = CURDATE() WHERE lesson_date IS NULL');
        DB::statement('ALTER TABLE lessons MODIFY COLUMN lesson_date DATE NOT NULL');
    }
};
