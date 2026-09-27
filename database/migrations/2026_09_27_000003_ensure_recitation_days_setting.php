<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make sure the "recitation_days" setting exists. Existing values are
     * never overwritten, so this is safe to run on installed systems.
     */
    public function up(): void
    {
        $exists = DB::table('settings')->where('key', 'recitation_days')->exists();

        if ($exists) {
            return;
        }

        DB::table('settings')->insert([
            'key' => 'recitation_days',
            'value' => 'saturday,tuesday,thursday',
            'group' => 'general',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Only remove the row when it still holds the value this migration set.
        DB::table('settings')
            ->where('key', 'recitation_days')
            ->where('value', 'saturday,tuesday,thursday')
            ->delete();
    }
};
