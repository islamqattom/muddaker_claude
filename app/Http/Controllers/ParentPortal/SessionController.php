<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');

        $sessions = RecitationSession::whereIn('student_id', $studentIds)
            ->with('student.user', 'part')
            ->latest()
            ->paginate(20);

        return view('parent.sessions.index', compact('sessions'));
    }
}
