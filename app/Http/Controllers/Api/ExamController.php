<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $exam = Exam::create([
            'student_id' => $validated['student_id'],
            'supervisor_id' => Auth::id(),
            'exam_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        return response()->json(['exam' => $exam->load('student', 'questions')], 201);
    }

    public function addQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'surah_id' => 'required|exists:surahs,id',
            'ayah_id' => 'required|exists:ayahs,id',
            'ayah_number' => 'required|integer',
            'page_number' => 'required|integer',
            'juz_number' => 'required|integer',
            'verse_text' => 'required|string',
            'errors_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $exam = Exam::find($validated['exam_id']);
        if ($exam->status === 'completed') {
            return response()->json(['error' => 'Cannot add questions to a completed exam'], 422);
        }

        $order = ExamQuestion::where('exam_id', $validated['exam_id'])->max('order') + 1;

        $question = ExamQuestion::create([
            'exam_id' => $validated['exam_id'],
            'surah_id' => $validated['surah_id'],
            'ayah_id' => $validated['ayah_id'],
            'ayah_number' => $validated['ayah_number'],
            'page_number' => $validated['page_number'],
            'juz_number' => $validated['juz_number'],
            'verse_text' => $validated['verse_text'],
            'errors_count' => $validated['errors_count'],
            'notes' => $validated['notes'] ?? null,
            'order' => $order,
        ]);

        return response()->json(['question' => $question->load('surah', 'ayah')], 201);
    }

    public function updateQuestion(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'errors_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $question = ExamQuestion::findOrFail($id);

        if ($question->exam->status === 'completed') {
            return response()->json(['error' => 'Cannot update questions in a completed exam'], 422);
        }

        $question->update($validated);

        return response()->json(['question' => $question->fresh()->load('surah', 'ayah')]);
    }

    public function finish(int $id): JsonResponse
    {
        $exam = Exam::findOrFail($id);

        if ($exam->supervisor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $exam->update(['status' => 'completed']);

        return response()->json(['exam' => $exam->load('student', 'questions.surah', 'questions.ayah')]);
    }

    public function getQuestions(int $examId): JsonResponse
    {
        $exam = Exam::findOrFail($examId);

        return response()->json([
            'exam' => $exam->load('student'),
            'questions' => $exam->questions()->with('surah', 'ayah')->orderBy('order')->get(),
        ]);
    }

    public function getInProgress(): JsonResponse
    {
        $exam = Exam::where('supervisor_id', Auth::id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$exam) {
            return response()->json(['exam' => null]);
        }

        return response()->json([
            'exam' => $exam->load('student'),
            'questions' => $exam->questions()->with('surah', 'ayah')->orderBy('order')->get(),
        ]);
    }
}
