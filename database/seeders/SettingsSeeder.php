<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'center_name', 'value' => 'مركز التسميع', 'group' => 'general'],
            ['key' => 'center_email', 'value' => '', 'group' => 'general'],
            ['key' => 'center_phone', 'value' => '', 'group' => 'general'],
            ['key' => 'work_start_time', 'value' => '08:00', 'group' => 'general'],
            ['key' => 'work_end_time', 'value' => '20:00', 'group' => 'general'],
            ['key' => 'work_days', 'value' => 'saturday,sunday,monday,tuesday,thursday', 'group' => 'general'],
            ['key' => 'enable_notifications', 'value' => 'true', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
