<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'recitation_days')
            ->update([
                'value'      => 'saturday,sunday,tuesday,thursday',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'recitation_days')
            ->update([
                'value'      => 'saturday,sunday,tuesday,thursday,friday',
                'updated_at' => now(),
            ]);
    }
};