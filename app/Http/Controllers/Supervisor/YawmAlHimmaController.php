<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\YawmAlHimmaSession;
use Illuminate\Http\Request;

class YawmAlHimmaController extends Controller
{
    public function index(Request $request)
    {
        $supervisorId = auth()->id();
        $supervisorName = auth()->user()->name;

        $query = YawmAlHimmaSession::with('student.user')
            ->where(function ($q) use ($supervisorId, $supervisorName) {
                $q->whereHas('student', function ($sq) use ($supervisorId) {
                    $sq->where('supervisor_id', $supervisorId);
                })->orWhere('supervisor_name', $supervisorName);
            })
            ->orderByDesc('session_date')
            ->orderByDesc('id');

        $studentFilter = null;
        if ($request->filled('student_id')) {
            $studentFilter = $request->student_id;
            $query->where('student_id', $studentFilter);
        }

        $sessions = $query->paginate(15)->withQueryString();

        $statsQuery = YawmAlHimmaSession::where(function ($q) use ($supervisorId, $supervisorName) {
            $q->whereHas('student', function ($sq) use ($supervisorId) {
                $sq->where('supervisor_id', $supervisorId);
            })->orWhere('supervisor_name', $supervisorName);
        });
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'this_month' => (clone $statsQuery)
                ->whereMonth('session_date', now()->month)
                ->whereYear('session_date', now()->year)
                ->count(),
            'total_errors' => (clone $statsQuery)->sum('total_errors'),
            'total_hesitations' => (clone $statsQuery)->sum('total_hesitations'),
        ];

        $students = Student::with('user')
            ->where('supervisor_id', $supervisorId)
            ->orderBy('id')
            ->get();

        return view('supervisor.yawm-al-himma.index', compact('sessions', 'stats', 'students', 'studentFilter'));
    }

    public function show(YawmAlHimmaSession $session)
    {
        $supervisorId = auth()->id();
        $supervisorName = auth()->user()->name;

        $owns = ($session->student && $session->student->supervisor_id === $supervisorId)
            || $session->supervisor_name === $supervisorName;

        if (! $owns) {
            abort(403);
        }

        $session->load('student.user');
        return view('supervisor.yawm-al-himma.show', compact('session'));
    }
}
