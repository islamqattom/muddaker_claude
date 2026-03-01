<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');

        if ($studentIds->isEmpty()) {
            $sessions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $students = collect();
            return view('parent.sessions.index', compact('sessions', 'students'));
        }

        // Get filter parameters
        $studentId = request('student_id');
        $type = request('type', 'all');
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

        $query = RecitationSession::whereIn('student_id', $studentIds)
            ->with('student.user', 'part', 'pages');

        // Filter by student
        if ($studentId && $parent->students->contains('id', $studentId)) {
            $query->where('student_id', $studentId);
        }

        // Filter by type
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        // Filter by date period
        if ($fromDate && $toDate) {
            $query->whereBetween('session_date', [$fromDate, $toDate]);
        }

        $sessions = $query->latest()->paginate(20);
        $students = $parent->students()->with('user')->get();

        return view('parent.sessions.index', compact('sessions', 'students'));
    }

    public function show(RecitationSession $session) {
        $session->load(['student.user', 'part', 'pages', 'supervisor']);

        return view('parent.sessions.show', compact('session'));
    }
}
