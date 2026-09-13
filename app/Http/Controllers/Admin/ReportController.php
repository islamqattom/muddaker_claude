<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\BuildsReportData;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ReportExporter;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use BuildsReportData;

    public function __construct(
        private ReportService $reports,
        private ReportExporter $exporter,
    ) {
    }

    public function index(Request $request)
    {
        // Cumulative headline numbers (not tied to the selected period).
        $totals = [
            'total_students' => Student::count(),
        ];

        // Selected period (default: current month), resolved through the
        // same service the dedicated report pages use so the metrics are
        // identical across the dashboard and the detailed reports.
        $request->merge(['period' => $request->period ?? ReportService::PERIOD_MONTH]);
        [$fromDate, $toDate, $period] = $this->reports->resolvePeriod($request);

        $sessions    = $this->reports->recitationQuery($request)->get();
        $attendances = $this->reports->attendanceQuery($request)->get();

        $stats       = $this->reports->recitationStats($sessions);
        $attStats    = $this->reports->attendanceStats($attendances);
        $trend       = $this->reports->recitationDailyTrend($sessions);
        $topStudents = $this->reports->topStudents($sessions, 5);

        return view('admin.reports.index', compact(
            'totals', 'stats', 'attStats', 'trend', 'topStudents',
            'fromDate', 'toDate', 'period'
        ));
    }

    public function recitation(Request $request)
    {
        $data = $this->buildRecitationData($request, null);

        if ($export = $request->export) {
            return $this->exporter->exportRecitation(
                $export, $data['allSessions'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.recitation', $data);
    }

    public function attendance(Request $request)
    {
        $data = $this->buildAttendanceData($request, null);

        if ($export = $request->export) {
            return $this->exporter->exportAttendance(
                $export, $data['allAttendances'], $data['stats'], $data['fromDate'], $data['toDate'], $data['period'], $data['summary']
            );
        }

        return view('reports.attendance', $data);
    }

    public function quarter(Request $request)
    {
        return $this->aggregateResponse($request, 'quarter', null, false, 'admin-');
    }

    public function half(Request $request)
    {
        return $this->aggregateResponse($request, 'half', null, false, 'admin-');
    }

    public function year(Request $request)
    {
        return $this->aggregateResponse($request, 'year', null, false, 'admin-');
    }
}
