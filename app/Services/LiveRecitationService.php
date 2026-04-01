<?php

namespace App\Services;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Live Recitation Service
 *
 * Orchestrates real-time Quran recitation evaluation sessions.
 */
class LiveRecitationService
{
    public function __construct(
        private QuranApiService $quranApi,
        private TajweedEvaluatorService $tajweedEvaluator,
        private AssemblyAIRealtimeService $assemblyAI
    ) {}

    /**
     * Start a new live recitation session
     *
     * @param Student $student
     * @param array $params Session parameters (mode, start_page, end_page, surah, verse_start, verse_end)
     * @return array{success: bool, session?: LiveRecitationSession, verses?: array, audio_urls?: array, websocket_token?: array, error?: string}
     */
    public function startSession(Student $student, array $params): array
    {
        try {
            $mode = $params['mode'] ?? 'page';
            $startPage = $params['start_page'] ?? 1;
            $endPage = $params['end_page'] ?? $startPage;
            $surah = $params['surah'] ?? null;
            $verseStart = $params['verse_start'] ?? null;
            $verseEnd = $params['verse_end'] ?? null;

            // Get verses based on mode
            $versesData = $this->getVersesForSession($mode, $startPage, $endPage, $surah, $verseStart, $verseEnd);

            if (empty($versesData['verses'])) {
                return ['success' => false, 'error' => 'No verses found for the specified range'];
            }

            // Get WebSocket token for AssemblyAI
            $websocketToken = $this->assemblyAI->getWebSocketToken();

            if (!$websocketToken['success']) {
                return ['success' => false, 'error' => 'Failed to get WebSocket token: ' . ($websocketToken['error'] ?? 'Unknown error')];
            }

            // Create session
            $session = LiveRecitationSession::create([
                'student_id' => $student->id,
                'mode' => $mode,
                'start_page' => $startPage,
                'end_page' => $endPage,
                'surah' => $surah,
                'verse_start' => $verseStart,
                'verse_end' => $verseEnd,
                'total_verses' => count($versesData['verses']),
                'verses_completed' => 0,
                'total_errors' => 0,
                'average_similarity' => 0,
                'status' => 'active',
                'started_at' => Carbon::now(),
            ]);

            return [
                'success' => true,
                'session' => $session,
                'verses' => $versesData['verses'],
                'audio_urls' => $versesData['audio_urls'],
                'websocket_token' => $websocketToken,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to start live recitation session', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get verses for a session
     *
     * @param string $mode page|range|surah
     * @param int $startPage
     * @param int $endPage
     * @param int|null $surah
     * @param int|null $verseStart
     * @param int|null $verseEnd
     * @return array{verses: array, audio_urls: array}
     */
    public function getVersesForSession(string $mode, int $startPage, int $endPage, ?int $surah = null, ?int $verseStart = null, ?int $verseEnd = null): array
    {
        $verses = [];
        $audioUrls = [];

        if ($mode === 'surah' && $surah && $verseStart && $verseEnd) {
            // Get specific verse range from a surah
            $versesData = $this->quranApi->getVerses($surah, $verseStart, $verseEnd);

            foreach ($versesData as $index => $verse) {
                $verseIndex = $verseStart + $index;
                $verses[] = [
                    'index' => $index,
                    'surah' => $surah,
                    'verse_number' => $verseIndex,
                    'text' => $verse['text'],
                    'global_number' => $verse['number'],
                ];
                $audioUrls["{$surah}:{$verseIndex}"] = $this->quranApi->getVerseAudioUrl($verse['number']);
            }
        } else {
            // Get all verses from page range
            for ($page = $startPage; $page <= $endPage; $page++) {
                try {
                    $pageData = $this->quranApi->getPage($page);
                    $pageAudioUrls = $this->quranApi->getAudioUrls($page);

                    foreach ($pageData['ayahs'] as $ayah) {
                        $surahNum = $ayah['surah']['number'];
                        $verseNum = $ayah['numberInSurah'];

                        $verses[] = [
                            'index' => count($verses),
                            'surah' => $surahNum,
                            'verse_number' => $verseNum,
                            'text' => $ayah['text'],
                            'global_number' => $ayah['number'],
                            'page' => $page,
                        ];

                        $key = "{$surahNum}:{$verseNum}";
                        $audioUrls[$key] = $pageAudioUrls[$key] ?? $this->quranApi->getVerseAudioUrl($ayah['number']);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to fetch page {$page}", ['error' => $e->getMessage()]);
                }
            }
        }

        return ['verses' => $verses, 'audio_urls' => $audioUrls];
    }

    /**
     * Evaluate a transcribed verse against expected text
     *
     * @param string $expected Expected Arabic text
     * @param string $transcribed Transcribed text from speech recognition
     * @param float $threshold Similarity threshold (0-100)
     * @return array{is_error: bool, similarity: float, error_type: ?string, details: ?array}
     */
    public function evaluateVerse(string $expected, string $transcribed, float $threshold = 80.0): array
    {
        return $this->tajweedEvaluator->evaluateVerseRealtime($expected, $transcribed, $threshold);
    }

    /**
     * Record an error for a session
     *
     * @param int $sessionId
     * @param array $errorData Error details
     * @return LiveRecitationError
     */
    public function recordError(int $sessionId, array $errorData): LiveRecitationError
    {
        $error = LiveRecitationError::create([
            'live_session_id' => $sessionId,
            'surah' => $errorData['surah'],
            'verse_number' => $errorData['verse_number'],
            'verse_index' => $errorData['verse_index'],
            'expected_text' => $errorData['expected_text'],
            'detected_text' => $errorData['detected_text'],
            'similarity' => $errorData['similarity'],
            'error_type' => $errorData['error_type'],
            'word_level_errors' => $errorData['word_level_errors'] ?? null,
            'occurred_at' => Carbon::now(),
        ]);

        // Update session error count
        $session = LiveRecitationSession::find($sessionId);
        if ($session) {
            $session->increment('total_errors');
        }

        return $error;
    }

    /**
     * Update session progress
     *
     * @param int $sessionId
     * @param int $verseIndex
     * @param float $similarity
     * @return void
     */
    public function updateProgress(int $sessionId, int $verseIndex, float $similarity): void
    {
        $session = LiveRecitationSession::find($sessionId);
        if (!$session) {
            return;
        }

        $session->verses_completed = $verseIndex + 1;

        // Update average similarity using incremental average formula
        $newCount = $session->verses_completed;
        $oldCount = $newCount - 1;
        if ($oldCount > 0) {
            $session->average_similarity = (($session->average_similarity * $oldCount) + $similarity) / $newCount;
        } else {
            $session->average_similarity = $similarity;
        }

        $session->save();
    }

    /**
     * End and finalize a session
     *
     * @param int $sessionId
     * @return LiveRecitationSession
     */
    public function endSession(int $sessionId): LiveRecitationSession
    {
        $session = LiveRecitationSession::with('errors')->findOrFail($sessionId);

        // Calculate final score based on average similarity and error count
        $errorPenalty = $session->total_errors * 3;
        $session->final_score = max(0, $session->average_similarity - $errorPenalty);
        $session->status = 'completed';
        $session->ended_at = Carbon::now();
        $session->save();

        return $session;
    }

    /**
     * Cancel a session
     *
     * @param int $sessionId
     * @return bool
     */
    public function cancelSession(int $sessionId): bool
    {
        $session = LiveRecitationSession::find($sessionId);
        if (!$session) {
            return false;
        }

        $session->status = 'cancelled';
        $session->ended_at = Carbon::now();
        $session->save();

        return true;
    }

    /**
     * Generate a detailed report for a completed session
     *
     * @param int $sessionId
     * @return array
     */
    public function generateReport(int $sessionId): array
    {
        $session = LiveRecitationSession::with('errors')->findOrFail($sessionId);

        $errorsByVerse = [];
        foreach ($session->errors as $error) {
            $errorsByVerse[] = [
                'surah' => $error->surah,
                'verse_number' => $error->verse_number,
                'verse_index' => $error->verse_index,
                'expected_text' => $error->expected_text,
                'detected_text' => $error->detected_text,
                'similarity' => $error->similarity,
                'error_type' => $error->error_type,
                'error_type_label' => $error->error_type_label,
                'word_level_errors' => $error->word_level_errors,
                'occurred_at' => $error->occurred_at?->toIso8601String(),
            ];
        }

        return [
            'session' => [
                'id' => $session->id,
                'mode' => $session->mode,
                'mode_label' => $session->mode_label,
                'start_page' => $session->start_page,
                'end_page' => $session->end_page,
                'surah' => $session->surah,
                'verse_start' => $session->verse_start,
                'verse_end' => $session->verse_end,
                'total_verses' => $session->total_verses,
                'verses_completed' => $session->verses_completed,
                'total_errors' => $session->total_errors,
                'average_similarity' => round($session->average_similarity, 2),
                'final_score' => round($session->final_score ?? 0, 2),
                'status' => $session->status,
                'status_label' => $session->status_label,
                'started_at' => $session->started_at?->toIso8601String(),
                'ended_at' => $session->ended_at?->toIso8601String(),
            ],
            'errors' => $errorsByVerse,
            'summary' => [
                'success_rate' => $session->total_verses > 0
                    ? round((($session->total_verses - $session->total_errors) / $session->total_verses) * 100, 2)
                    : 0,
                'verses_with_errors' => count($errorsByVerse),
                'most_common_error_type' => $this->getMostCommonErrorType($session->errors),
            ],
        ];
    }

    /**
     * Get the most common error type
     */
    private function getMostCommonErrorType($errors): ?string
    {
        $counts = [];
        foreach ($errors as $error) {
            $type = $error->error_type;
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        if (empty($counts)) {
            return null;
        }

        arsort($counts);
        return array_key_first($counts);
    }
}
