<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPartRecordController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $allSessions = $this->getSessionsForStudent($student->id);
        $records = $this->autoFillRecords($student, $allSessions);
        $parts = QuranPart::orderBy('part_number')->get();

        $partSessionTypes = [];
        $mafsilCount = 0;
        $mutqinSardCount = 0;

        foreach ($allSessions as $session) {
            if ($session->type === RecitationSession::TYPE_MAFSIL) {
                $mafsilType = $session->mafsil_type ?? 1;
                $startPart = $session->part?->part_number;
                if ($startPart) {
                    for ($i = 0; $i < $mafsilType; $i++) {
                        $partSessionTypes[$startPart + $i] = RecitationSession::TYPE_MAFSIL;
                    }
                    $mafsilCount += $mafsilType;
                }
            } elseif ($session->type === RecitationSession::TYPE_RECITATION) {
                $partNum = $session->part?->part_number;
                if ($partNum && !isset($partSessionTypes[$partNum])) {
                    $partSessionTypes[$partNum] = $session->type;
                    $mutqinSardCount++;
                }
            }
        }

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

    private function getSessionsForStudent(int $studentId)
    {
        return RecitationSession::where('student_id', $studentId)
            ->whereIn('type', [RecitationSession::TYPE_RECITATION, RecitationSession::TYPE_MAFSIL])
            ->where('status', RecitationSession::STATUS_PASSED)
            ->with('part')
            ->get();
    }

    private function autoFillRecords(Student $student, $allSessions)
    {
        $existingRecords = StudentPartRecord::where('student_id', $student->id)->get()->keyBy('part_number');
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

        return StudentPartRecord::where('student_id', $student->id)->get()->keyBy('part_number');
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
            'total_hesitations' => $partSessions->sum('hesitations'),
            'total_score' => $partSessions->avg('final_score'),
            'last_recitation_date' => $partSessions->max('session_date'),
            'is_manual_override' => false,
        ];
    }
}