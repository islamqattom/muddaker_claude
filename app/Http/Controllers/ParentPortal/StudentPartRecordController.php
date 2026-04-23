<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasStudentPartRecords;
use App\Models\QuranPart;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPartRecordController extends Controller
{
    use HasStudentPartRecords;

    public function index()
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile()->with('students.user', 'students.level')->firstOrFail();
        $students = $parentProfile->students;

        return view('parent.student-parts.index', compact('students'));
    }

    public function show($studentId)
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile()->firstOrFail();

        $student = $parentProfile->students()->with(['user', 'level', 'supervisor'])->findOrFail($studentId);

        $allSessions = $this->getSessionsForStudent($studentId);
        $records = $this->autoFillRecords($student, $allSessions);
        $parts = QuranPart::orderBy('part_number')->get();

        $partSessionTypes = [];
        $mafsilCount = 0;
        $mutqinSardCount = 0;

        foreach ($allSessions as $session) {
            if ($session->type === \App\Models\RecitationSession::TYPE_MAFSIL) {
                $mafsilType = $session->mafsil_type ?? 1;
                $startPart = $session->part?->part_number;
                if ($startPart) {
                    for ($i = 0; $i < $mafsilType; $i++) {
                        $partSessionTypes[$startPart + $i] = \App\Models\RecitationSession::TYPE_MAFSIL;
                    }
                    $mafsilCount += $mafsilType;
                }
            } elseif ($session->type === \App\Models\RecitationSession::TYPE_RECITATION) {
                $partNum = $session->part?->part_number;
                if ($partNum && !isset($partSessionTypes[$partNum])) {
                    $partSessionTypes[$partNum] = $session->type;
                    $mutqinSardCount++;
                }
            }
        }

        $totalMafsilAndMutqin = $mafsilCount + $mutqinSardCount;

        return view('parent.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
            'partSessionTypes' => $partSessionTypes,
            'mafsilCount' => $mafsilCount,
            'totalMafsilAndMutqin' => $totalMafsilAndMutqin,
        ]);
    }
}