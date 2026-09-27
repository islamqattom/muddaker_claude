<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\Warning;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    public function index(Request $request)
    {
        $warnings = Warning::with(['student.user', 'issuedBy'])
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->student_id, function ($query) use ($request) {
                return $query->where('student_id', $request->student_id);
            })
            ->latest()
            ->paginate(20);

        return view('admin.warnings.index', compact('warnings'));
    }

    public function show(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        return view('admin.warnings.show', compact('warning'));
    }

    public function destroy(Warning $warning)
    {
        // Authorization: ensure warning belongs to a student under this admin's supervision
        $student = $warning->student;
        if (!$student || $student->supervisor_id !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }
        $warning->delete();
        return redirect()->route('admin.warnings.index')->with('success', 'تم حذف الإنذار');
    }

    public function create(Request $request)
    {
        $students = Student::with(['user', 'level'])->orderedByName()->get();

        $selectedStudent = null;
        $attendanceStats = null;
        $recitationStats = null;

        if ($request->student_id) {
            $selectedStudent = Student::with(['user', 'level'])->find($request->student_id);

            if ($selectedStudent) {
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now();

                $attendanceStats = [
                    'present' => Attendance::where('student_id', $selectedStudent->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'present')->count(),
                    'absent' => Attendance::where('student_id', $selectedStudent->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'absent')->count(),
                    'late' => Attendance::where('student_id', $selectedStudent->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'late')->count(),
                    'excused' => Attendance::where('student_id', $selectedStudent->id)->whereBetween('date', [$fromDate, $toDate])->where('status', 'excused')->count(),
                    'total' => Attendance::where('student_id', $selectedStudent->id)->whereBetween('date', [$fromDate, $toDate])->count(),
                ];

                $recitationStats = [
                    'total' => RecitationSession::where('student_id', $selectedStudent->id)->whereBetween('session_date', [$fromDate, $toDate])->count(),
                    'passed' => RecitationSession::where('student_id', $selectedStudent->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'passed')->count(),
                    'failed' => RecitationSession::where('student_id', $selectedStudent->id)->whereBetween('session_date', [$fromDate, $toDate])->where('status', 'failed')->count(),
                ];
            }
        }

        return view('admin.warnings.create', compact('students', 'selectedStudent', 'attendanceStats', 'recitationStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        $attendanceData = null;
        if ($request->attendance_data) {
            $decoded = json_decode($request->attendance_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['attendance_data' => 'Invalid JSON format in attendance data'])->withInput();
            }
            $attendanceData = $decoded;
        }

        $recitationData = null;
        if ($request->recitation_data) {
            $decoded = json_decode($request->recitation_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['recitation_data' => 'Invalid JSON format in recitation data'])->withInput();
            }
            $recitationData = $decoded;
        }

        $testData = null;
        if ($request->test_data) {
            $decoded = json_decode($request->test_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['test_data' => 'Invalid JSON format in test data'])->withInput();
            }
            $testData = $decoded;
        }

        $student = Student::findOrFail($request->student_id);

        $lastWarning = Warning::where('warning_number', 'LIKE', 'WRN-' . date('Y') . '-%')
            ->orderBy('id', 'desc')
            ->first();
        $lastNum = $lastWarning ? (int) substr($lastWarning->warning_number, -4) : 0;
        $warningNumber = 'WRN-' . date('Y') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        $warning = Warning::create([
            'student_id' => $request->student_id,
            'issued_by' => auth()->id(),
            'warning_number' => $warningNumber,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'issue_day' => $student->getArabicDay($request->issue_date),
            'from_date' => $request->from_date,
            'from_day' => $request->from_date ? $student->getArabicDay($request->from_date) : null,
            'to_date' => $request->to_date,
            'to_day' => $request->to_date ? $student->getArabicDay($request->to_date) : null,
            'total_days' => $request->total_days,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_sessions' => $request->total_sessions,
            'failed_sessions' => $request->failed_sessions,
            'additional_notes' => $request->additional_notes,
            'attendance_data' => $attendanceData,
            'recitation_data' => $recitationData,
            'test_data' => $testData,
        ]);

        $this->notifyStudentAndParent($student, $warning);

        return redirect()->route('admin.warnings.show', $warning)->with('success', 'تم إضافة الإنذار بنجاح');
    }

    public function edit(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        $students = Student::with(['user', 'level'])->orderedByName()->get();

        return view('admin.warnings.edit', compact('warning', 'students'));
    }

    public function update(Request $request, Warning $warning)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        $student = Student::findOrFail($request->student_id);

        $warning->update([
            'student_id' => $request->student_id,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'issue_day' => $student->getArabicDay($request->issue_date),
            'from_date' => $request->from_date,
            'from_day' => $request->from_date ? $student->getArabicDay($request->from_date) : null,
            'to_date' => $request->to_date,
            'to_day' => $request->to_date ? $student->getArabicDay($request->to_date) : null,
            'total_days' => $request->total_days,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_sessions' => $request->total_sessions,
            'failed_sessions' => $request->failed_sessions,
            'additional_notes' => $request->additional_notes,
        ]);

        return redirect()->route('admin.warnings.show', $warning)->with('success', 'تم تحديث الإنذار بنجاح');
    }

    public function getAttendance(Request $request, Student $student)
    {
        $fromDate = $request->from_date ?? date('Y-m-01');

        try {
            $attendances = Attendance::where('student_id', $student->id)
                ->where('date', '>=', $fromDate)
                ->whereIn('status', ['absent', 'late', 'excused'])
                ->orderBy('date', 'desc')
                ->get();

            $attendances = $attendances->map(function ($record) {
                return [
                    'id' => $record->id,
                    'day' => $record->day,
                    'date' => $record->date->toDateString(),
                    'status' => $record->status,
                    'arrival_time' => $record->arrival_time ? $this->convertTo12HourFormat($record->arrival_time) : null,
                    'notes' => $record->notes,
                ];
            });

            return response()->json($attendances);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch attendance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch attendance data'], 500);
        }
    }

    public function getRecitations(Request $request, Student $student)
    {
        $fromDate = $request->from_date ?? date('Y-m-01');

        try {
            $recitations = RecitationSession::where('student_id', $student->id)
                ->where('session_date', '>=', $fromDate)
                ->whereIn('status', ['failed', 'not_recitate'])
                ->with(['pages' => function ($query) {
                    $query->select('id', 'session_id', 'page_number', 'is_passed', 'color_status');
                }])
                ->orderBy('session_date', 'desc')
                ->get();

            $recitations = $recitations->map(function ($session) {
                $passedPages = $session->pages->where('is_passed', true)->count();
                $failedPages = $session->pages->where('is_passed', false)->count();

                return [
                    'id' => $session->id,
                    'day' => $session->day,
                    'session_date' => $session->session_date->toDateString(),
                    'type' => $session->type,
                    'status' => $session->status,
                    'total_pages' => $session->total_pages,
                    'passed_pages' => $passedPages,
                    'failed_pages' => $failedPages,
                    'notes' => $session->notes,
                ];
            });

            return response()->json($recitations);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch recitations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch recitation data'], 500);
        }
    }

    private function convertTo12HourFormat($time)
    {
        if (!$time) {
            return null;
        }
        $dateTime = Carbon::createFromFormat('H:i:s', $time);
        if ($dateTime) {
            return $dateTime->format('h:i A');
        }
        $dateTime = Carbon::createFromFormat('H:i', $time);
        if ($dateTime) {
            return $dateTime->format('h:i A');
        }
        return $time;
    }

    private function notifyStudentAndParent(Student $student, Warning $warning): void
    {
        $notificationService = app(NotificationService::class);

        if ($student->user) {
            try {
                $notificationService->sendToUser(
                    $student->user,
                    'warning',
                    "إنذار جديد",
                    "تم إصدار إنذار جديد: {$warning->title}",
                    route('student.warnings.show', $warning->id)
                );
            } catch (\Exception $e) {
                \Log::error("Failed to notify student {$student->id} about warning {$warning->id}: " . $e->getMessage());
            }
        }

        $parent = $student->parents()->first();
        if ($parent && $parent->user) {
            try {
                $notificationService->sendToUser(
                    $parent->user,
                    'warning',
                    "إنذار جديد لابنكم",
                    "تم إصدار إنذار جديد لابنكم {$student->name}: {$warning->title}",
                    route('parent.warnings.show', $warning->id)
                );
            } catch (\Exception $e) {
                \Log::error("Failed to notify parent {$parent->id} about warning {$warning->id}: " . $e->getMessage());
            }
        }
    }
}
