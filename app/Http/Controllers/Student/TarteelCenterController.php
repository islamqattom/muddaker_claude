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

    public function voiceSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $request->input('query');

        try {
            $searchResults = $this->quranApiService->search($query);

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $searchResults,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTafseer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surah' => 'required|integer|min:1|max:114',
            'verse' => 'required|integer|min:1',
            'interpretation' => 'nullable|string|in:ibn_kathir,saadi,tabari',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $surah = $request->input('surah');
        $verse = $request->input('verse');
        $interpretation = $request->input('interpretation', 'ibn_kathir');

        try {
            $verseKey = "{$surah}:{$verse}";
            $verseData = $this->quranApiService->getVerse($verseKey);

            if (!$verseData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Verse not found',
                ], 404);
            }

            $tafseerData = $this->quranApiService->getTafseer($verseKey, $interpretation);

            $translations = [];
            try {
                $translations = $this->quranApiService->getTranslation($verseKey);
            } catch (\Exception $e) {
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'verse' => $verseData,
                    'tafseer' => $tafseerData,
                    'translation' => $translations,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load tafseer: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getChallenges()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $challenges = \App\Models\Challenge::where(function ($query) use ($student) {
            $query->where('challenger_id', $student->id)
                  ->orWhere('challenged_id', $student->id);
        })
        ->whereIn('status', ['pending', 'accepted'])
        ->with(['challenger', 'challenged'])
        ->orderBy('created_at', 'desc')
        ->get();

        $formattedChallenges = $challenges->map(function ($challenge) use ($student) {
            $isChallenger = $challenge->challenger_id === $student->id;
            return [
                'id' => $challenge->id,
                'surah' => $challenge->surah,
                'verse_start' => $challenge->verse_start,
                'verse_end' => $challenge->verse_end,
                'target_date' => $challenge->target_date,
                'status' => $challenge->status,
                'is_challenger' => $isChallenger,
                'challenger' => $challenge->challenger->user->name ?? 'غير معروف',
                'challenged' => $challenge->challenged->user->name ?? 'غير معروف',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedChallenges,
        ]);
    }

    public function createChallenge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'challenged_id' => 'required|integer|exists:students,id',
            'surah' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'required|integer|gte:verse_start',
            'target_date' => 'required|date|after:today',
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

        if ($request->input('challenged_id') === $student->id) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot challenge yourself',
            ], 400);
        }

        $challenge = \App\Models\Challenge::create([
            'challenger_id' => $student->id,
            'challenged_id' => $request->input('challenged_id'),
            'surah' => $request->input('surah'),
            'verse_start' => $request->input('verse_start'),
            'verse_end' => $request->input('verse_end'),
            'target_date' => $request->input('target_date'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $challenge,
        ]);
    }

    public function respondToChallenge(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|in:accept,decline',
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

        $challenge = \App\Models\Challenge::where('id', $id)
            ->where('challenged_id', $student->id)
            ->first();

        if (!$challenge) {
            return response()->json([
                'success' => false,
                'error' => 'Challenge not found',
            ], 404);
        }

        $challenge->status = $request->input('response') === 'accept' ? 'accepted' : 'declined';
        $challenge->save();

        return response()->json([
            'success' => true,
            'data' => $challenge,
        ]);
    }

    public function challengesPage()
    {
        return view('student.live-recitation.tarteel-challenges');
    }
}