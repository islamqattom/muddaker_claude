<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LiveRecitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LiveRecitationController extends Controller
{
    public function __construct(
        private LiveRecitationService $liveRecitationService
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
        $student = $user->student()->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found',
            ], 404);
        }

        $result = $this->liveRecitationService->startSession($student, [
            'mode' => $request->input('mode'),
            'start_page' => $request->input('start_page', 1),
            'end_page' => $request->input('end_page', $request->input('start_page', 1)),
            'surah' => $request->input('surah'),
            'verse_start' => $request->input('verse_start'),
            'verse_end' => $request->input('verse_end'),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $result['session']->id,
                'verses' => $result['verses'],
                'audio_urls' => $result['audio_urls'],
                'websocket_token' => $result['websocket_token']['token'],
                'websocket_url' => $result['websocket_token']['websocket_url'],
            ],
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
        $student = Auth::user()->student()->first();
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
}
