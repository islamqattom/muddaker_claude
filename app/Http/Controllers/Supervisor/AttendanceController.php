<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{Attendance, Student};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\NotificationService;
use App\Services\RecitationService;

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
            'holiday' => $existingAttendances->filter(fn($s) => $s === 'holiday')->count(),
            'total' => $students->count(),
        ];

        return view('supervisor.attendance.index', compact('students', 'date', 'existingAttendances', 'stats'));
    }

    public function store(Request $request)
    {
        // Handle holiday action
        if ($request->action === 'holiday') {
            $request->validate([
                'date' => 'required|date',
            ]);

            $supervisorId = Auth::id();
            $date = $request->date;

            $students = Student::where('supervisor_id', $supervisorId)->get();

            foreach ($students as $student) {
                Attendance::updateOrCreate(
                    ['student_id' => $student->id, 'date' => $date],
                    [
                        'supervisor_id' => $supervisorId,
                        'status' => 'holiday',
                        'arrival_time' => null,
                        'notes' => 'عطلة',
                    ]
                );
            }

            return redirect()->back()->with('success', 'تم تسجيل اليوم كعطلة لجميع الطلاب');
        }

        // Handle normal attendance store
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,excused,holiday',
            'attendance.*.arrival_time' => 'nullable|date_format:H:i',
        ]);

        $supervisorId = Auth::id();
        $presentStudentIds = [];

        foreach ($request->attendance as $studentId => $data) {
            $attendance = Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'supervisor_id' => $supervisorId,
                    'status' => $data['status'],
                    'arrival_time' => $data['arrival_time'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            if (in_array($data['status'], ['absent', 'late'])) {
                $this->notifyParentOnAttendance($attendance);
            }

            if ($data['status'] === 'present') {
                $presentStudentIds[] = (int) $studentId;
            }
        }

        if (!empty($presentStudentIds)) {
            app(RecitationService::class)->ensureNotRecitateSessions(
                $presentStudentIds,
                $supervisorId,
                $request->date
            );
        }

        return redirect()->back()->with('success', 'تم حفظ سجل الحضور والغياب بنجاح');
    }

    public function holiday(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $supervisorId = Auth::id();
        $date = $request->date;

        // Get all students under this supervisor
        $students = Student::where('supervisor_id', $supervisorId)->get();

        foreach ($students as $student) {
            Attendance::updateOrCreate(
                ['student_id' => $student->id, 'date' => $date],
                [
                    'supervisor_id' => $supervisorId,
                    'status' => 'holiday',
                    'arrival_time' => null,
                    'notes' => 'عطلة',
                ]
            );
        }

        return redirect()->back()->with('success', 'تم تسجيل اليوم كعطلة لجميع الطلاب');
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

    public function destroy(Attendance $attendance)
    {
        // Ensure the attendance belongs to this supervisor
        if ($attendance->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $attendance->delete();

        return redirect()->back()
            ->with('success', 'تم حذف سجل الحضور بنجاح');
    }

    protected function notifyParentOnAttendance(Attendance $attendance): void
    {
        $student = $attendance->student;
        $parent = $student->parents()->first();
        if (!$parent || !$parent->user) {
            return;
        }

        $statusLabels = [
            'absent' => 'غائب',
            'late' => 'متأخر',
        ];
        $statusLabel = $statusLabels[$attendance->status] ?? $attendance->status;

        try {
            $notificationService = app(NotificationService::class);
            $notificationService->sendToUser(
                $parent->user,
                'attendance',
                "غياب/تأخر للطالب {$student->name}",
                "تم تسجيل الطالب {$student->name} ك{$statusLabel} بتاريخ {$attendance->date}",
                route('parent.attendance.index')
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify parent about attendance {$attendance->id}: " . $e->getMessage());
        }
    }
}
