<?php

namespace App\Services;

use App\Models\PronunciationAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Tajweed Evaluation Service
 *
 * Evaluates Arabic recitation pronunciation using AssemblyAI transcription
 * and applies tajweed rule checking.
 */
class TajweedEvaluatorService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.assemblyai.api_key', env('ASSEMBLYAI_API_KEY', ''));
    }

    /**
     * Process a pronunciation attempt
     *
     * @param PronunciationAttempt $attempt
     * @return array{tajweed_score: int, error_segments: array, status: string}
     */
    public function evaluate(PronunciationAttempt $attempt): array
    {
        try {
            // Transcribe audio via AssemblyAI
            $transcription = $this->transcribe($attempt->audio_url);

            if (!$transcription['success']) {
                return [
                    'tajweed_score' => 0,
                    'error_segments' => [],
                    'status' => 'failed',
                    'error' => $transcription['error'] ?? 'Transcription failed',
                ];
            }

            // Get the expected Arabic text
            $expectedText = $this->getExpectedText($attempt->surah, $attempt->verse_start, $attempt->verse_end);

            // Analyze and score
            $analysis = $this->analyzeRecitation($expectedText, $transcription['text']);

            return [
                'tajweed_score' => $analysis['score'],
                'error_segments' => $analysis['errors'],
                'status' => 'completed',
                'assemblyai_response' => $transcription,
            ];
        } catch (\Exception $e) {
            Log::error('Tajweed evaluation failed', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
            ]);
            return [
                'tajweed_score' => 0,
                'error_segments' => [],
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transcribe audio using AssemblyAI API
     *
     * @param string $audioUrl
     * @return array{success: bool, text?: string, words?: array, error?: string}
     */
    public function transcribe(string $audioUrl): array
    {
        if (empty($this->apiKey)) {
            Log::warning('AssemblyAI API key not configured');
            return ['success' => false, 'error' => 'AssemblyAI API key not configured'];
        }

        try {
            // If audioUrl is a local path, upload to AssemblyAI
            if (!str_starts_with($audioUrl, 'http')) {
                $audioUrl = $this->uploadAudio($audioUrl);
            }

            // Start transcription
            $response = Http::withToken($this->apiKey)
                ->post('https://api.assemblyai.com/v2/transcript', [
                    'audio_url' => $audioUrl,
                    'language_code' => 'ar', // Arabic
                ]);

            if (!$response->successful()) {
                Log::error('AssemblyAI transcription request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'Transcription request failed'];
            }

            $transcriptId = $response->json('id');

            // Poll for completion
            return $this->pollTranscription($transcriptId);
        } catch (\Exception $e) {
            Log::error('AssemblyAI transcription error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Upload local audio file to AssemblyAI
     *
     * @param string $localPath
     * @return string Remote URL
     */
    private function uploadAudio(string $localPath): string
    {
        $fullPath = Storage::path($localPath);

        // Validate file exists and is readable before attempting upload
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            throw new \Exception('Audio file not found or not readable: ' . $localPath);
        }

        $handle = fopen($fullPath, 'r');
        $response = Http::withToken($this->apiKey)
            ->upload('https://api.assemblyai.com/v2/upload', $handle);
        fclose($handle);

        if (!$response->successful()) {
            throw new \Exception('Failed to upload audio: ' . $response->body());
        }

        return $response->json('upload_url');
    }

    /**
     * Poll for transcription completion
     *
     * @param string $transcriptId
     * @return array
     */
    private function pollTranscription(string $transcriptId): array
    {
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $response = Http::withToken($this->apiKey)
                ->get("https://api.assemblyai.com/v2/transcript/{$transcriptId}");

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Polling request failed'];
            }

            $data = $response->json();

            if ($data['status'] === 'completed') {
                return [
                    'success' => true,
                    'text' => $data['text'] ?? '',
                    'words' => $data['words'] ?? [],
                ];
            }

            if (in_array($data['status'], ['error', 'failed'])) {
                return ['success' => false, 'error' => $data['error'] ?? 'Transcription error'];
            }

            // Handle 'queued' and 'processing' statuses by continuing to poll
            sleep(2);
        }

        return ['success' => false, 'error' => 'Transcription timeout'];
    }

    /**
     * Get expected Arabic text for verses
     *
     * @param int $surah
     * @param int $verseStart
     * @param int $verseEnd
     * @return string
     * @throws \Exception if verse text cannot be fetched
     */
    public function getExpectedText(int $surah, int $verseStart, int $verseEnd): string
    {
        // Use QuranApiService to fetch verses (uses cached API calls)
        $quranService = app(QuranApiService::class);

        if ($verseStart === $verseEnd) {
            $page = $quranService->getVerse($surah, $verseStart);
            if (empty($page['text'])) {
                throw new \Exception("Failed to fetch verse text for {$surah}:{$verseStart}");
            }
            return $page['text'];
        }

        $versesData = $quranService->getVerses($surah, $verseStart, $verseEnd);

        foreach ($versesData as $index => $page) {
            if (empty($page['text'])) {
                throw new \Exception("Failed to fetch verse text for {$surah}:" . ($verseStart + $index));
            }
        }

        return implode(' ', array_column($versesData, 'text'));
    }

    /**
     * Analyze recitation comparing expected vs transcribed text
     *
     * @param string $expected
     * @param string $transcribed
     * @return array{score: int, errors: array}
     */
    public function analyzeRecitation(string $expected, string $transcribed): array
    {
        // Simple character-based similarity scoring
        // In production, this would use more sophisticated Arabic text comparison

        $expected = $this->normalizeArabic($expected);
        $transcribed = $this->normalizeArabic($transcribed);

        $similarity = $this->calculateSimilarity($expected, $transcribed);

        // Calculate tajweed score (0-100)
        $score = (int) round($similarity * 100);

        // Identify error segments
        $errors = $this->identifyErrors($expected, $transcribed);

        return [
            'score' => $score,
            'errors' => $errors,
        ];
    }

    /**
     * Normalize Arabic text for comparison
     *
     * @param string $text
     * @return string
     */
    private function normalizeArabic(string $text): string
    {
        // Remove diacritics (tashkeel)
        $text = preg_replace('/[\u064B-\u065F\u0670]/u', '', $text);

        // Normalize alef variations
        $text = preg_replace('/[إأآا]/u', 'ا', $text);

        // Normalize teh marbuta - trailing gets heh form, others get teh form
        $text = str_replace('ة', 'ت', $text);  // First: all teh_marbuta -> teh
        $text = preg_replace('/ه$/u', 'ة', $text);  // Then: trailing heh that should be teh_marbuta

        // Remove non-Arabic characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

        return trim($text);
    }

    /**
     * Calculate similarity between two strings using Levenshtein distance
     *
     * @param string $expected
     * @param string $transcribed
     * @return float Similarity ratio (0-1)
     */
    private function calculateSimilarity(string $expected, string $transcribed): float
    {
        if (empty($expected) && empty($transcribed)) {
            return 1.0;
        }
        if (empty($expected) || empty($transcribed)) {
            return 0.0;
        }

        $levenshtein = levenshtein($expected, $transcribed);
        $maxLength = max(mb_strlen($expected), mb_strlen($transcribed));

        return 1 - ($levenshtein / $maxLength);
    }

    /**
     * Identify specific error segments
     *
     * @param string $expected
     * @param string $transcribed
     * @return array
     */
    private function identifyErrors(string $expected, string $transcribed): array
    {
        $errors = [];

        // Split into words
        $expectedWords = explode(' ', $expected);
        $transcribedWords = explode(' ', $transcribed);

        $transcribedIndex = 0;
        foreach ($expectedWords as $index => $expectedWord) {
            if (!isset($transcribedWords[$transcribedIndex])) {
                $errors[] = [
                    'position' => $index,
                    'type' => 'missing',
                    'expected' => $expectedWord,
                    'detected' => null,
                    'rule' => null,
                ];
                continue;
            }

            $transcribedWord = $transcribedWords[$transcribedIndex];

            if ($this->normalizeArabic($expectedWord) !== $this->normalizeArabic($transcribedWord)) {
                $rule = $this->detectTajweedRule($expectedWord, $transcribedWord);
                $errors[] = [
                    'position' => $index,
                    'type' => 'mismatch',
                    'expected' => $expectedWord,
                    'detected' => $transcribedWord,
                    'rule' => $rule,
                ];
            }

            $transcribedIndex++;
        }

        // Add extra words in transcription as errors
        while ($transcribedIndex < count($transcribedWords)) {
            $errors[] = [
                'position' => count($expectedWords),
                'type' => 'extra',
                'expected' => null,
                'detected' => $transcribedWords[$transcribedIndex],
                'rule' => null,
            ];
            $transcribedIndex++;
        }

        return $errors;
    }

    /**
     * Detect which tajweed rule might be violated
     *
     * @param string $expected
     * @param string $detected
     * @return string|null
     */
    private function detectTajweedRule(string $expected, string $detected): ?string
    {
        // Common tajweed rules for error detection
        $rules = [
            'ممدود' => ['ا', 'ى', 'و', 'ي'], // Madd (elongation)
            'غنة' => ['ن', 'م'], // Ghunnah (nasalization)
            'قلقلة' => ['ق', 'ط', 'ب', 'ج', 'د', 'خ', 'ذ'], // Qalqala
            'أحكام النون الساكنة' => ['ن'], // Noon sakinah rules
            'أحكام الراء' => ['ر'], // Ra rules
        ];

        // Simple heuristic: check character differences
        $expectedChars = mb_str_split($this->normalizeArabic($expected));
        $detectedChars = mb_str_split($this->normalizeArabic($detected));

        if (count($expectedChars) !== count($detectedChars)) {
            // Length difference suggests madd or shadda issues
            if (count($detectedChars) > count($expectedChars)) {
                return 'ممدود -elongation';
            }
            return 'نقصان في الحروف';
        }

        return null;
    }

    /**
     * Get assessment label in Arabic
     *
     * @param int $score
     * @return string
     */
    public function getAssessmentLabel(int $score): string
    {
        return match (true) {
            $score >= 95 => 'ممتاز - تجويد صحيح',
            $score >= 85 => 'جيد جداً',
            $score >= 75 => 'جيد',
            $score >= 60 => 'مقبول',
            default => 'يحتاج تحسين',
        };
    }
}
