<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\YawmAlHimmaSession;
use Illuminate\Http\Request;

class YawmAlHimmaController extends Controller
{
    public function index(Request $request)
    {
        $parent = auth()->user()->parentProfile;
        if (! $parent) {
            abort(403, 'لا يوجد ملف ولي أمر مرتبط بهذا الحساب');
        }

        $studentIds = $parent->students()->pluck('students.id');

        $query = YawmAlHimmaSession::with('student.user')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('session_date')
            ->orderByDesc('id');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $sessions = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => YawmAlHimmaSession::whereIn('student_id', $studentIds)->count(),
            'total_errors' => YawmAlHimmaSession::whereIn('student_id', $studentIds)->sum('total_errors'),
            'total_hesitations' => YawmAlHimmaSession::whereIn('student_id', $studentIds)->sum('total_hesitations'),
            'this_month' => YawmAlHimmaSession::whereIn('student_id', $studentIds)
                ->whereMonth('session_date', now()->month)
                ->whereYear('session_date', now()->year)
                ->count(),
        ];

        $children = $parent->students()->with('user')->get();

        return view('parent.yawm-al-himma.index', compact('sessions', 'stats', 'children'));
    }

    public function show(YawmAlHimmaSession $session)
    {
        $parent = auth()->user()->parentProfile;
        if (! $parent) {
            abort(403);
        }

        $studentIds = $parent->students()->pluck('students.id');
        if (! $studentIds->contains($session->student_id)) {
            abort(403);
        }

        $session->load('student.user');
        return view('parent.yawm-al-himma.show', compact('session'));
    }
}
