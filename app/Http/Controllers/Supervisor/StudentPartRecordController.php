<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasStudentPartRecords;
use App\Models\QuranPart;
use App\Models\RecitationSession;
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
        $mutqinSardCount = collect($partSessionTypes)->filter(fn($type) => $type === \App\Models\RecitationSession::TYPE_RECITATION)->count();

        $totalMafsilAndMutqin = $mafsilCount + $mutqinSardCount;

        // DEBUG: dump partSessionTypes and allSessions
        \Log::debug('student-parts show', [
            'student_id' => $studentId,
            'allSessions_count' => $allSessions->count(),
            'allSessions_types' => $allSessions->pluck('type')->unique()->values()->toArray(),
            'partSessionTypes' => $partSessionTypes,
            'recitation_sessions' => $allSessions->where('type', 'recitation')->map(fn($s) => [
                'id' => $s->id,
                'part_id' => $s->part_id,
                'part_number' => $s->part?->part_number,
                'status' => $s->status,
            ])->values()->toArray(),
        ]);

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
            'is_completed' => 'boolean',
            'total_errors' => 'integer|min:0',
            'total_hesitations' => 'integer|min:0',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'last_recitation_date' => 'nullable|date',
        ]);

        $validated['is_manual_override'] = true;

        StudentPartRecord::updateOrCreate(
            ['student_id' => $studentId, 'part_number' => $partNumber],
            $validated
        );

        return redirect()->back()->with('success', 'Part record updated.');
    }

    public function resetToAuto($studentId, $partNumber)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeStudent($student);

        $calculated = $this->calculatePartRecordWithGrouped(
            $allSessions = $this->getSessionsForStudent($studentId)->groupBy(fn($s) => $s->part?->part_number),
            $partNumber
        );

        StudentPartRecord::updateOrCreate(
            ['student_id' => $studentId, 'part_number' => $partNumber],
            array_merge($calculated, ['is_manual_override' => false])
        );

        return redirect()->back()->with('success', 'Part record reset to auto-calculated.');
    }

    private function authorizeStudent(Student $student): void
    {
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
    }
}