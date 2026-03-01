<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller {
    public function index() {
        $user = Auth::user();
        $role = $user->role;

        if (in_array($role, ['admin', 'supervisor'])) {
            // Admin/Supervisor - show ALL students' sessions
            $sessions = RecitationSession::with('student.user', 'part')
                ->latest()
                ->paginate(20);
        } elseif ($role === 'parent') {
            // Parent - show their children's sessions
            $parent = $user->parentProfile()->with('students')->first();
            $studentIds = $parent->students->pluck('id');
            $sessions = RecitationSession::whereIn('student_id', $studentIds)
                ->with('student.user', 'part')
                ->latest()
                ->paginate(20);
        } else {
            // Student - show own sessions
            $student = $user->student;
            $sessions = RecitationSession::where('student_id', $student->id)
                ->with('part', 'pages')
                ->latest()
                ->paginate(20);
        }

        return view('student.sessions.index', compact('sessions'));
    }

    public function show(RecitationSession $session) {
        $user = Auth::user();
        $role = $user->role;

        // Load common relationships
        $session->load(['part', 'pages', 'supervisor']);

        // For students, ensure they can only view their own sessions
        if ($role === 'student') {
            $student = $user->student;
            if ($session->student_id !== $student->id) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
            }
        }

        return view('student.sessions.show', compact('session'));
    }
}
