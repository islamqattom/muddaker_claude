<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller {
    public function index() {
        $supervisor = Auth::user()->supervisor;

        $sessions = RecitationSession::where('supervisor_id', $supervisor->id)
            ->with('student.user', 'part', 'pages', 'supervisor')
            ->latest()
            ->paginate(20);

        return view('supervisor.sessions.index', compact('sessions'));
    }

    public function show(RecitationSession $session) {
        $session->load(['student.user', 'part', 'pages', 'supervisor']);

        return view('supervisor.sessions.show', compact('session'));
    }
}
