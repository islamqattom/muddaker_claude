<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add holiday to enum using raw SQL (MySQL enum modification)
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'excused', 'holiday') DEFAULT 'present'");
    }

    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present'");
    }
};
