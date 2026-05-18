<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentPartRecord;
use App\Models\ReviewItem;
use App\Models\TajweedRule;
use App\Services\QuranApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TarteelCenterController extends Controller
{
    public function __construct(
        private QuranApiService $quranApiService
    ) {}

    public function index()
    {
        return view('student.live-recitation.index');
    }

    public function getMemorizationPlan(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $parts = StudentPartRecord::where('student_id', $student->id)
            ->orderBy('part_number')
            ->get();

        $completedParts = $parts->where('is_completed', true)->count();
        $totalParts = $parts->count();

        $reviewItems = ReviewItem::where('student_id', $student->id)
            ->where('next_review_date', '<=', now()->addDays(3))
            ->orderBy('next_review_date')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'parts' => $parts,
                'completed_parts' => $completedParts,
                'total_parts' => $totalParts,
                'due_reviews' => $reviewItems,
            ],
        ]);
    }

    public function setDailyGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_verses' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $student->daily_verse_goal = $request->input('target_verses');
        $student->save();

        return response()->json([
            'success' => true,
            'data' => [
                'target_verses' => $student->daily_verse_goal,
            ],
        ]);
    }

    public function getMemorizationStats(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $completedParts = StudentPartRecord::where('student_id', $student->id)
            ->where('is_completed', true)
            ->count();

        $dueReviews = ReviewItem::where('student_id', $student->id)
            ->where('next_review_date', '<=', now())
            ->count();

        $recentlyMemorized = StudentPartRecord::where('student_id', $student->id)
            ->where('last_recitation_date', '>=', now()->subDays(7))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'completed_parts' => $completedParts,
                'total_parts' => 30,
                'due_reviews' => $dueReviews,
                'recently_memorized' => $recentlyMemorized,
            ],
        ]);
    }

    public function getTajweedRules()
    {
        $rules = TajweedRule::orderBy('category')->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    public function getTajweedExample($ruleId)
    {
        $rule = TajweedRule::find($ruleId);

        if (!$rule) {
            return response()->json([
                'success' => false,
                'error' => 'Rule not found',
            ], 404);
        }

        $exampleVerse = null;
        if ($rule->example_verse_key) {
            $verseData = $this->quranApiService->getVerse($rule->example_verse_key);
            if ($verseData) {
                $exampleVerse = [
                    'text' => $verseData['text'] ?? null,
                    'surah' => $verseData['surah'] ?? null,
                    'verse_number' => $verseData['verse_number'] ?? null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'rule' => $rule,
                'example_verse' => $exampleVerse,
            ],
        ]);
    }

    public function getDueReviews()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $reviews = ReviewItem::where('student_id', $student->id)
            ->where('next_review_date', '<=', now()->addDays(7))
            ->orderBy('next_review_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function getLastMemorized()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $lastPart = StudentPartRecord::where('student_id', $student->id)
            ->whereNotNull('last_recitation_date')
            ->orderBy('last_recitation_date', 'desc')
            ->first();

        if (!$lastPart) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $verseData = null;
        if ($lastPart->part_number) {
            $verseData = $this->quranApiService->getPart($lastPart->part_number);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'part' => $lastPart,
                'verse_data' => $verseData,
            ],
        ]);
    }
}