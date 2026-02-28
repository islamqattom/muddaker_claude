<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');

        if ($studentIds->isEmpty()) {
            $attendances = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
            $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
            $students = collect();
            $studentId = null;

            return view('parent.attendance.index', compact('attendances', 'stats', 'students', 'studentId'));
        }

        // Filter by student if selected
        $studentId = request('student_id');
        $query = Attendance::whereIn('student_id', $studentIds)->with('student.user');

        if ($studentId && $parent->students->contains('id', $studentId)) {
            $query->where('student_id', $studentId);
        }

        // Get all records first for stats (single query)
        $allAttendances = (clone $query)->get();

        // Calculate statistics from full dataset
        $stats = [
            'present' => $allAttendances->where('status', 'present')->count(),
            'absent' => $allAttendances->where('status', 'absent')->count(),
            'late' => $allAttendances->where('status', 'late')->count(),
            'excused' => $allAttendances->where('status', 'excused')->count(),
        ];

        // Paginate for display
        $attendances = $query->latest('date')->paginate(30);

        $students = $parent->students()->with('user')->get();

        return view('parent.attendance.index', compact('attendances', 'stats', 'students', 'studentId'));
    }
}
