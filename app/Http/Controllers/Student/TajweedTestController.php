<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TajweedQuizAnswer;
use App\Models\TajweedQuizAttempt;
use App\Models\TajweedRuleDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TajweedTestController extends Controller
{
    public const QUESTIONS_PER_QUIZ = 5;
    public const OPTIONS_PER_QUESTION = 4;

    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|string|max:100',
            'count' => 'nullable|integer|min:3|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()?->student;
        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Student profile not found'], 404);
        }

        $count = (int) $request->input('count', self::QUESTIONS_PER_QUIZ);
        $category = $request->input('category');

        $query = TajweedRuleDefinition::query();
        if ($category) {
            $query->where('category', $category);
        }

        $rules = $query->inRandomOrder()->limit(max($count * 4, 20))->get();

        if ($rules->count() < max($count, 4)) {
            return response()->json([
                'success' => false,
                'error' => 'لا توجد قواعد تجويد كافية لإنشاء الاختبار',
            ], 422);
        }

        $selected = $rules->shuffle()->take($count)->values();
        $allDescriptions = $rules->pluck('description')->filter()->unique()->values()->all();

        $questions = $selected->map(function (TajweedRuleDefinition $rule) use ($allDescriptions) {
            $correct = $rule->description ?: $rule->category;

            $distractors = collect($allDescriptions)
                ->reject(fn($d) => $d === $correct)
                ->shuffle()
                ->take(self::OPTIONS_PER_QUESTION - 1)
                ->values()
                ->all();

            while (count($distractors) < self::OPTIONS_PER_QUESTION - 1) {
                $distractors[] = 'إجابة أخرى';
            }

            $options = collect([$correct, ...$distractors])->shuffle()->values()->all();
            $correctIndex = array_search($correct, $options, true);

            return [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'category' => $rule->category,
                'question' => 'ما هي قاعدة "' . $rule->name . '"؟',
                'options' => $options,
                'correct_index' => $correctIndex,
            ];
        })->all();

        $attempt = TajweedQuizAttempt::create([
            'student_id' => $student->id,
            'total_questions' => count($questions),
            'correct_answers' => 0,
            'score_percentage' => 0,
            'category_filter' => $category,
            'questions_payload' => $questions,
            'started_at' => Carbon::now(),
        ]);

        $clientQuestions = array_map(function ($q) {
            unset($q['correct_index']);
            return $q;
        }, $questions);

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $attempt->id,
                'questions' => $clientQuestions,
                'started_at' => $attempt->started_at?->toIso8601String(),
            ],
        ]);
    }

    public function submit(Request $request, int $attemptId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.rule_id' => 'required|integer',
            'answers.*.selected_index' => 'required|integer|min:0',
            'answers.*.time_taken_seconds' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()?->student;
        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Student profile not found'], 404);
        }

        $attempt = TajweedQuizAttempt::where('id', $attemptId)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'error' => 'Attempt not found'], 404);
        }

        if ($attempt->finished_at) {
            return response()->json([
                'success' => true,
                'data' => $this->formatAttemptResult($attempt->fresh('answers.rule')),
            ]);
        }

        $payload = $attempt->questions_payload ?? [];
        $questionsByRule = collect($payload)->keyBy('rule_id');

        $correct = 0;
        $durationSeconds = null;
        if ($attempt->started_at) {
            $durationSeconds = (int) Carbon::now()->diffInSeconds($attempt->started_at, true);
        }

        DB::transaction(function () use ($attempt, $request, $questionsByRule, &$correct, &$durationSeconds) {
            $answersInput = $request->input('answers', []);

            foreach ($answersInput as $ans) {
                $ruleId = (int) $ans['rule_id'];
                $selectedIndex = (int) $ans['selected_index'];
                $timeTaken = isset($ans['time_taken_seconds']) ? (int) $ans['time_taken_seconds'] : null;

                $question = $questionsByRule->get($ruleId);
                if (!$question) {
                    continue;
                }

                $options = $question['options'] ?? [];
                $correctIndex = $question['correct_index'] ?? null;
                $correctText = $options[$correctIndex] ?? $question['correct'] ?? '';

                $studentAnswer = $options[$selectedIndex] ?? null;
                $isCorrect = $selectedIndex === $correctIndex;

                if ($isCorrect) {
                    $correct++;
                }

                TajweedQuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'tajweed_rule_id' => $ruleId,
                    'question_text' => $question['question'] ?? '',
                    'correct_answer' => $correctText,
                    'student_answer' => $studentAnswer,
                    'is_correct' => $isCorrect,
                    'time_taken_seconds' => $timeTaken,
                ]);
            }

            $total = $attempt->total_questions ?: max(count($answersInput), 1);
            $score = (int) round(($correct / $total) * 100);

            $attempt->update([
                'correct_answers' => $correct,
                'score_percentage' => $score,
                'duration_seconds' => $durationSeconds,
                'finished_at' => Carbon::now(),
            ]);
        });

        $attempt->refresh();
        $attempt->load('answers.rule');

        return response()->json([
            'success' => true,
            'data' => $this->formatAttemptResult($attempt),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $student = Auth::user()?->student;
        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Student profile not found'], 404);
        }

        $attempts = TajweedQuizAttempt::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn(TajweedQuizAttempt $a) => [
                'id' => $a->id,
                'total_questions' => $a->total_questions,
                'correct_answers' => $a->correct_answers,
                'score_percentage' => $a->score_percentage,
                'category_filter' => $a->category_filter,
                'duration_seconds' => $a->duration_seconds,
                'finished_at' => $a->finished_at?->toIso8601String(),
                'created_at' => $a->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $attempts,
        ]);
    }

    public function show(int $attemptId): JsonResponse
    {
        $student = Auth::user()?->student;
        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Student profile not found'], 404);
        }

        $attempt = TajweedQuizAttempt::where('id', $attemptId)
            ->where('student_id', $student->id)
            ->with('answers.rule')
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'error' => 'Attempt not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAttemptResult($attempt),
        ]);
    }

    protected function formatAttemptResult(TajweedQuizAttempt $attempt): array
    {
        return [
            'attempt_id' => $attempt->id,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->correct_answers,
            'score_percentage' => $attempt->score_percentage,
            'duration_seconds' => $attempt->duration_seconds,
            'category_filter' => $attempt->category_filter,
            'started_at' => $attempt->started_at?->toIso8601String(),
            'finished_at' => $attempt->finished_at?->toIso8601String(),
            'answers' => $attempt->answers->map(fn(TajweedQuizAnswer $a) => [
                'tajweed_rule_id' => $a->tajweed_rule_id,
                'rule_name' => $a->rule?->name,
                'category' => $a->rule?->category ?? $a->rule?->category ?? null,
                'question_text' => $a->question_text,
                'correct_answer' => $a->correct_answer,
                'student_answer' => $a->student_answer,
                'is_correct' => (bool) $a->is_correct,
                'time_taken_seconds' => $a->time_taken_seconds,
            ])->values(),
        ];
    }
}
