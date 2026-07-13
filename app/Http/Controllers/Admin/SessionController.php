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

        $query = RecitationSession::with('student.user', 'part', 'pages', 'supervisor');

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

    public function mafsilHistory(Request $request) {
        $request->validate([
            'student_name' => 'nullable|string|max:255',
            'mahd_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:passed,failed,incomplete,in_progress,failed_continued,not_recitate',
            'mafsil_type' => 'nullable|integer|min:1|max:30',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'source' => 'nullable|in:all,public,supervised',
        ]);

        $source = $request->input('source', 'all');

        $baseQuery = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('type', RecitationSession::TYPE_MAFSIL);

        if ($source === 'public') {
            $baseQuery->whereNull('supervisor_id');
        } elseif ($source === 'supervised') {
            $baseQuery->whereNotNull('supervisor_id');
        }

        $query = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->with(['student.user', 'pages', 'supervisor'])
            ->where('type', RecitationSession::TYPE_MAFSIL);

        if ($source === 'public') {
            $query->whereNull('supervisor_id');
        } elseif ($source === 'supervised') {
            $query->whereNotNull('supervisor_id');
        }

        if ($request->filled('student_name')) {
            $query->where('student_name', 'like', '%' . $request->student_name . '%');
        }

        if ($request->filled('mahd_name')) {
            $query->where('mahd_name', 'like', '%' . $request->mahd_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mafsil_type')) {
            $query->where('mafsil_type', $request->mafsil_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('session_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('session_date', '<=', $request->to_date);
        }

        $sessions = $query->latest('session_date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'passed' => (clone $baseQuery)->where('status', 'passed')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'public_count' => RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')->where('type', RecitationSession::TYPE_MAFSIL)->whereNull('supervisor_id')->count(),
            'supervised_count' => RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')->where('type', RecitationSession::TYPE_MAFSIL)->whereNotNull('supervisor_id')->count(),
        ];

        return view('admin.mafsil-history.index', compact('sessions', 'stats', 'source'));
    }

    public function mafsilShow(int $sessionId) {
        $session = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->with(['student.user', 'pages', 'supervisor'])
            ->findOrFail($sessionId);

        abort_unless($session->type === RecitationSession::TYPE_MAFSIL, 404);

        return view('admin.mafsil-history.show', compact('session'));
    }
}
