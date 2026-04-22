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

        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $this->autoFillRecords($student);

        $parts = QuranPart::orderBy('part_number')->get();

        $records = StudentPartRecord::where('student_id', $studentId)
            ->get()
            ->keyBy('part_number');

        return view('supervisor.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
        ]);
    }

    public function update(Request $request, $studentId, $partNumber)
    {
        $student = Student::findOrFail($studentId);
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

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
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($studentId, $partNumber, $student) {
            StudentPartRecord::where('student_id', $studentId)
                ->where('part_number', $partNumber)
                ->delete();

            $calculated = $this->calculatePartRecord($student, $partNumber);

            StudentPartRecord::create(array_merge($calculated, [
                'student_id' => $studentId,
                'part_number' => $partNumber,
            ]));
        });

        return redirect()->back()->with('success', 'Part record reset to auto-calculated.');
    }

    private function autoFillRecords(Student $student): void
    {
        foreach (range(1, 30) as $partNumber) {
            $existing = StudentPartRecord::where('student_id', $student->id)
                ->where('part_number', $partNumber)
                ->first();

            if ($existing && $existing->is_manual_override) {
                continue;
            }

            $calculated = $this->calculatePartRecord($student, $partNumber);

            StudentPartRecord::updateOrCreate(
                ['student_id' => $student->id, 'part_number' => $partNumber],
                $calculated
            );
        }
    }

    private function calculatePartRecord(Student $student, int $partNumber): array
    {
        $sessions = RecitationSession::where('student_id', $student->id)
            ->whereIn('type', ['new_memorization', 'review'])
            ->where('status', 'passed')
            ->get();

        $partSessions = $sessions->filter(function ($session) use ($partNumber) {
            $part = $session->part;
            if (!$part) {
                return false;
            }
            return $part->part_number == $partNumber;
        });

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

        $totalErrors = $partSessions->sum('total_errors');
        $totalHesitations = $partSessions->sum('total_hesitations');
        $avgScore = $partSessions->avg('final_score');
        $lastDate = $partSessions->max('session_date');

        return [
            'is_completed' => true,
            'total_errors' => $totalErrors,
            'total_hesitations' => $totalHesitations,
            'total_score' => $avgScore,
            'last_recitation_date' => $lastDate,
            'is_manual_override' => false,
        ];
    }
}