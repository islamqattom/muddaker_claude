<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Services\RecitationAlertService;
use App\Services\RecitationStartSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecitationAlertController extends Controller
{
    public function studentAlerts(Student $student, RecitationAlertService $alerts)
    {
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $pending = $alerts->unreadMemorizationAlertsForStudent($student->id, Auth::id());

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'alerts' => $pending->map(fn ($alert) => [
                'id' => $alert->id,
                'part_number' => $alert->part_number,
            ])->values(),
        ]);
    }

    public function failedPages(Student $student, Request $request, RecitationAlertService $alerts)
    {
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', RecitationSession::TYPES),
        ]);

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'type' => $validated['type'],
            'data' => $alerts->failedPagesForLastSession($student, $validated['type']),
        ]);
    }

    public function startSuggestion(Student $student, Request $request, RecitationStartSuggestionService $suggestions)
    {
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', RecitationSession::TYPES),
        ]);

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'type' => $validated['type'],
            'data' => $suggestions->suggest($student, $validated['type']),
        ]);
    }

    public function acknowledge(Request $request, RecitationAlertService $alerts)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $updated = $alerts->acknowledge($request->input('ids', []), Auth::id());

        return response()->json(['success' => true, 'updated' => $updated]);
    }
}
