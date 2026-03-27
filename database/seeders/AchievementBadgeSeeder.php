<?php

namespace Database\Seeders;

use App\Services\AchievementService;
use Illuminate\Database\Seeder;

class AchievementBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $service = new AchievementService();
        $service->seedDefaultBadges();
    }
}
