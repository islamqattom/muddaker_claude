<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');

        if ($studentIds->isEmpty()) {
            $attendances = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
            $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'holiday' => 0];
            $students = collect();
            $studentId = null;
            $period = 'all';

            return view('parent.attendance.index', compact('attendances', 'stats', 'students', 'studentId', 'period'));
        }

        // Get filter parameters
        $studentId = request('student_id');
        $period = request('period', 'all');

        // Handle period filter
        switch ($period) {
            case 'today':
                $fromDate = Carbon::now()->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                break;
            case 'week':
                $fromDate = Carbon::now()->startOfWeek();
                $toDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $fromDate = Carbon::now()->startOfMonth();
                $toDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $fromDate = Carbon::parse(request('from_date', Carbon::now()->startOfMonth()));
                $toDate = Carbon::parse(request('to_date', Carbon::now()));
                break;
            default:
                $fromDate = null;
                $toDate = null;
                break;
        }

        $query = Attendance::whereIn('student_id', $studentIds)->with('student.user');

        // Filter by student
        if ($studentId && $parent->students->contains('id', $studentId)) {
            $query->where('student_id', $studentId);
        }

        // Filter by date period
        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }

        // Get all records for stats (respecting filters)
        $allAttendances = (clone $query)->get();

        // Calculate statistics from filtered dataset
        $stats = [
            'present' => $allAttendances->where('status', 'present')->count(),
            'absent' => $allAttendances->where('status', 'absent')->count(),
            'late' => $allAttendances->where('status', 'late')->count(),
            'excused' => $allAttendances->where('status', 'excused')->count(),
            'holiday' => $allAttendances->where('status', 'holiday')->count(),
        ];

        // Paginate for display
        $attendances = $query->latest('date')->paginate(30);

        $students = $parent->students()->with('user')->get()->sortBy(fn ($s) => $s->user->name ?? '')->values();

        return view('parent.attendance.index', compact('attendances', 'stats', 'students', 'studentId', 'period'));
    }
}
