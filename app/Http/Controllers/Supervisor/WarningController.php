<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Warning, Student, Attendance, RecitationSession, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WarningController extends Controller
{
    public function index(Request $request)
    {
        $supervisorId = Auth::id();
        $warnings = Warning::where('issued_by', $supervisorId)
            ->with(['student.user'])
            ->when($request->student_id, function($query) use ($request) {
                return $query->where('student_id', $request->student_id);
            })
            ->when($request->type, function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20);

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        return view('supervisor.warnings.index', compact('warnings', 'students'));
    }

    public function create(Request $request)
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        $student = null;
        $attendanceStats = null;
        $recitationStats = null;

        if ($request->student_id) {
            $student = Student::with('user')->findOrFail($request->student_id);
            $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
            $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

            $attendanceStats = [
                'present' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'present')->count(),
                'absent' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'absent')->count(),
                'late' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'late')->count(),
                'excused' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'excused')->count(),
                'total' => Attendance::where('student_id', $student->id)->whereBetween('date', [$fromDate, $toDate])->count(),
            ];

            $recitationStats = [
                'total' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->count(),
                'passed' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'passed')->count(),
                'failed' => RecitationSession::where('student_id', $student->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'failed')->count(),
            ];
        }

        return view('supervisor.warnings.create', compact('students', 'student', 'attendanceStats', 'recitationStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $warning = Warning::create([
            'student_id' => $request->student_id,
            'issued_by' => Auth::id(),
            'warning_number' => Warning::generateWarningNumber(),
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_days' => $request->total_days,
            'failed_sessions' => $request->failed_sessions,
            'total_sessions' => $request->total_sessions,
            'additional_notes' => $request->additional_notes,
        ]);

        // Notify parents
        $student = Student::with('parents.user')->findOrFail($request->student_id);
        foreach ($student->parents as $parent) {
            Notification::create([
                'user_id' => $parent->user_id,
                'title' => 'إنذار جديد',
                'message' => "صدر إنذار جديد بحق ابنكم/ابنتكم {$student->user->name}",
                'type' => 'warning',
                'link' => route('parent.warnings.show', $warning->id),
            ]);
        }

        return redirect()->route('supervisor.warnings.show', $warning)
            ->with('success', 'تم إصدار الإنذار بنجاح');
    }

    public function show(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        return view('supervisor.warnings.show', compact('warning'));
    }

    public function print(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        $pdf = Pdf::loadView('supervisor.warnings.print', compact('warning'))
            ->setPaper('a4', 'portrait');
        return $pdf->download("إنذار-{$warning->warning_number}.pdf");
    }
}
