<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LiveRecitationService;
use App\Services\QuranApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LiveRecitationController extends Controller
{
    public function __construct(
        private LiveRecitationService $liveRecitationService,
        private QuranApiService $quranApi
    ) {}

    /**
     * Display the live recitation page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('student.live-recitation.index');
    }

    /**
     * Get or create API token for live recitation
     * Uses session auth to identify user, returns Sanctum token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken()
    {
        $user = Auth::user();

        // Clean up old tokens periodically (keep max 5 recent tokens)
        $tokenCount = $user->tokens()->where('name', 'like', 'live-recitation-%')->count();
        if ($tokenCount >= 5) {
            $user->tokens()
                ->where('name', 'like', 'live-recitation-%')
                ->orderBy('created_at', 'asc')
                ->limit($tokenCount - 4)
                ->delete();
        }

        // Create new token
        $tokenResult = $user->createToken('live-recitation-' . time());

        return response()->json([
            'success' => true,
            'token' => $tokenResult->plainTextToken,
        ]);
    }

    /**
     * Preview verses without creating a session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewVerses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:page,range,surah',
            'start_page' => 'required_if:mode,page,range|integer|min:1|max:604',
            'end_page' => 'required_if:mode,range|integer|min:1|max:604|gte:start_page',
            'surah' => 'required_if:mode,surah|integer|min:1|max:114',
            'verse_start' => 'required_if:mode,surah|integer|min:1',
            'verse_end' => 'required_if:mode,surah|integer|gte:verse_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $versesData = $this->liveRecitationService->getVersesForSession(
                $request->input('mode'),
                (int) $request->input('start_page', 1),
                (int) $request->input('end_page', $request->input('start_page', 1)),
                $request->input('surah') ? (int) $request->input('surah') : null,
                $request->input('verse_start') ? (int) $request->input('verse_start') : null,
                $request->input('verse_end') ? (int) $request->input('verse_end') : null,
                $request->input('reciter') ?: null,
            );

            if (empty($versesData['verses'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'لم يتم العثور على آيات للنطاق المحدد',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'verses' => $versesData['verses'],
                    'audio_urls' => $versesData['audio_urls'],
                    'word_segments' => $versesData['word_segments'] ?? null,
                    'verse_words' => $versesData['verse_words'] ?? null,
                    'tajweed_rules' => $versesData['tajweed_rules'] ?? null,
                    'concordance' => $versesData['concordance'] ?? null,
                    'mutashabihat' => $versesData['mutashabihat'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Live recitation preview error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب الآيات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a new live recitation session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:page,range,surah',
            'start_page' => 'required_if:mode,page,range|integer|min:1|max:604',
            'end_page' => 'required_if:mode,range|integer|min:1|max:604|gte:start_page',
            'surah' => 'required_if:mode,surah|integer|min:1|max:114',
            'verse_start' => 'required_if:mode,surah|integer|min:1',
            'verse_end' => 'required_if:mode,surah|integer|gte:verse_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated',
            ], 401);
        }

        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found for user: ' . $user->id,
            ], 404);
        }

        try {
            Log::info('Live recitation start attempt', [
                'user_id' => $user->id,
                'mode' => $request->input('mode'),
                'start_page' => $request->input('start_page'),
                'end_page' => $request->input('end_page'),
            ]);

            $result = $this->liveRecitationService->startSession($student, [
                'mode' => $request->input('mode'),
                'start_page' => $request->input('start_page', 1),
                'end_page' => $request->input('end_page', $request->input('start_page', 1)),
                'surah' => $request->input('surah'),
                'verse_start' => $request->input('verse_start'),
                'verse_end' => $request->input('verse_end'),
                'reciter' => $request->input('reciter') ?: null,
            ]);
        } catch (\Exception $e) {
            Log::error('Live recitation start error', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'خطأ في بدء التسميع: ' . $e->getMessage(),
            ], 500);
        }

        Log::info('Live recitation start result', ['success' => $result['success'], 'error' => $result['error'] ?? null]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        $responseData = [
            'session_id' => $result['session']->id,
            'verses' => $result['verses'],
            'audio_urls' => $result['audio_urls'],
            'websocket_token' => $result['websocket_token']['token'] ?? null,
            'websocket_url' => $result['websocket_url'] ?? null,
        ];

        foreach (['word_segments', 'verse_words', 'tajweed_rules', 'concordance', 'mutashabihat'] as $field) {
            if (!empty($result[$field])) {
                $responseData[$field] = $result[$field];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $responseData,
        ]);
    }

    /**
     * Get session for current student (helper to avoid repeated lookup pattern)
     *
     * @param int $id
     * @return \App\Models\LiveRecitationSession|null
     */
    private function getSessionForStudent(int $id): ?\App\Models\LiveRecitationSession
    {
        $user = Auth::user();
        $student = $user->student ?? null;
        if (!$student) {
            return null;
        }
        return \App\Models\LiveRecitationSession::where('id', $id)
            ->where('student_id', $student->id)
            ->first();
    }

    /**
     * Get session details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSession(int $id)
    {
        $session = $this->getSessionForStudent($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * End a session and get report
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function endSession(int $id)
    {
        $session = $this->getSessionForStudent($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        if ($session->status === 'completed' || $session->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'error' => 'Session already ended',
            ], 400);
        }

        $this->liveRecitationService->endSession($id);
        $report = $this->liveRecitationService->generateReport($id);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Cancel a session
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSession(int $id)
    {
        $session = $this->getSessionForStudent($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        $this->liveRecitationService->cancelSession($id);

        return response()->json([
            'success' => true,
            'message' => 'Session cancelled',
        ]);
    }

    /**
     * Record an error
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:live_recitation_sessions,id',
            'surah' => 'required|integer|min:1|max:114',
            'verse_number' => 'required|integer|min:1',
            'verse_index' => 'required|integer|min:0',
            'expected_text' => 'required|string|max:500',
            'detected_text' => 'required|string|max:500',
            'similarity' => 'required|numeric|min:0|max:100',
            'error_type' => 'required|in:missing,mismatch,extra,unclear',
            'word_level_errors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $session = $this->getSessionForStudent($request->input('session_id'));

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        $error = $this->liveRecitationService->recordError($request->input('session_id'), [
            'surah' => $request->input('surah'),
            'verse_number' => $request->input('verse_number'),
            'verse_index' => $request->input('verse_index'),
            'expected_text' => $request->input('expected_text'),
            'detected_text' => $request->input('detected_text'),
            'similarity' => $request->input('similarity'),
            'error_type' => $request->input('error_type'),
            'word_level_errors' => $request->input('word_level_errors'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'error_id' => $error->id,
                'total_errors' => $session->fresh()->total_errors,
            ],
        ]);
    }

    /**
     * Update progress
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:live_recitation_sessions,id',
            'verse_index' => 'required|integer|min:0',
            'similarity' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $session = $this->getSessionForStudent($request->input('session_id'));

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        $this->liveRecitationService->updateProgress(
            $request->input('session_id'),
            $request->input('verse_index'),
            $request->input('similarity')
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get session report
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReport(int $id)
    {
        $session = $this->getSessionForStudent($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }

        $report = $this->liveRecitationService->generateReport($id);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get available reciters
     */
    public function getReciters()
    {
        return response()->json([
            'success' => true,
            'reciters' => $this->quranApi->getAvailableReciters(),
        ]);
    }
}
