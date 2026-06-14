<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\YawmAlHimmaSession;
use Illuminate\Http\Request;

class YawmAlHimmaController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;
        if (! $student) {
            abort(403, 'لا يوجد ملف طالب مرتبط بهذا الحساب');
        }

        $sessions = YawmAlHimmaSession::with('student.user')
            ->where('student_id', $student->id)
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(15);

        $stats = [
            'total' => YawmAlHimmaSession::where('student_id', $student->id)->count(),
            'total_errors' => YawmAlHimmaSession::where('student_id', $student->id)->sum('total_errors'),
            'total_hesitations' => YawmAlHimmaSession::where('student_id', $student->id)->sum('total_hesitations'),
            'this_month' => YawmAlHimmaSession::where('student_id', $student->id)
                ->whereMonth('session_date', now()->month)
                ->whereYear('session_date', now()->year)
                ->count(),
        ];

        return view('student.yawm-al-himma.index', compact('sessions', 'stats'));
    }

    public function show(YawmAlHimmaSession $session)
    {
        $student = auth()->user()->student;
        if (! $student || $session->student_id !== $student->id) {
            abort(403);
        }

        $session->load('student.user');
        return view('student.yawm-al-himma.show', compact('session'));
    }
}
