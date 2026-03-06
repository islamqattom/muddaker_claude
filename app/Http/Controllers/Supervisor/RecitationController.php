<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\{RecitationSession, RecitationPage, Student, QuranPart, QuranPage};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecitationController extends Controller
{
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
            'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil',
        ]);

        $student = Student::with('level')->findOrFail($request->student_id);
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

        $student = Student::with('level')->findOrFail($request->student_id);
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

        $student = Student::with('level')->findOrFail($request->student_id);
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

        $student = Student::with('level')->findOrFail($request->student_id);
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

        $student = Student::with('level')->findOrFail($request->student_id);
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

        $student = Student::with('level')->findOrFail($request->student_id);
        $mafsilType = $request->mafsil_type;
        $maxScore = 100; // Always use 100
        $passingPercentage = RecitationSession::getMafsilPassingPercentage($mafsilType);
        $partsCount = $mafsilType;

        // Get all 30 parts for selection
        $allParts = QuranPart::orderBy('part_number')->get();

        // Check if parts have been selected (POST request)
        if ($request->isMethod('post') || $request->has('selected_parts')) {
            $selectedParts = $request->input('selected_parts', []);

            // Validate selected parts count
            if (count($selectedParts) != $partsCount) {
                return back()->withErrors(['selected_parts' => 'يجب اختيار exactly ' . $partsCount . ' أجزاء']);
            }

            // Validate selected parts exist
            $parts = QuranPart::whereIn('part_number', $selectedParts)
                ->distinct()
                ->orderByRaw("FIELD(part_number, " . implode(',', $selectedParts) . ")")
                ->get();

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
     * Save Session (API-like endpoint)
     */
    public function saveSession(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil',
            'part_id' => 'nullable|exists:quran_parts,id',
            'session_date' => 'required|date',
            'pages' => 'required|array',
            'pages.*.page_number' => 'required|integer',
            'pages.*.errors' => 'required|integer|min:0',
            'pages.*.hesitations' => 'required|integer|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $pages = $request->input('pages');
            $totalErrors = 0;
            $totalHesitations = 0;
            $sessionStatus = 'passed';
            $finalScore = null;
            $maxScore = null;

            // Calculate totals
            foreach ($pages as $page) {
                // For 'recitation' (سرد) type: 3 hesitations = 1 equivalent error per page
                if ($request->type === 'recitation') {
                    $pageErrors = $page['errors'] + floor($page['hesitations'] / 3);
                    $totalErrors += $pageErrors;
                } else {
                    $totalErrors += $page['errors'];
                }
                $totalHesitations += $page['hesitations'];
            }

            // For mafsil, calculate score (always from 100)
            if ($request->type === 'mafsil') {
                $maxScore = 100;
                $finalScore = RecitationSession::calculateMafsilScore($maxScore, $totalErrors, $totalHesitations);
            }

            // Check overall pass/fail
            $failedPages = 0;
            foreach ($pages as $page) {
                $colorResult = RecitationSession::calculatePageColor($page['errors'], $page['hesitations'], $request->type);
                if (!$colorResult['passed']) {
                    $failedPages++;
                }
            }

            // For mafsil ONLY: check passing percentage
            // - مفصل 3,6: إذا حصل على أقل من 90% → راسب
            // - مفصل 10,15: إذا حصل على أقل من 80% → راسب
            // - مفصل 20,25,30: إذا حصل على أقل من 75% → راسب
            if ($request->type === 'mafsil') {
                $passingPercentage = RecitationSession::getMafsilPassingPercentage($request->mafsil_type);
                if ($finalScore < $passingPercentage) {
                    $sessionStatus = 'failed';
                }
            } elseif ($failedPages > 0 || ($request->type === 'recitation' && $totalErrors >= 6)) {
                $sessionStatus = 'failed';
            }

            if ($request->failed) {
                $sessionStatus = 'failed';
            }

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
                'notes' => $request->notes,
            ]);

            // Save each page
            foreach ($pages as $pageData) {
                $colorResult = RecitationSession::calculatePageColor($pageData['errors'], $pageData['hesitations'], $request->type);

                $pageScore = null;
                if ($request->type === 'mafsil') {
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

        return redirect()->route('supervisor.recitation.index')
            ->with('success', 'تم حفظ جلسة التسميع بنجاح');
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
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:new_memorization,review,unmastered,recitation,mafsil',
            'part_id' => 'nullable|exists:quran_parts,id',
            'session_date' => 'required|date',
            'pages' => 'required|array',
            'pages.*.page_number' => 'required|integer',
            'pages.*.errors' => 'required|integer|min:0',
            'pages.*.hesitations' => 'required|integer|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $pages = $request->input('pages');
            $totalErrors = 0;
            $totalHesitations = 0;
            $maxScore = 100;

            // Calculate totals
            foreach ($pages as $page) {
                $totalErrors += $page['errors'];
                $totalHesitations += $page['hesitations'];
            }

            // Calculate score
            $finalScore = RecitationSession::calculateMafsilScore($maxScore, $totalErrors, $totalHesitations);

            // For failed_continued, we use 'failed' status but add note indicating continued
            $sessionStatus = 'failed';

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
                if ($request->type === 'mafsil') {
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

        return response()->json(['success' => true, 'message' => 'تم حفظ سجل الرسوب والإكمال بنجاح']);
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
            ->with(['student.user', 'part'])
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
        // Ensure the recitation belongs to a student of this supervisor
        if ($recitation->student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $recitation->delete();

        return redirect()->back()
            ->with('success', 'تم حذف جلسة التسميع بنجاح');
    }
}
