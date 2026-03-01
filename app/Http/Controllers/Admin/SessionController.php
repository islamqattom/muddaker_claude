<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Student, Supervisor, RecitationSession};
use Illuminate\Http\Request;
use Carbon\Carbon;

class SessionController extends Controller {
    public function index(Request $request) {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'supervisor_id' => 'nullable|exists:supervisors,user_id',
            'type' => 'nullable|in:new_memorization,review,unmastered,recitation,mafsil,all',
        ]);

        // Handle period filter
        $period = $request->period ?? 'all';
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
                $fromDate = Carbon::parse($request->from_date ?? Carbon::now()->startOfMonth());
                $toDate = Carbon::parse($request->to_date ?? Carbon::now());
                break;
            default:
                $fromDate = null;
                $toDate = null;
                break;
        }

        $query = RecitationSession::with('student.user', 'part', 'supervisor');

        // Filter by supervisor (supervisor_id is now the user_id of the supervisor)
        if ($request->supervisor_id) {
            $query->whereHas('student', fn($q) => $q->where('supervisor_id', $request->supervisor_id));
        }

        // Filter by student
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by type
        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by date period
        if ($fromDate && $toDate) {
            $query->whereBetween('session_date', [$fromDate, $toDate]);
        }

        $sessions = $query->latest()->paginate(20);

        // Get data for dropdowns
        $students = Student::with('user')->get();
        $supervisors = Supervisor::with('user')->get();

        return view('admin.sessions.index', compact('sessions', 'students', 'supervisors'));
    }

    public function show(RecitationSession $session) {
        $session->load(['student.user', 'part', 'pages', 'supervisor']);

        return view('admin.sessions.show', compact('session'));
    }
}
