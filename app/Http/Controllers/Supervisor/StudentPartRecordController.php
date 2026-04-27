<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasStudentPartRecords;
use App\Models\QuranPart;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPartRecordController extends Controller
{
    use HasStudentPartRecords;

    public function index()
    {
        $students = Student::where('supervisor_id', Auth::id())
            ->with('user', 'level')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();

        return view('supervisor.student-parts.index', compact('students'));
    }

    public function show($studentId)
    {
        $student = Student::with(['user', 'level', 'supervisor'])->findOrFail($studentId);
        $this->authorizeStudent($student);

        $allSessions = $this->getSessionsForStudent($studentId);
        $records = $this->autoFillRecords($student, $allSessions);
        $parts = QuranPart::orderBy('part_number')->get();

        $partSessionTypes = $this->buildPartSessionTypes($allSessions);

        $mafsilCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_MAFSIL)->count();
        $totalMafsilAndMutqin = $this->countMafsilAndMutqin($records, $partSessionTypes);

        return view('supervisor.student-parts.show', [
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
        $this->authorizeStudent($student);

        $validated = $request->validate([
            'total_errors' => 'integer|min:0',
            'total_hesitations' => 'integer|min:0',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'session_date' => 'nullable|date',
            'mastery_status' => 'nullable|string|in:' . StudentPartRecord::MASTERY_MASTERED . ',' . StudentPartRecord::MASTERY_NOT_MASTERED,
        ]);

        $this->updatePartRecord($studentId, $partNumber, $validated);

        return redirect()->back()->with('success', 'تم تحديث سجل التسميع.');
    }

    public function resetToAuto($studentId, $partNumber)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeStudent($student);
        $this->resetPartRecordToAuto($studentId, $partNumber);

        return redirect()->back()->with('success', 'تم إعادة الجزء للاحتساب التلقائي.');
    }

    private function authorizeStudent(Student $student): void
    {
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
    }
}