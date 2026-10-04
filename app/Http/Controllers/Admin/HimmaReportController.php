<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\BuildsHimmaReport;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\HimmaReportService;
use App\Services\ReportExporter;
use Illuminate\Http\Request;

class HimmaReportController extends Controller
{
    use BuildsHimmaReport;

    public function __construct(HimmaReportService $himma, ReportExporter $exporter)
    {
        $this->himma = $himma;
        $this->himmaExporter = $exporter;
    }

    public function index(Request $request)
    {
        return $this->himmaOverviewResponse($request, false);
    }

    public function day(Request $request, $date)
    {
        return $this->himmaDayResponse($request, $date, false);
    }

    public function student(Request $request, Student $student)
    {
        return $this->himmaStudentResponse($request, $student, false);
    }
}
