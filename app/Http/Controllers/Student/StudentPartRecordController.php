<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasStudentPartRecords;
use App\Models\QuranPart;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPartRecordController extends Controller
{
    use HasStudentPartRecords;

    public function show()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $allSessions = $this->getSessionsForStudent($student->id);
        $records = $this->autoFillRecords($student, $allSessions);
        $parts = QuranPart::orderBy('part_number')->get();

        $partSessionTypes = $this->buildPartSessionTypes($allSessions);

        $mafsilCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_MAFSIL)->count();
        $mutqinSardCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_RECITATION)->count();

        $totalMafsilAndMutqin = $mafsilCount + $mutqinSardCount;

        return view('student.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
            'partSessionTypes' => $partSessionTypes,
            'mafsilCount' => $mafsilCount,
            'totalMafsilAndMutqin' => $totalMafsilAndMutqin,
        ]);
    }
}