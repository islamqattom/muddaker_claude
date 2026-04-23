<?php

namespace App\Http\Controllers\Admin;

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
        $students = Student::with(['user', 'level', 'supervisor'])->get()
            ->sortBy(fn($s) => $s->user->name);

        return view('admin.student-parts.index', compact('students'));
    }

    public function show($studentId)
    {
        $student = Student::with(['user', 'level', 'supervisor'])->findOrFail($studentId);

        $allSessions = $this->getSessionsForStudent($studentId);
        $records = $this->autoFillRecords($student, $allSessions);
        $parts = QuranPart::orderBy('part_number')->get();

        $partSessionTypes = $this->buildPartSessionTypes($allSessions);

        $mafsilCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_MAFSIL)->count();
        $mutqinSardCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_RECITATION)->count();

        $totalMafsilAndMutqin = $mafsilCount + $mutqinSardCount;

        return view('admin.student-parts.show', [
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
        $student = Student::findOrFail($studentId);

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