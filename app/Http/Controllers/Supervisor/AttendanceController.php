<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Attendance, Student};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $supervisorId = Auth::id();

        $students = Student::where('supervisor_id', $supervisorId)
            ->with(['user', 'attendances' => fn($q) => $q->where('date', $date->toDateString())])
            ->get();

        $existingAttendances = Attendance::where('supervisor_id', $supervisorId)
            ->where('date', $date->toDateString())
            ->pluck('status', 'student_id');

        $stats = [
            'present' => $existingAttendances->filter(fn($s) => $s === 'present')->count(),
            'absent' => $existingAttendances->filter(fn($s) => $s === 'absent')->count(),
            'late' => $existingAttendances->filter(fn($s) => $s === 'late')->count(),
            'excused' => $existingAttendances->filter(fn($s) => $s === 'excused')->count(),
            'total' => $students->count(),
        ];

        return view('supervisor.attendance.index', compact('students', 'date', 'existingAttendances', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.arrival_time' => 'nullable|date_format:H:i',
        ]);

        $supervisorId = Auth::id();

        foreach ($request->attendance as $studentId => $data) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'supervisor_id' => $supervisorId,
                    'status' => $data['status'],
                    'arrival_time' => $data['arrival_time'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'تم حفظ سجل الحضور والغياب بنجاح');
    }

    public function history(Request $request)
    {
        $supervisorId = Auth::id();
        $studentId = $request->student_id;
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        $query = Attendance::where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->with('student.user');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(30);

        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        return view('supervisor.attendance.history', compact('attendances', 'students', 'fromDate', 'toDate', 'studentId'));
    }
}
