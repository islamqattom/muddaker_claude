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
        $parentProfile = $user->parentProfile()->with('students.user', 'students.level', 'students.supervisor')->firstOrFail();
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

        $partSessionTypes = $this->buildPartSessionTypes($allSessions);

        $mafsilCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_MAFSIL)->count();
        $totalMafsilAndMutqin = $this->countMafsilAndMutqin($records, $partSessionTypes);


        return view('parent.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
            'partSessionTypes' => $partSessionTypes,
            'mafsilCount' => $mafsilCount,
            'totalMafsilAndMutqin' => $totalMafsilAndMutqin,
        ]);
    }

    public function update(Request $request, $studentId, $partNumber)
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile()->firstOrFail();

        $student = $parentProfile->students()->findOrFail($studentId);

        $validated = $request->validate([
            'is_completed' => 'boolean',
            'total_errors' => 'integer|min:0',
            'total_hesitations' => 'integer|min:0',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'last_recitation_date' => 'nullable|date',
        ]);

        $validated['is_manual_override'] = true;

        \App\Models\StudentPartRecord::updateOrCreate(
            ['student_id' => $studentId, 'part_number' => $partNumber],
            $validated
        );

        return redirect()->back()->with('success', 'Part record updated.');
    }
}