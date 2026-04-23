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