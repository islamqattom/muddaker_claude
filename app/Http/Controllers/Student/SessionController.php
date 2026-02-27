<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller {
    public function index() {
        $student = Auth::user()->student;
        $sessions = RecitationSession::where('student_id',$student->id)->with('part','pages')->latest()->paginate(20);
        return view('student.sessions.index', compact('sessions'));
    }
    public function show(RecitationSession $session) {
        $session->load(['part','pages','supervisor']);
        return view('student.sessions.show', compact('session'));
    }
}
