<?php

namespace Tests\Unit\Services;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ReportServiceTest extends TestCase
{
    private ReportService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new ReportService();
    }

    public function test_quarter_of_resolves_correctly_for_each_month(): void
    {
        $this->assertSame(1, ReportService::quarterOf(Carbon::create(2026, 1, 15)));
        $this->assertSame(1, ReportService::quarterOf(Carbon::create(2026, 3, 31)));
        $this->assertSame(2, ReportService::quarterOf(Carbon::create(2026, 4, 1)));
        $this->assertSame(2, ReportService::quarterOf(Carbon::create(2026, 6, 30)));
        $this->assertSame(3, ReportService::quarterOf(Carbon::create(2026, 7, 1)));
        $this->assertSame(3, ReportService::quarterOf(Carbon::create(2026, 9, 30)));
        $this->assertSame(4, ReportService::quarterOf(Carbon::create(2026, 10, 1)));
        $this->assertSame(4, ReportService::quarterOf(Carbon::create(2026, 12, 31)));
    }

    public function test_quarter_of_defaults_to_today(): void
    {
        $expected = (int) ceil(Carbon::now()->month / 3);
        $this->assertSame($expected, ReportService::quarterOf());
    }

    public function test_half_of_resolves_correctly(): void
    {
        $this->assertSame(1, ReportService::halfOf(Carbon::create(2026, 1, 15)));
        $this->assertSame(1, ReportService::halfOf(Carbon::create(2026, 6, 30)));
        $this->assertSame(2, ReportService::halfOf(Carbon::create(2026, 7, 1)));
        $this->assertSame(2, ReportService::halfOf(Carbon::create(2026, 12, 31)));
    }

    public function test_quarter_range_clamping(): void
    {
        [$start, $end] = ReportService::quarterRange(2026, 3);
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-09-30', $end->toDateString());

        [$start, $end] = ReportService::quarterRange(2026, 0);
        $this->assertSame('2026-01-01', $start->toDateString());

        [$start, $end] = ReportService::quarterRange(2026, 99);
        $this->assertSame('2026-10-01', $start->toDateString());
    }

    public function test_half_range_clamping(): void
    {
        [$start, $end] = ReportService::halfRange(2026, 1);
        $this->assertSame('2026-01-01', $start->toDateString());
        $this->assertSame('2026-06-30', $end->toDateString());

        [$start, $end] = ReportService::halfRange(2026, 2);
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-12-31', $end->toDateString());

        [$start, $end] = ReportService::halfRange(2026, 0);
        $this->assertSame('2026-01-01', $start->toDateString());
    }

    public function test_year_range(): void
    {
        [$start, $end] = ReportService::yearRange(2026);
        $this->assertSame('2026-01-01', $start->toDateString());
        $this->assertSame('2026-12-31', $end->toDateString());
    }

    public function test_resolve_period_quarter_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'quarter', 'year' => 2026, 'quarter' => 2]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-04-01', $result[0]->toDateString());
        $this->assertSame('2026-06-30', $result[1]->toDateString());
        $this->assertSame('quarter', $result[2]);
    }

    public function test_resolve_period_half_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'half', 'year' => 2026, 'half' => 2]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-07-01', $result[0]->toDateString());
        $this->assertSame('2026-12-31', $result[1]->toDateString());
        $this->assertSame('half', $result[2]);
    }

    public function test_resolve_period_year_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'year', 'year' => 2025]);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2025-01-01', $result[0]->toDateString());
        $this->assertSame('2025-12-31', $result[1]->toDateString());
        $this->assertSame('year', $result[2]);
    }

    public function test_resolve_period_today_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'today']);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame(Carbon::now()->toDateString(), $result[0]->toDateString());
        $this->assertSame('today', $result[2]);
    }

    public function test_resolve_period_all_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET');
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertSame('all', $result[2]);
    }

    public function test_resolve_period_custom_returns_three_elements(): void
    {
        $req = Request::create('/', 'GET', ['period' => 'custom', 'from_date' => '2026-01-01', 'to_date' => '2026-01-31']);
        $result = $this->svc->resolvePeriod($req);

        $this->assertCount(3, $result);
        $this->assertSame('2026-01-01', $result[0]->toDateString());
        $this->assertSame('2026-01-31', $result[1]->toDateString());
        $this->assertSame('custom', $result[2]);
    }
}
