<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{RecitationSession, RecitationPage, Student, QuranPart, QuranPage};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class RecitationController extends Controller
{
    private const SESSION_VALIDATION_RULES = [
        'student_id' => 'required|exists:students,id',
        'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil,not_recitate',
        'part_id' => 'nullable|exists:quran_parts,id',
        'session_date' => 'required|date',
        'pages' => 'required|array',
        'pages.*.page_number' => 'required|integer|exists:quran_pages,page_number',
        'pages.*.errors' => 'required|integer|min:0',
        'pages.*.hesitations' => 'required|integer|min:0',
    ];

    private const MAFSIL_MAX_SCORE = 100;

    /**
     * Calculate total errors and hesitations from pages array
     */
    private function calculateTotals(array $pages, string $type): array
    {
        $totalErrors = 0;
        $totalHesitations = 0;
        foreach ($pages as $page) {
            $totalHesitations += $page['hesitations'];
            if ($type === RecitationSession::TYPE_RECITATION) {
                $totalErrors += $page['errors'] + floor($page['hesitations'] / 3);
            } else {
                $totalErrors += $page['errors'];
            }
        }
        return ['errors' => $totalErrors, 'hesitations' => $totalHesitations];
    }

    public function index()
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user', 'level')->get();
        $parts = QuranPart::orderBy('part_number')->get();

        return view('supervisor.recitation.index', compact('students', 'parts'));
    }

    public function startSession(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil,not_recitate',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $parts = QuranPart::orderBy('part_number')->get();

        return view('supervisor.recitation.session', compact('student', 'parts'));
    }

    /**
     * New Memorization Session (حفظ جديد)
     */
    public function newMemorization(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'part_id' => 'required|exists:quran_parts,id',
            'page_number' => 'required|integer|min:1',
            'continuation_part_id' => 'nullable|exists:quran_parts,id',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $part = QuranPart::findOrFail($request->part_id);

        // Validate page number is within part range
        if ($request->page_number < $part->start_page || $request->page_number > $part->end_page) {
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين ' . $part->start_page . ' و ' . $part->end_page]);
        }

        $startPage = $request->page_number;
        $pagesCount = $student->level->new_memorization_pages;

        // Get pages for this session
        $pages = [];
        $continuationPart = null;

        for ($i = 0; $i < $pagesCount; $i++) {
            $pageNum = $startPage + $i;
            if ($pageNum <= $part->end_page) {
                $pages[] = $pageNum;
            }
        }

        // If we need more pages and continuation part is provided
        $remainingNeeded = $pagesCount - count($pages);
        if ($remainingNeeded > 0 && $request->continuation_part_id) {
            $continuationPart = QuranPart::findOrFail($request->continuation_part_id);
            for ($i = 0; $i < $remainingNeeded; $i++) {
                $pageNum = $continuationPart->start_page + $i;
                if ($pageNum <= $continuationPart->end_page) {
                    $pages[] = $pageNum;
                }
            }
        }

        // Handle additional and repeated pages
        $this->processAdditionalAndRepeatedPages($pages, $request);

        return view('supervisor.recitation.new-memorization', compact('student', 'part', 'pages', 'pagesCount', 'continuationPart'));
    }

    /**
     * Review Session (مراجعة)
     */
    public function review(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'part_id' => 'required|exists:quran_parts,id',
            'page_number' => 'required|integer|min:1',
            'continuation_part_id' => 'nullable|exists:quran_parts,id',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $part = QuranPart::findOrFail($request->part_id);

        // Validate page number is within part range
        if ($request->page_number < $part->start_page || $request->page_number > $part->end_page) {
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين ' . $part->start_page . ' و ' . $part->end_page]);
        }

        $startPage = $request->page_number;
        $pagesCount = $student->level->review_pages;

        $pages = [];
        $continuationPart = null;

        for ($i = 0; $i < $pagesCount; $i++) {
            $pageNum = $startPage + $i;
            if ($pageNum <= $part->end_page) {
                $pages[] = $pageNum;
            }
        }

        // If we need more pages and continuation part is provided
        $remainingNeeded = $pagesCount - count($pages);
        if ($remainingNeeded > 0 && $request->continuation_part_id) {
            $continuationPart = QuranPart::findOrFail($request->continuation_part_id);
            for ($i = 0; $i < $remainingNeeded; $i++) {
                $pageNum = $continuationPart->start_page + $i;
                if ($pageNum <= $continuationPart->end_page) {
                    $pages[] = $pageNum;
                }
            }
        }

        // Handle additional and repeated pages
        $this->processAdditionalAndRepeatedPages($pages, $request);

        return view('supervisor.recitation.review', compact('student', 'part', 'pages', 'pagesCount', 'continuationPart'));
    }

    /**
     * Unmastered Memorization (حفظ غير متقن)
     */
    public function unmastered(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'part_id' => 'required|exists:quran_parts,id',
            'pages_count' => 'required|in:5,10,15,20',
            'page_number' => 'required|integer|min:1',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $part = QuranPart::findOrFail($request->part_id);

        // Validate page number is within part range
        if ($request->page_number < $part->start_page || $request->page_number > $part->end_page) {
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين ' . $part->start_page . ' و ' . $part->end_page]);
        }

        $startPage = $request->page_number;
        $pagesCount = $request->pages_count;

        $pages = [];
        for ($i = 0; $i < $pagesCount; $i++) {
            $pageNum = $startPage + $i;
            if ($pageNum <= $part->end_page) {
                $pages[] = $pageNum;
            }
        }

        // Handle additional and repeated pages
        $this->processAdditionalAndRepeatedPages($pages, $request);

        return view('supervisor.recitation.unmastered', compact('student', 'part', 'pages', 'pagesCount'));
    }

    /**
     * Full Recitation / Sard (سرد)
     */
    public function recitation(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'part_id' => 'required|exists:quran_parts,id',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $part = QuranPart::with('pages')->findOrFail($request->part_id);

        $pages = range($part->start_page, $part->end_page);

        return view('supervisor.recitation.recitation', compact('student', 'part', 'pages'));
    }

    /**
     * Mafsil Session (مفصل)
     */
    public function mafsil(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'mafsil_type' => 'required|in:3,6,10,15,20,25,30',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $mafsilType = $request->mafsil_type;
        $maxScore = self::MAFSIL_MAX_SCORE;
        $passingPercentage = RecitationSession::getMafsilPassingPercentage($mafsilType);
        $partsCount = $mafsilType;

        // Get all 30 parts for selection
        $allParts = QuranPart::orderBy('part_number')->get();

        // Check if parts have been selected (POST request)
        if ($request->isMethod('post') || $request->has('selected_parts')) {
            $selectedParts = $request->input('selected_parts', []);

            // Validate selected parts count
            if (count($selectedParts) != $partsCount) {
                return back()->withErrors(['selected_parts' => 'يجب اختيار ' . $partsCount . ' أجزاء']);
            }

            // Ensure all selected parts are integers to prevent SQL injection
            $selectedParts = array_map('intval', $selectedParts);

            // Validate selected parts exist using a safe query
            $parts = QuranPart::whereIn('part_number', $selectedParts)
                ->get()
                ->sortBy(function ($part) use ($selectedParts) {
                    return array_search($part->part_number, $selectedParts);
                })
                ->values();

            if ($parts->count() != $partsCount) {
                return back()->withErrors(['selected_parts' => 'الأجزاء المختارة غير صالحة']);
            }

            // Parts selected - show scoring form
            $selectionMode = false; // Show scoring form
            return view('supervisor.recitation.mafsil', compact('student', 'mafsilType', 'maxScore', 'parts', 'partsCount', 'passingPercentage', 'allParts', 'selectionMode'));
        }

        // No parts selected yet - show selection UI
        $parts = collect(); // Empty collection for now
        $selectionMode = true; // Show part selection UI
        return view('supervisor.recitation.mafsil', compact('student', 'mafsilType', 'maxScore', 'parts', 'partsCount', 'passingPercentage', 'allParts', 'selectionMode'));
    }

    /**
     * Not Recitate Session (لم يسمع)
     */
    public function notRecitate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::with('level')->where('supervisor_id', Auth::id())->findOrFail($request->student_id);
        $parts = QuranPart::orderBy('part_number')->get();

        return view('supervisor.recitation.not-recitate', compact('student', 'parts'));
    }

    /**
     * Save Not Recitate Session
     */
    public function saveNotRecitate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_date' => 'required|date',
        ]);

        $session = RecitationSession::create([
            'student_id' => $request->student_id,
            'supervisor_id' => Auth::id(),
            'type' => 'not_recitate',
            'status' => 'not_recitate',
            'session_date' => $request->session_date,
            'notes' => $request->reason,
        ]);

        $this->notifyParentOnSession($session);
        $this->notifyStudentOnSession($session);

        return redirect()->route('supervisor.recitation.index')
            ->with('success', 'تم تسجيل عدم التسميع بنجاح');
    }

    /**
     * Save Session (API-like endpoint)
     */
    public function saveSession(Request $request)
    {
        try {
            $request->validate(self::SESSION_VALIDATION_RULES);

            $student = Student::where('supervisor_id', Auth::id())->findOrFail($request->student_id);

            $session = DB::transaction(function() use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];

                $maxScore = null;
                $finalScore = null;
                if ($request->type === RecitationSession::TYPE_MAFSIL) {
                    $maxScore = 100;
                    $finalScore = RecitationSession::calculateMafsilScore($maxScore, $totalErrors, $totalHesitations);
                }

                $failedPages = 0;
                foreach ($pages as $page) {
                    $colorResult = RecitationSession::calculatePageColor($page['errors'], $page['hesitations'], $request->type);
                    if (!$colorResult['passed']) {
                        $failedPages++;
                    }
                }

                $sessionStatus = RecitationSession::STATUS_PASSED;
                if ($request->type === RecitationSession::TYPE_MAFSIL) {
                    $passingPercentage = RecitationSession::getMafsilPassingPercentage($request->mafsil_type);
                    if ($finalScore < $passingPercentage) {
                        $sessionStatus = RecitationSession::STATUS_FAILED;
                    }
                } elseif ($request->type === RecitationSession::TYPE_RECITATION && $totalErrors >= 6) {
                    $sessionStatus = RecitationSession::STATUS_FAILED;
                } elseif (in_array($request->type, [RecitationSession::TYPE_NEW_MEMORIZATION, RecitationSession::TYPE_REVIEW, RecitationSession::TYPE_UNMASTERED]) && $failedPages > 0) {
                    $sessionStatus = RecitationSession::STATUS_FAILED;
                }

                if ($request->failed) {
                    $sessionStatus = RecitationSession::STATUS_FAILED;
                }

                return $this->createSessionWithPages($request, $sessionStatus, $totalErrors, $totalHesitations, $maxScore, $finalScore);
            });

            $this->notifyParentOnSession($session);
            $this->notifyStudentOnSession($session);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ جلسة التسميع بنجاح',
                    'session_id' => $session->id
                ]);
            }

            return redirect()->route('supervisor.recitation.index')
                ->with('success', 'تم حفظ جلسة التسميع بنجاح');
        } catch (\Exception $e) {
            \Log::error('Failed to save session: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'student_id' => $request->student_id,
                'pages' => $request->input('pages'),
            ]);
            if ($request->expectsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
                }
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ'], 500);
            }
            return back()->with('error', 'حدث خطأ أثناء الحفظ');
        }
    }

    /**
     * Save Incomplete Session (لم يكمل)
     */
    public function saveIncomplete(Request $request)
    {
        try {
            $request->validate(self::SESSION_VALIDATION_RULES);

            $student = Student::where('supervisor_id', Auth::id())->findOrFail($request->student_id);

            $session = DB::transaction(function() use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];

                $maxScore = $request->type === RecitationSession::TYPE_MAFSIL ? 100 : null;
                $finalScore = $maxScore ? RecitationSession::calculateMafsilScore($maxScore, $totalErrors, $totalHesitations) : null;

                return $this->createSessionWithPages($request, RecitationSession::STATUS_INCOMPLETE, $totalErrors, $totalHesitations, $maxScore, $finalScore);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ الجلسة بنجاح (لم يكمل)',
                    'session_id' => $session->id
                ]);
            }

            return redirect()->route('supervisor.recitation.index')
                ->with('success', 'تم حفظ الجلسة بنجاح (لم يكمل)');
        } catch (\Exception $e) {
            \Log::error('Failed to save incomplete session: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'student_id' => $request->student_id,
                'pages' => $request->input('pages'),
            ]);
            if ($request->expectsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
                }
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ'], 500);
            }
            return back()->with('error', 'حدث خطأ أثناء الحفظ');
        }
    }

    /**
     * Create a recitation session with its pages
     */
    private function createSessionWithPages(Request $request, string $status, int $totalErrors, int $totalHesitations, ?float $maxScore, ?float $finalScore): RecitationSession
    {
        $pages = $request->input('pages');

        $session = RecitationSession::create([
            'student_id' => $request->student_id,
            'supervisor_id' => Auth::id(),
            'type' => $request->type,
            'part_id' => $request->part_id,
            'start_page' => $pages[0]['page_number'] ?? null,
            'end_page' => end($pages)['page_number'] ?? null,
            'total_pages' => count($pages),
            'total_errors' => $totalErrors,
            'total_hesitations' => $totalHesitations,
            'status' => $status,
            'mafsil_type' => $request->mafsil_type ?? null,
            'max_score' => $maxScore,
            'final_score' => $finalScore,
            'unmastered_pages_count' => $request->unmastered_pages_count ?? null,
            'session_date' => $request->session_date,
            'notes' => $request->notes,
        ]);

        foreach ($pages as $pageData) {
            $colorResult = RecitationSession::calculatePageColor($pageData['errors'], $pageData['hesitations'], $request->type);

            $pageScore = null;
            if ($request->type === RecitationSession::TYPE_MAFSIL) {
                $pageScore = RecitationSession::calculateMafsilScore($maxScore, $pageData['errors'], $pageData['hesitations']);
            }

            RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $pageData['page_number'],
                'errors' => $pageData['errors'],
                'hesitations' => $pageData['hesitations'],
                'color_status' => $colorResult['color'],
                'is_passed' => $colorResult['passed'],
                'page_score' => $pageScore,
            ]);
        }

        return $session;
    }

    /**
     * Calculate page color via AJAX
     */
    public function calculateColor(Request $request)
    {
        $request->validate([
            'errors' => 'required|integer|min:0',
            'hesitations' => 'required|integer|min:0',
            'type' => 'nullable|string',
        ]);

        $result = RecitationSession::calculatePageColor($request->errors, $request->hesitations, $request->type);

        return response()->json($result);
    }

    /**
     * Get pages for a part starting from page number
     */
    public function getPages(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:quran_parts,id',
            'start_page' => 'required|integer|min:1',
            'count' => 'required|integer|min:1',
        ]);

        $part = QuranPart::findOrFail($request->part_id);
        $pages = [];

        for ($i = 0; $i < $request->count; $i++) {
            $pageNum = $request->start_page + $i;
            if ($pageNum <= $part->end_page) {
                $pages[] = $pageNum;
            }
        }

        return response()->json(['pages' => $pages, 'part' => $part]);
    }

    /**
     * Save failed continue session - called when supervisor continues after failure
     */
    public function saveFailedContinue(Request $request)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil',
            'part_id' => 'nullable|exists:quran_parts,id',
            'session_date' => 'required|date',
            'pages' => 'required|array',
            'pages.*.page_number' => 'required|integer',
            'pages.*.errors' => 'required|integer|min:0',
            'pages.*.hesitations' => 'required|integer|min:0',
            'failed_continued' => 'nullable|boolean',
            'is_final_submission' => 'nullable|boolean',
        ];

        // For AJAX requests (jQuery sends X-Requested-With: XMLHttpRequest), return JSON errors
        $isAjax = $request->expectsJson() || $request->isXmlHttpRequest();

        if ($isAjax) {
            try {
                $request->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::warning('Recitation saveFailedContinue validation failed', [
                    'student_id' => $request->student_id,
                    'type' => $request->type,
                    'session_date' => $request->session_date,
                    'errors' => $e->errors(),
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Validation failed'
                ], 422);
            }

        } else {
            $request->validate($rules);
        }

        $student = Student::where('supervisor_id', Auth::id())->findOrFail($request->student_id);

        try {
            $session = DB::transaction(function() use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];
                $maxScore = 100;

                // Calculate score
                $finalScore = RecitationSession::calculateMafsilScore($maxScore, $totalErrors, $totalHesitations);

                // For failed_continued, we use 'failed' status but add note indicating continued
                $sessionStatus = RecitationSession::STATUS_FAILED;

                $session = RecitationSession::create([
                    'student_id' => $request->student_id,
                    'supervisor_id' => Auth::id(),
                    'type' => $request->type,
                    'part_id' => $request->part_id,
                    'start_page' => $pages[0]['page_number'] ?? null,
                    'end_page' => end($pages)['page_number'] ?? null,
                    'total_pages' => count($pages),
                    'total_errors' => $totalErrors,
                    'total_hesitations' => $totalHesitations,
                    'status' => $sessionStatus,
                    'mafsil_type' => $request->mafsil_type ?? null,
                    'max_score' => $maxScore,
                    'final_score' => $finalScore,
                    'unmastered_pages_count' => $request->unmastered_pages_count ?? null,
                    'session_date' => $request->session_date,
                    'notes' => ($request->notes ?? '') . ' [راسب ومكمل - أكمل بعد الرسوب]',
                ]);

                // Save each page
                foreach ($pages as $pageData) {
                    $colorResult = RecitationSession::calculatePageColor($pageData['errors'], $pageData['hesitations'], $request->type);

                    $pageScore = null;
                    if ($request->type === RecitationSession::TYPE_MAFSIL) {
                        $pageScore = RecitationSession::calculateMafsilScore($maxScore, $pageData['errors'], $pageData['hesitations']);
                    }

                    RecitationPage::create([
                        'session_id' => $session->id,
                        'page_number' => $pageData['page_number'],
                        'errors' => $pageData['errors'],
                        'hesitations' => $pageData['hesitations'],
                        'color_status' => $colorResult['color'],
                        'is_passed' => $colorResult['passed'],
                        'page_score' => $pageScore,
                    ]);
                }

                return $session;
            });

            $this->notifyParentOnSession($session);
            $this->notifyStudentOnSession($session);

            return response()->json(['success' => true, 'message' => 'تم حفظ سجل الرسوب والإكمال بنجاح']);
        } catch (\Exception $e) {
            \Log::error('Failed to save recitation session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السجل'
            ], 500);
        }
    }

    /**
     * Process additional and repeated pages for a recitation session.
     * Modifies $pages array by reference.
     */
    private function processAdditionalAndRepeatedPages(array &$pages, Request $request): void
    {
        $originalPagesCount = count($pages);

        // Handle additional pages
        if ($request->additional_pages) {
            $additionalPages = json_decode($request->additional_pages, true);
            if (is_array($additionalPages)) {
                foreach ($additionalPages as $additionalPage) {
                    if (isset($additionalPage['page_number'])) {
                        $pages[] = $additionalPage['page_number'];
                    }
                }
            }
        }

        // Handle repeated pages (replace last N pages from ORIGINAL pages only)
        if ($request->repeated_pages) {
            $repeatedPages = json_decode($request->repeated_pages, true);
            if (is_array($repeatedPages) && count($repeatedPages) > 0) {
                $repeatedCount = count($repeatedPages);
                $keepCount = max(0, $originalPagesCount - $repeatedCount);
                $originalPages = array_slice($pages, 0, $keepCount);

                foreach ($repeatedPages as $repeatedPage) {
                    if (isset($repeatedPage['page_number'])) {
                        $originalPages[] = $repeatedPage['page_number'];
                    }
                }

                // Append additional pages at the end
                $additionalCount = count($pages) - $originalPagesCount;
                if ($additionalCount > 0) {
                    $additionalOnly = array_slice($pages, $originalPagesCount);
                    $originalPages = array_merge($originalPages, $additionalOnly);
                }
                $pages = $originalPages;
            }
        }
    }

    /**
     * History - display recitation history
     */
    public function history(Request $request)
    {
        $supervisorId = Auth::id();
        $studentId = $request->student_id;
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        $query = RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', $supervisorId))
            ->with(['student.user', 'part', 'pages'])
            ->whereBetween('session_date', [$fromDate, $toDate]);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $sessions = $query->orderBy('session_date', 'desc')->paginate(30);
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->get();

        return view('supervisor.recitation.history', compact('sessions', 'students', 'fromDate', 'toDate', 'studentId'));
    }

    public function destroy(RecitationSession $recitation)
    {
        // Ensure the recitation belongs to this supervisor
        if ($recitation->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $recitation->delete();

        return redirect()->back()
            ->with('success', 'تم حذف جلسة التسميع بنجاح');
    }

    protected function notifyParentOnSession(RecitationSession $session): void
    {
        $student = $session->student;
        $parent = $student->parents()->first();
        if (!$parent || !$parent->user) {
            return;
        }

        $typeLabels = [
            'new_memorization' => 'حفظ جديد',
            'review' => 'مراجعة',
            'unmastered' => 'غير متقنة',
            'recitation' => 'تلاوة',
            'mafsil' => 'مفصل',
        ];

        $typeLabel = $typeLabels[$session->type] ?? $session->type;
        $scoreText = $session->final_score !== null ? " - الدرجة: {$session->final_score}" : '';
        $statusText = $session->status === RecitationSession::STATUS_PASSED ? 'ناجح' : 'راسب';

        try {
            $notificationService = app(NotificationService::class);
            $notificationService->sendToUser(
                $parent->user,
                'recitation_session',
                "تسميع جديد للطالب {$student->name}",
                "تم تسجيل تلاوة بتاريخ {$session->session_date} من نوع {$typeLabel} بنتيجة {$statusText}{$scoreText}",
                route('parent.sessions.show', $session->id)
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify parent about session {$session->id}: " . $e->getMessage());
        }
    }

    protected function notifyStudentOnSession(RecitationSession $session): void
    {
        $student = $session->student;
        if (!$student->user) {
            return;
        }

        $typeLabels = [
            'new_memorization' => 'حفظ جديد',
            'review' => 'مراجعة',
            'unmastered' => 'غير متقنة',
            'recitation' => 'تلاوة',
            'mafsil' => 'مفصل',
        ];

        $typeLabel = $typeLabels[$session->type] ?? $session->type;
        $scoreText = $session->final_score !== null ? " - الدرجة: {$session->final_score}" : '';
        $statusText = $session->status === RecitationSession::STATUS_PASSED ? 'ناجح' : 'راسب';

        try {
            $notificationService = app(NotificationService::class);
            $notificationService->sendToUser(
                $student->user,
                'recitation_session',
                "تم تسجيل تسميع جديد",
                "تم تسجيل تلاوة بتاريخ {$session->session_date} من نوع {$typeLabel} بنتيجة {$statusText}{$scoreText}",
                route('student.sessions.show', $session->id)
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify student about session {$session->id}: " . $e->getMessage());
        }
    }
}
