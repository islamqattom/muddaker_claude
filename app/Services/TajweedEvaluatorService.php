<?php

namespace App\Services;

use App\Models\PronunciationAttempt;
use App\Models\TajweedRuleDefinition;
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
        $this->apiKey = config('app.assemblyai.api_key', env('ASSEMBLYAI_API_KEY', ''));
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
    public function normalizeArabic(string $text): string
    {
        // Remove Uthmani-script invisible separators (hair space U+200A,
        // word joiner U+2060, etc.) that split words like "صِرَٰطَ".
        $text = preg_replace('/[\x{2000}-\x{200F}\x{2028}\x{2029}\x{205F}\x{2060}\x{FEFF}]/u', '', $text);

        // Convert dagger alef (U+0670) to regular alef — in Uthmani script
        // it represents a spoken alef (e.g. مَٰلِكِ -> مالك, صِرَٰطَ -> صراط)
        $text = str_replace("\u{0670}", 'ا', $text);

        // Remove diacritics (tashkeel) - Unicode ranges for Arabic diacritics
        // \x{064B}-\x{065F}: Fatha through Tanween Fath, Damatan, Kasratan, Fatha, Damma, Kasra, Sukun, Shadda
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);

        // Fix: U+0670 in 'رَحْمَٰنِ' does not represent a spoken alef
        $text = str_replace('رحمان', 'رحمن', $text);

        // Normalize alef variations (includes alef wasla U+0671)
        $text = preg_replace('/[إأآٱا]/u', 'ا', $text);

        // Normalize teh marbuta (ة) to teh (ت)
        $text = str_replace('ة', 'ت', $text);

        // Normalize ya-al-maqsura (ى) to ya (ي)
        $text = str_replace('ى', 'ي', $text);

        // Remove non-Arabic characters but keep Arabic letters, numbers, spaces
        $text = preg_replace('/[^\p{Arabic}\p{N}\s]/u', '', $text);

        return trim($text);
    }

    /**
     * Calculate similarity between two strings using Levenshtein distance
     *
     * @param string $expected
     * @param string $transcribed
     * @return float Similarity ratio (0-1)
     */
    public function calculateSimilarity(string $expected, string $transcribed): float
    {
        if (empty($expected) && empty($transcribed)) {
            return 1.0;
        }
        if (empty($expected) || empty($transcribed)) {
            return 0.0;
        }

        // Use custom character-based Levenshtein for proper UTF-8 support
        $levenshtein = $this->mbLevenshtein($expected, $transcribed);
        $expectedChars = mb_strlen($expected);
        $transcribedChars = mb_strlen($transcribed);
        $maxLength = max($expectedChars, $transcribedChars);

        return 1 - ($levenshtein / $maxLength);
    }

    /**
     * Multibyte character-based Levenshtein distance
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    private function mbLevenshtein(string $str1, string $str2): int
    {
        // Split into Unicode characters
        $chars1 = preg_split('//u', $str1, -1, PREG_SPLIT_NO_EMPTY);
        $chars2 = preg_split('//u', $str2, -1, PREG_SPLIT_NO_EMPTY);
        $len1 = count($chars1);
        $len2 = count($chars2);

        if ($len1 === 0) {
            return $len2;
        }
        if ($len2 === 0) {
            return $len1;
        }

        // Build distance matrix
        $matrix = [];
        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i] = [$i];
        }
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = ($chars1[$i - 1] === $chars2[$j - 1]) ? 0 : 1;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,
                    $matrix[$i][$j - 1] + 1,
                    $matrix[$i - 1][$j - 1] + $cost
                );
            }
        }

        return $matrix[$len1][$len2];
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
     * Detect category of the violated tajweed rule (Arabic label, matches TajweedRuleDefinition::category).
     *
     * Returns the category constant (e.g. "المد", "الغنة", "القلقلة", "النون الساكنة والتنوين")
     * or null if no rule could be detected.
     */
    public function detectRuleCategory(string $expected, string $detected): ?string
    {
        $ruleText = $this->detectTajweedRule($expected, $detected);
        if ($ruleText === null) {
            return null;
        }

        if (str_contains($ruleText, 'ممدود')) {
            return TajweedRuleDefinition::CATEGORY_MADD;
        }
        if (str_contains($ruleText, 'غنة')) {
            return TajweedRuleDefinition::CATEGORY_GHUNNA;
        }
        if (str_contains($ruleText, 'قلقلة')) {
            return TajweedRuleDefinition::CATEGORY_QALQALA;
        }
        if (str_contains($ruleText, 'النون')) {
            return TajweedRuleDefinition::CATEGORY_NOON_SAKIN;
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

    /**
     * Evaluate a transcribed verse in real-time
     *
     * @param string $expected Expected Arabic text
     * @param string $transcribed Transcribed text from speech recognition
     * @param float $threshold Similarity threshold (0-100), default 80
     * @return array{is_error: bool, similarity: float, error_type: ?string, details: ?array}
     */
    public function evaluateVerseRealtime(string $expected, string $transcribed, float $threshold = 80.0): array
    {
        $normalizedExpected = $this->normalizeArabic($expected);
        $normalizedTranscribed = $this->normalizeArabic($transcribed);

        $similarity = $this->calculateSimilarity($normalizedExpected, $normalizedTranscribed);
        $similarityPercent = $similarity * 100;

        if ($similarityPercent >= $threshold) {
            return [
                'is_error' => false,
                'similarity' => round($similarityPercent, 2),
                'error_type' => null,
                'details' => null,
            ];
        }

        // Identify specific errors
        $errors = $this->identifyErrors($normalizedExpected, $normalizedTranscribed);
        $errorType = $this->classifyError($errors, count(explode(' ', $normalizedExpected)), count(explode(' ', $normalizedTranscribed)));

        return [
            'is_error' => true,
            'similarity' => round($similarityPercent, 2),
            'error_type' => $errorType,
            'details' => $errors,
        ];
    }

    /**
     * Classify the type of error based on word-level errors
     */
    private function classifyError(array $errors, int $expectedWordCount, int $transcribedWordCount): string
    {
        if (empty($errors)) {
            return 'unclear';
        }

        $hasMissing = collect($errors)->contains('type', 'missing');
        $hasExtra = collect($errors)->contains('type', 'extra');
        $hasMismatch = collect($errors)->contains('type', 'mismatch');

        if ($transcribedWordCount < $expectedWordCount * 0.5) {
            return 'unclear';
        }

        if ($hasMissing && $hasExtra) {
            return 'mismatch';
        }

        if ($hasMissing) {
            return 'missing';
        }

        if ($hasExtra) {
            return 'extra';
        }

        return 'mismatch';
    }
}
