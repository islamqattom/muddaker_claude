<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\WeakArea;
use App\Services\WeakAreaService;
use App\Models\Student;
use Illuminate\Http\Request;

class WeakAreaController extends Controller
{
    public function __construct(private WeakAreaService $weakAreaService) {}

    public function index()
    {
        $student = $this->getStudent();

        // Sync weak areas from session data
        $this->weakAreaService->syncWeakAreas($student);

        $weakAreas = $student->weakAreas()
            ->where('is_remediated', false)
            ->orderByDesc('priority_score')
            ->get();

        $progress = $this->weakAreaService->getRemediationProgress($student);

        return view('student.weak-areas.index', [
            'weakAreas' => $weakAreas,
            'progress' => $progress,
        ]);
    }

    public function markRemediated(WeakArea $weakArea)
    {
        $student = $this->getStudent();

        if ($weakArea->student_id !== $student->id) {
            abort(403);
        }

        $this->weakAreaService->markAsRemediated($weakArea->id);

        return redirect()->route('student.weak-areas.index')
            ->with('success', 'تم تسجيل تحسن المنطقة الضعيفة');
    }

    public function refresh()
    {
        $student = $this->getStudent();

        $count = $this->weakAreaService->syncWeakAreas($student);

        return redirect()->route('student.weak-areas.index')
            ->with('success', "تم تحديث المناطق الضعيفة - تم العثور على {$count} منطقة");
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
