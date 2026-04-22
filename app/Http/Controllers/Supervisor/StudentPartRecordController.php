<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentPartRecordController extends Controller
{
    public function index()
    {
        return view('supervisor.student-parts.index');
    }

    public function show($studentId)
    {
        $student = Student::with(['user', 'level', 'supervisor'])->findOrFail($studentId);
        $this->authorizeStudent($student);

        $existingRecords = StudentPartRecord::where('student_id', $studentId)->get()->keyBy('part_number');

        $allSessions = RecitationSession::where('student_id', $studentId)
            ->whereIn('type', ['new_memorization', 'review'])
            ->where('status', 'passed')
            ->with('part')
            ->get();

        $this->autoFillRecords($student, $existingRecords, $allSessions);

        $parts = QuranPart::orderBy('part_number')->get();
        $records = StudentPartRecord::where('student_id', $studentId)->get()->keyBy('part_number');

        return view('supervisor.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
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

        $calculated = $this->calculatePartRecord($student, $partNumber, $this->getSessionsForStudent($studentId));

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

    private function getSessionsForStudent(int $studentId): \Illuminate\Database\Eloquent\Collection
    {
        return RecitationSession::where('student_id', $studentId)
            ->whereIn('type', ['new_memorization', 'review'])
            ->where('status', 'passed')
            ->with('part')
            ->get();
    }

    private function autoFillRecords(Student $student, $existingRecords, $allSessions): void
    {
        $partsToFill = [];

        foreach (range(1, 30) as $partNumber) {
            $existing = $existingRecords->get($partNumber);

            if ($existing && $existing->is_manual_override) {
                continue;
            }

            $calculated = $this->calculatePartRecord($student, $partNumber, $allSessions);

            if ($existing) {
                $isDirty = $existing->total_errors != $calculated['total_errors']
                    || $existing->total_hesitations != $calculated['total_hesitations']
                    || $existing->total_score != $calculated['total_score']
                    || $existing->is_completed != $calculated['is_completed'];
                if ($isDirty) {
                    $partsToFill[$partNumber] = $calculated;
                }
            } else {
                $partsToFill[$partNumber] = $calculated;
            }
        }

        foreach ($partsToFill as $partNumber => $data) {
            StudentPartRecord::updateOrCreate(
                ['student_id' => $student->id, 'part_number' => $partNumber],
                $data
            );
        }
    }

    private function calculatePartRecord(Student $student, int $partNumber, $allSessions): array
    {
        $partSessions = $allSessions->filter(fn($s) => $s->part?->part_number === $partNumber);

        if ($partSessions->isEmpty()) {
            return [
                'is_completed' => false,
                'total_errors' => 0,
                'total_hesitations' => 0,
                'total_score' => null,
                'last_recitation_date' => null,
                'is_manual_override' => false,
            ];
        }

        return [
            'is_completed' => true,
            'total_errors' => $partSessions->sum('total_errors'),
            'total_hesitations' => $partSessions->sum('total_hesitations'),
            'total_score' => $partSessions->avg('final_score'),
            'last_recitation_date' => $partSessions->max('session_date'),
            'is_manual_override' => false,
        ];
    }
}
