<?php

namespace Tests\Feature;

use App\Services\ReportService;
use Carbon\Carbon;
use Tests\TestCase;

class ReportDayTotalsTest extends TestCase
{
    private ReportService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new ReportService();
    }

    public function test_configured_day_count_matches_configured_weekdays(): void
    {
        // September 2026: Saturdays are 5, 12, 19, 26 and Sundays are
        // 6, 13, 20, 27 -> 8 days.
        config(['app.work_days' => 'saturday,sunday']);

        $this->assertSame(8, $this->svc->workDaysInRange(
            Carbon::create(2026, 9, 1),
            Carbon::create(2026, 9, 30)
        ));
    }

    public function test_recitation_days_use_the_recitation_setting(): void
    {
        // Saturdays (5, 12, 19, 26) and Tuesdays (1, 8, 15, 22, 29) -> 9.
        config(['app.recitation_days' => 'saturday,tuesday']);

        $this->assertSame(9, $this->svc->recitationDaysInRange(
            Carbon::create(2026, 9, 1),
            Carbon::create(2026, 9, 30)
        ));
    }

    public function test_empty_setting_yields_zero_total(): void
    {
        config(['app.work_days' => '']);

        $this->assertSame(0, $this->svc->workDaysInRange(
            Carbon::create(2026, 9, 1),
            Carbon::create(2026, 9, 30)
        ));
    }
}
