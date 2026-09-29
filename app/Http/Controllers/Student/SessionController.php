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
            $sessions = RecitationSession::with('student.user', 'part', 'pages.mafsilPosition')
                ->latest('session_date')
                ->latest('id')
                ->paginate(20);
        } elseif ($role === 'parent') {
            // Parent - show their children's sessions
            $parent = $user->parentProfile()->with('students')->first();
            $studentIds = $parent->students->pluck('id');
            $sessions = RecitationSession::whereIn('student_id', $studentIds)
                ->with('student.user', 'part', 'pages.mafsilPosition')
                ->latest('session_date')
                ->latest('id')
                ->paginate(20);
        } else {
            // Student - show own sessions
            $student = $user->student;
            $sessions = RecitationSession::where('student_id', $student->id)
                ->with('part', 'pages.mafsilPosition')
                ->latest('session_date')
                ->latest('id')
                ->paginate(20);
        }

        return view('student.sessions.index', compact('sessions'));
    }

    public function show(RecitationSession $session) {
        $user = Auth::user();
        $role = $user->role;

        $session->load(['part', 'pages', 'supervisor']);

        if ($role === 'student') {
            $student = $user->student;
            if ($session->student_id !== $student->id) {
                abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
            }
        }

        if ($session->start_page && $session->end_page) {
            $recordedPages = $session->pages->keyBy('page_number');
            $allPages = collect();

            for ($p = $session->start_page; $p <= $session->end_page; $p++) {
                if (isset($recordedPages[$p])) {
                    $allPages->push($recordedPages[$p]);
                } else {
                    $allPages->push((object) [
                        'page_number' => $p,
                        'errors' => 0,
                        'hesitations' => 0,
                        'color_status' => 'green',
                        'color_label' => 'ممتاز',
                        'color_class' => 'bg-success text-white',
                        'page_score' => null,
                    ]);
                }
            }
            $session->all_pages = $allPages;
        }

        return view('student.sessions.show', compact('session'));
    }
}
