<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PronunciationAttempt;
use App\Services\TajweedEvaluatorService;
use App\Services\QuranApiService;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PronunciationController extends Controller
{
    public function __construct(
        private TajweedEvaluatorService $tajweedService,
        private QuranApiService $quranApiService
    ) {}

    public function index()
    {
        $student = $this->getStudent();

        $attempts = $student->pronunciationAttempts()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.pronunciation.index', [
            'attempts' => $attempts,
        ]);
    }

    public function practice()
    {
        return view('student.pronunciation.practice', []);
    }

    public function getVerse(Request $request)
    {
        $request->validate([
            'surah' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'nullable|integer|min:1',
        ]);

        $verseEnd = $request->verse_end ?? $request->verse_start;

        try {
            if ($verseEnd == $request->verse_start) {
                $verse = $this->quranApiService->getVerse($request->surah, $request->verse_start);
                $verseText = $verse['text'] ?? '';
                $verseMeta = $verse;
            } else {
                $verses = $this->quranApiService->getVerses($request->surah, $request->verse_start, $verseEnd);
                $verseText = implode(' ', array_column($verses, 'text'));
                $verseMeta = [
                    'surah_name' => $verses[0]['surah_name'] ?? '',
                    'verses' => $verses,
                ];
            }

            if (empty($verseText)) {
                return response()->json([
                    'success' => false,
                    'error' => 'لم يتم العثور على النص للآية المحددة',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => array_merge($verseMeta, ['text' => $verseText]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'فشل في جلب نص الآية: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a|max:10240', // 10MB max
            'surah' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'nullable|integer|min:1',
        ]);

        $student = $this->getStudent();

        // Store audio file
        $audio = $request->file('audio');
        $fullPath = $audio->store('pronunciation/' . $student->id, 'public');

        $verseEnd = $request->verse_end ?? $request->verse_start;

        // Create attempt record
        $attempt = PronunciationAttempt::create([
            'student_id' => $student->id,
            'surah' => $request->surah,
            'verse_start' => $request->verse_start,
            'verse_end' => $verseEnd,
            'audio_url' => $fullPath,
            'status' => 'pending',
        ]);

        // Process synchronously for now (could be queued for production)
        $this->processAttempt($attempt);

        return redirect()->route('student.pronunciation.results', $attempt);
    }

    private function processAttempt(PronunciationAttempt $attempt)
    {
        $attempt->status = 'processing';
        $attempt->save();

        $result = $this->tajweedService->evaluate($attempt);

        $attempt->update([
            'status' => $result['status'],
            'tajweed_score' => $result['tajweed_score'],
            'error_segments' => $result['error_segments'],
            'assemblyai_response' => $result['assemblyai_response'] ?? null,
            'error_message' => $result['error'] ?? null,
        ]);
    }

    public function results(PronunciationAttempt $attempt)
    {
        $student = $this->getStudent();

        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Get verse text
        $verse = null;
        try {
            if ($attempt->verse_start == $attempt->verse_end) {
                $verse = $this->quranApiService->getVerse($attempt->surah, $attempt->verse_start);
            } else {
                $verses = $this->quranApiService->getVerses($attempt->surah, $attempt->verse_start, $attempt->verse_end);
                $verse = [
                    'text' => implode(' ', array_column($verses, 'text')),
                    'surah_name' => $verses[0]['surah_name'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            // Keep verse as null
        }

        // Ensure verse is never null for the view
        if ($verse === null || empty($verse['text'])) {
            $verse = [
                'text' => '',
                'surah_name' => 'غير متوفر',
            ];
        }

        return view('student.pronunciation.results', [
            'attempt' => $attempt,
            'verse' => $verse,
        ]);
    }

    public function destroy(PronunciationAttempt $attempt)
    {
        $student = $this->getStudent();

        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Delete audio file
        if ($attempt->audio_url) {
            Storage::disk('public')->delete($attempt->audio_url);
        }

        $attempt->delete();

        return redirect()->route('student.pronunciation.index')
            ->with('success', 'تم حذف المحاولة');
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
