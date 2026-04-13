<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'billing_monthly_fee_per_student',
                'value' => '0',
                'group' => 'billing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'billing_reminder_frequency_days',
                'value' => '7',
                'group' => 'billing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'billing_reminder_enabled',
                'value' => '1',
                'group' => 'billing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
