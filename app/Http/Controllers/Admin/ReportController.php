<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Student, Attendance, RecitationSession, User};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller {
    public function index() {
        $stats = [
            'total_students' => Student::count(),
            'total_sessions' => RecitationSession::count(),
            'passed_sessions' => RecitationSession::where('status','passed')->count(),
            'failed_sessions' => RecitationSession::where('status','failed')->count(),
        ];
        return view('admin.reports.index', compact('stats'));
    }
    public function recitation(Request $request) {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();
        $sessions = RecitationSession::with(['student.user','supervisor','part'])->whereBetween('session_date',[$fromDate,$toDate])->latest()->paginate(30);
        $supervisors = User::where('role','supervisor')->get();
        $students = Student::with('user')->get();
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.recitation-pdf', compact('sessions','fromDate','toDate'))->setPaper('a4','landscape');
            return $pdf->download('recitation-report.pdf');
        }
        return view('admin.reports.recitation', compact('sessions','supervisors','students','fromDate','toDate'));
    }
    public function attendance(Request $request) {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();
        $attendances = Attendance::with(['student.user','supervisor'])->whereBetween('date',[$fromDate,$toDate])->latest('date')->paginate(30);
        return view('admin.reports.attendance', compact('attendances','fromDate','toDate'));
    }
}
