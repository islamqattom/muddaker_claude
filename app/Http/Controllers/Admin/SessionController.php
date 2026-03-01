<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller {
    public function index() {
        $sessions = RecitationSession::with('student.user', 'part', 'supervisor')
            ->latest()
            ->paginate(20);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function show(RecitationSession $session) {
        $session->load(['student.user', 'part', 'pages', 'supervisor']);

        return view('admin.sessions.show', compact('session'));
    }
}
