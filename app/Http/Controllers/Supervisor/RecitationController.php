<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\MafsilPosition;
use App\Models\QuranPart;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\RecitationTajweedError;
use App\Models\Student;
use App\Models\SupervisorAlert;
use App\Models\TajweedRuleDefinition;
use App\Services\MafsilPositionSelector;
use App\Services\NotificationService;
use App\Services\RecitationAlertService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        'pages.*.is_completed' => 'nullable|boolean',
        'pages.*.mafsil_position_id' => 'nullable|exists:mafsil_positions,id',
        'pages.*.position_range' => 'nullable|string|max:100',
        'pages.*.page_number_in_mushaf' => 'nullable|integer|min:1',
        'pages.*.tajweed_errors' => 'nullable|array',
        'pages.*.tajweed_errors.*.tajweed_rule_id' => 'nullable|integer|exists:tajweed_rule_definitions,id',
        'pages.*.tajweed_errors.*.category' => 'nullable|string|max:100',
        'pages.*.tajweed_errors.*.note' => 'nullable|string|max:500',
        'pages.*.tajweed_errors.*.severity' => 'nullable|integer|min:1|max:5',
        'pages.*.tajweed_errors.*.occurrences' => 'nullable|integer|min:1',
    ];

    private const MAFSIL_MAX_SCORE = 100;

    private function mafsilSelector(): MafsilPositionSelector
    {
        return new MafsilPositionSelector();
    }

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

    /**
     * Calculate final mafsil score as aggregate deduction from max score.
     */
    private function calculateMafsilFinalScore(array $pages, float $maxScore = 100): float
    {
        $totalDeduction = 0;
        foreach ($pages as $page) {
            $errorDeduction = $page['errors'] * 3;
            $hesitationDeduction = floor($page['hesitations'] / 3) + (($page['hesitations'] % 3) * 0.33);
            $totalDeduction += $errorDeduction + $hesitationDeduction;
        }

        return round(max(0, $maxScore - $totalDeduction), 2);
    }

    public function index()
    {
        $supervisorId = Auth::id();
        $students = Student::where('supervisor_id', $supervisorId)->with('user', 'level')->orderedByName()->get();
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
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين '.$part->start_page.' و '.$part->end_page]);
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
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين '.$part->start_page.' و '.$part->end_page]);
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
            return redirect()->back()->withErrors(['page_number' => 'رقم الصفحة يجب أن يكون بين '.$part->start_page.' و '.$part->end_page]);
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

        // Check if parts have been selected (POST request only)
        if ($request->isMethod('post')) {
            $selectedParts = $request->input('selected_parts', []);

            // Validate selected parts count
            if (count($selectedParts) != $partsCount) {
                return back()->withErrors(['selected_parts' => 'يجب اختيار '.$partsCount.' أجزاء']);
            }

            // Ensure all selected parts are integers to prevent SQL injection
            $selectedParts = array_map('intval', $selectedParts);

            // Validate selected parts exist using a safe query (always ascending by part)
            $parts = QuranPart::whereIn('part_number', $selectedParts)
                ->get()
                ->sortBy('part_number')
                ->values();

            if ($parts->count() != $partsCount) {
                return back()->withErrors(['selected_parts' => 'الأجزاء المختارة غير صالحة']);
            }

            $difficultyDistribution = $request->input('difficulty', []);
            $validatedDistribution = $this->validateDifficultyDistribution($selectedParts, $difficultyDistribution);

            if ($validatedDistribution === null) {
                return back()->withErrors(['difficulty' => 'توزيع المواضيع غير صالح. يجب أن يكون المجموع 3 لكل جزء.']);
            }

            $byPart = $this->mafsilSelector()
                ->selectAndRecord($selectedParts, $validatedDistribution, $student->id, Auth::id());

            // Drop older mafsil:* session keys for this student to keep the session small.
            foreach ($request->session()->all() as $k => $v) {
                if (is_string($k) && str_starts_with($k, 'mafsil:')
                    && is_array($v)
                    && (int) ($v['student_id'] ?? 0) === (int) $student->id) {
                    $request->session()->forget($k);
                }
            }

            $sessionKey = 'mafsil:'.bin2hex(random_bytes(8));
            $request->session()->put($sessionKey, [
                'student_id'  => (int) $student->id,
                'mafsil_type' => (int) $mafsilType,
                'created_at'  => now()->toIso8601String(),
                'part_positions' => collect($byPart)->map(function ($positions) {
                    return $positions->map(fn ($p) => [
                        'id'         => (int) $p->id,
                        'difficulty' => (int) ($p->difficulty ?? 0),
                    ])->all();
                })->all(),
            ]);

            return redirect()->route('supervisor.recitation.mafsil.show', [
                'student_id'  => $student->id,
                'mafsil_type' => $mafsilType,
                'k'           => $sessionKey,
            ]);
        }

        // No parts selected yet - show selection UI
        $parts = collect(); // Empty collection for now
        $mafsilPositions = collect();
        $tajweedRules = collect();
        $selectionMode = true; // Show part selection UI

        return view('supervisor.recitation.mafsil', compact('student', 'mafsilType', 'maxScore', 'parts', 'partsCount', 'passingPercentage', 'allParts', 'selectionMode', 'mafsilPositions', 'tajweedRules'));
    }

    /**
     * Render the scoring form for an existing selection. Never calls the selector;
     * uses a stored session key (k=…) to render the same picks across refreshes.
     */
    public function mafsilShow(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'mafsil_type' => 'required|in:3,6,10,15,20,25,30',
            'k'           => 'required|string',
        ]);

        $key = $request->input('k');
        $payload = $request->session()->get($key);

        if (!$payload
            || (int) $payload['student_id']  !== (int) $request->student_id
            || (int) $payload['mafsil_type'] !== (int) $request->mafsil_type) {
            return redirect()
                ->route('supervisor.recitation.mafsil', [
                    'student_id'  => $request->student_id,
                    'mafsil_type' => $request->mafsil_type,
                ])
                ->withErrors(['session' => 'انتهت صلاحية الجلسة. أعد اختيار الأجزاء.']);
        }

        $student = Student::with('level')->where('supervisor_id', Auth::id())
            ->findOrFail($request->student_id);
        $mafsilType = (int) $request->mafsil_type;
        $maxScore = self::MAFSIL_MAX_SCORE;
        $passingPercentage = RecitationSession::getMafsilPassingPercentage($mafsilType);
        $allParts = QuranPart::orderBy('part_number')->get();
        $partsCount = $mafsilType;

        $selectedPartNumbers = array_keys($payload['part_positions']);
        sort($selectedPartNumbers);
        $parts = QuranPart::whereIn('part_number', $selectedPartNumbers)
            ->get()
            ->sortBy('part_number')
            ->values();

        $allIds = collect($payload['part_positions'])->flatten(1)->pluck('id')->all();
        $realIds = array_values(array_filter($allIds, fn ($id) => $id > 0));
        $realPositions = MafsilPosition::whereIn('id', $realIds)
            ->with(['startAyah.surah', 'endAyah.surah'])
            ->get()
            ->keyBy('id');

        $mafsilPositions = collect($payload['part_positions'])->map(
            function (array $rows) use ($realPositions) {
                $positions = [];
                foreach ($rows as $row) {
                    $id = (int) $row['id'];
                    $difficulty = (int) ($row['difficulty'] ?? 0);
                    if ($id > 0 && $realPositions->has($id)) {
                        $positions[] = $realPositions->get($id);
                    } elseif ($id < 0) {
                        $positions[] = $this->rebuildPlaceholder($id, $difficulty);
                    }
                }
                return collect($positions);
            }
        );

        $tajweedRules = TajweedRuleDefinition::orderBy('category')->orderBy('order')
            ->get(['id', 'name', 'category']);

        $selectionMode = false;

        return view('supervisor.recitation.mafsil', compact(
            'student', 'mafsilType', 'maxScore', 'parts',
            'passingPercentage', 'allParts', 'selectionMode',
            'mafsilPositions', 'tajweedRules', 'partsCount'
        ));
    }

    /**
     * Re-create a placeholder MafsilPosition row from its synthetic negative id
     * and the difficulty recorded in the session payload.
     */
    private function rebuildPlaceholder(int $id, int $difficulty): MafsilPosition
    {
        $p = new MafsilPosition();
        $p->id = $id;
        $p->juz_number = (int) (abs($id) / 100);
        $p->position_number = abs($id) % 100;
        $p->list_order = $p->position_number;
        $p->start_ayah_id = null;
        $p->end_ayah_id = null;
        $p->difficulty = $difficulty > 0 ? $difficulty : 2;
        $p->notes = null;
        $p->exists = false;
        return $p;
    }

    /**
     * Validate difficulty distribution: each selected part must have exactly 3 positions.
     */
    private function validateDifficultyDistribution(array $selectedParts, array $difficultyDistribution): ?array
    {
        $validated = [];

        foreach ($selectedParts as $partNumber) {
            $partDistribution = $difficultyDistribution[$partNumber] ?? [];
            $easy = isset($partDistribution[1]) ? (int) $partDistribution[1] : 0;
            $medium = isset($partDistribution[2]) ? (int) $partDistribution[2] : 0;
            $hard = isset($partDistribution[3]) ? (int) $partDistribution[3] : 0;

            if ($easy < 0 || $medium < 0 || $hard < 0) {
                return null;
            }

            if (($easy + $medium + $hard) !== 3) {
                return null;
            }

            $validated[$partNumber] = [
                1 => $easy,
                2 => $medium,
                3 => $hard,
            ];
        }

        return $validated;
    }

    /**
     * Get ayahs for a mafsil position (Uthmani script).
     */
    public function getMafsilPositionAyahs(MafsilPosition $mafsilPosition)
    {
        $start = $mafsilPosition->startAyah;
        $end = $mafsilPosition->endAyah;

        if (! $start || ! $end) {
            return response()->json(['html' => '<p class="text-muted text-center">لا توجد آيات مرتبطة بهذا الموضع</p>']);
        }

        $ayahs = Ayah::with('surah')
            ->where('id', '>=', $start->id)
            ->where('id', '<=', $end->id)
            ->orderBy('id')
            ->get();

        $html = '<div class="quran-page" dir="rtl" style="font-family: Amiri, serif; font-size: 1.6rem; line-height: 2.4; text-align: justify; padding: 1rem;">';

        $currentSurah = null;
        foreach ($ayahs as $ayah) {
            if ($currentSurah !== $ayah->surah_id) {
                if ($currentSurah !== null) {
                    $html .= '</div>';
                }
                $html .= '<div class="surah-block" style="margin-bottom: 1.5rem;">';
                $html .= '<div class="surah-name" style="font-size: 1.4rem; font-weight: bold; margin-bottom: 0.75rem; color: #182B48; text-align: center;">'.e($ayah->surah->name_ar).'</div>';
                $currentSurah = $ayah->surah_id;
            }

            $html .= '<span class="ayah-text" data-ayah-id="'.$ayah->id.'">';
            $html .= e($ayah->text).' ﴿'.$this->arabicNumeral($ayah->ayah_number).'﴾ ';
            $html .= '</span>';
        }

        if ($currentSurah !== null) {
            $html .= '</div>';
        }

        $html .= '</div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Convert number to Arabic numeral string.
     */
    private function arabicNumeral(int $num): string
    {
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return implode('', array_map(fn ($d) => $arabic[(int) $d], str_split($num)));
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

            $session = DB::transaction(function () use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];

                $maxScore = null;
                $finalScore = null;
                if ($request->type === RecitationSession::TYPE_MAFSIL) {
                    $maxScore = 100;
                    $finalScore = $this->calculateMafsilFinalScore($pages, $maxScore);
                }

                $failedPages = 0;
                $notCompletedPages = 0;
                foreach ($pages as $page) {
                    if (! $this->isPageCompleted($page)) {
                        $notCompletedPages++;
                        $failedPages++;
                        continue;
                    }

                    $colorResult = RecitationSession::calculatePageColor($page['errors'], $page['hesitations'], $request->type);
                    if (! $colorResult['passed']) {
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

                // A session with any not-completed page is "لم يكمل".
                if ($notCompletedPages > 0) {
                    $sessionStatus = RecitationSession::STATUS_INCOMPLETE;
                }

                return $this->createSessionWithPages($request, $sessionStatus, $totalErrors, $totalHesitations, $maxScore, $finalScore);
            });

            $this->notifyParentOnSession($session);
            $this->notifyStudentOnSession($session);

            $this->removeAutoNotRecite($session);

            $alertService = app(RecitationAlertService::class);
            $alertService->alertCompletedMemorization($session);
            $mafsilAlerts = $alertService->alertMafsilStations($session);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ جلسة التسميع بنجاح',
                    'session_id' => $session->id,
                    'mafsil_alerts' => $mafsilAlerts->map(fn ($alert) => [
                        'id' => $alert->id,
                        'station' => $alert->station,
                    ])->values(),
                    'mafsil_alerts_count' => $mafsilAlerts->isNotEmpty()
                        ? $alertService->recitedPartsCount($session->student)
                        : 0,
                    'mafsil_alerts_student' => $session->student->name ?? '',
                ]);
            }

            return redirect()->route('supervisor.recitation.index')
                ->with('success', 'تم حفظ جلسة التسميع بنجاح');
        } catch (\Exception $e) {
            \Log::error('Failed to save session: '.$e->getMessage(), [
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

            $session = DB::transaction(function () use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];

                $maxScore = $request->type === RecitationSession::TYPE_MAFSIL ? 100 : null;
                $finalScore = $maxScore ? $this->calculateMafsilFinalScore($pages, $maxScore) : null;

                return $this->createSessionWithPages($request, RecitationSession::STATUS_INCOMPLETE, $totalErrors, $totalHesitations, $maxScore, $finalScore, true);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ الجلسة بنجاح (لم يكمل)',
                    'session_id' => $session->id,
                ]);
            }

            return redirect()->route('supervisor.recitation.index')
                ->with('success', 'تم حفظ الجلسة بنجاح (لم يكمل)');
        } catch (\Exception $e) {
            \Log::error('Failed to save incomplete session: '.$e->getMessage(), [
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
    private function createSessionWithPages(Request $request, string $status, int $totalErrors, int $totalHesitations, ?float $maxScore, ?float $finalScore, bool $allPagesIncomplete = false): RecitationSession
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
            $isCompleted = $allPagesIncomplete ? false : $this->isPageCompleted($pageData);

            $errors = $isCompleted ? (int) $pageData['errors'] : 0;
            $hesitations = $isCompleted ? (int) $pageData['hesitations'] : 0;

            $colorResult = RecitationSession::calculatePageColor($errors, $hesitations, $request->type);

            $pageScore = null;
            if ($request->type === RecitationSession::TYPE_MAFSIL) {
                $pageScore = RecitationSession::calculateMafsilScore($maxScore, $errors, $hesitations);
            }

            $page = RecitationPage::create([
                'session_id' => $session->id,
                'page_number' => $pageData['page_number'],
                'mafsil_position_id' => $pageData['mafsil_position_id'] ?? null,
                'position' => $pageData['position'] ?? null,
                'position_range' => $pageData['position_range'] ?? null,
                'page_number_in_mushaf' => $pageData['page_number_in_mushaf'] ?? null,
                'errors' => $errors,
                'hesitations' => $hesitations,
                'color_status' => $isCompleted ? $colorResult['color'] : 'black',
                'is_passed' => $isCompleted ? $colorResult['passed'] : false,
                'is_completed' => $isCompleted,
                'page_score' => $pageScore,
            ]);

            $this->saveTajweedErrorsForPage($page, $pageData['tajweed_errors'] ?? []);
        }

        return $session;
    }

    /**
     * Whether a submitted page record counts as completed. Missing values
     * default to completed (backwards compatible).
     */
    private function isPageCompleted(array $pageData): bool
    {
        if (! array_key_exists('is_completed', $pageData) || $pageData['is_completed'] === null) {
            return true;
        }

        return filter_var($pageData['is_completed'], FILTER_VALIDATE_BOOLEAN);
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
            'pages.*.is_completed' => 'nullable|boolean',
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
                    'message' => 'Validation failed',
                ], 422);
            }

        } else {
            $request->validate($rules);
        }

        $student = Student::where('supervisor_id', Auth::id())->findOrFail($request->student_id);

        try {
            $session = DB::transaction(function () use ($request) {
                $pages = $request->input('pages');
                $totals = $this->calculateTotals($pages, $request->type);
                $totalErrors = $totals['errors'];
                $totalHesitations = $totals['hesitations'];
                $maxScore = 100;

                // Calculate score
                $finalScore = $this->calculateMafsilFinalScore($pages, $maxScore);

                // For failed_continued, we use 'failed' status but add note indicating continued
                $sessionStatus = RecitationSession::STATUS_FAILED;

                $session = RecitationSession::create([
                    'student_id' => $request->student_id,
                    'supervisor_id' => Auth::id(),
                    'type' => $request->type,
                    'part_id' => $request->part_id,
                    'start_page' => $pages[0]['page_number'] ?? null,
                    'end_page' => end($pages)['page_number'] ?? null,
                    'total_pages' => $request->type === RecitationSession::TYPE_MAFSIL
                        ? count(array_unique(array_column($pages, 'page_number')))
                        : count($pages),
                    'total_errors' => $totalErrors,
                    'total_hesitations' => $totalHesitations,
                    'status' => $sessionStatus,
                    'mafsil_type' => $request->mafsil_type ?? null,
                    'max_score' => $maxScore,
                    'final_score' => $finalScore,
                    'unmastered_pages_count' => $request->unmastered_pages_count ?? null,
                    'session_date' => $request->session_date,
                    'notes' => ($request->notes ?? '').' [راسب ومكمل - أكمل بعد الرسوب]',
                ]);

                // Save each page
                foreach ($pages as $pageData) {
                    $isCompleted = $this->isPageCompleted($pageData);
                    $errors = $isCompleted ? (int) $pageData['errors'] : 0;
                    $hesitations = $isCompleted ? (int) $pageData['hesitations'] : 0;

                    $colorResult = RecitationSession::calculatePageColor($errors, $hesitations, $request->type);

                    $pageScore = null;
                    if ($request->type === RecitationSession::TYPE_MAFSIL) {
                        $pageScore = RecitationSession::calculateMafsilScore($maxScore, $errors, $hesitations);
                    }

                    RecitationPage::create([
                        'session_id' => $session->id,
                        'page_number' => $pageData['page_number'],
                        'mafsil_position_id' => $pageData['mafsil_position_id'] ?? null,
                        'position' => $pageData['position'] ?? null,
                        'position_range' => $pageData['position_range'] ?? null,
                        'page_number_in_mushaf' => $pageData['page_number_in_mushaf'] ?? null,
                        'errors' => $errors,
                        'hesitations' => $hesitations,
                        'color_status' => $isCompleted ? $colorResult['color'] : 'black',
                        'is_passed' => $isCompleted ? $colorResult['passed'] : false,
                        'is_completed' => $isCompleted,
                        'page_score' => $pageScore,
                    ]);
                }

                return $session;
            });

            $this->notifyParentOnSession($session);
            $this->notifyStudentOnSession($session);

            $this->removeAutoNotRecite($session);

            return response()->json(['success' => true, 'message' => 'تم حفظ سجل الرسوب والإكمال بنجاح']);
        } catch (\Exception $e) {
            \Log::error('Failed to save recitation session: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السجل',
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

        // Handle repeated pages: the repeated pages appear first, followed by
        // the remaining session pages (keeping the same total count), then any
        // additional pages.
        if ($request->repeated_pages) {
            $repeatedPages = json_decode($request->repeated_pages, true);
            if (is_array($repeatedPages) && count($repeatedPages) > 0) {
                $repeatedPageNumbers = [];
                foreach ($repeatedPages as $repeatedPage) {
                    if (isset($repeatedPage['page_number'])) {
                        $repeatedPageNumbers[] = $repeatedPage['page_number'];
                    }
                }

                if (! empty($repeatedPageNumbers)) {
                    $keepCount = max(0, $originalPagesCount - count($repeatedPageNumbers));
                    $keptOriginals = array_slice($pages, 0, $keepCount);

                    // Additional pages were appended after the original pages.
                    $additionalCount = count($pages) - $originalPagesCount;
                    $additionalOnly = $additionalCount > 0 ? array_slice($pages, $originalPagesCount) : [];

                    $pages = array_merge($repeatedPageNumbers, $keptOriginals, $additionalOnly);
                }
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

        $query = RecitationSession::whereHas('student', fn ($q) => $q->where('supervisor_id', $supervisorId))
            ->with(['student.user', 'part', 'pages.mafsilPosition'])
            ->whereBetween('session_date', [$fromDate, $toDate]);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $sessions = $query->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(30);
        $students = Student::where('supervisor_id', $supervisorId)->with('user')->orderedByName()->get();

        return view('supervisor.recitation.history', compact('sessions', 'students', 'fromDate', 'toDate', 'studentId'));
    }

    public function destroy(RecitationSession $recitation)
    {
        // Ensure the recitation belongs to this supervisor
        if ($recitation->supervisor_id !== Auth::id()) {
            abort(403);
        }

        SupervisorAlert::where('recitation_session_id', $recitation->id)->delete();

        $recitation->delete();

        return redirect()->back()
            ->with('success', 'تم حذف جلسة التسميع بنجاح');
    }

    /**
     * Remove an automatic "لم يسمع" session once a real session has been
     * recorded for the same student and day. Manual sessions are never touched.
     */
    private function removeAutoNotRecite(RecitationSession $session): void
    {
        if ($session->type === RecitationSession::TYPE_NOT_RECITE) {
            return;
        }

        RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('student_id', $session->student_id)
            ->whereDate('session_date', $session->session_date)
            ->where('type', RecitationSession::TYPE_NOT_RECITE)
            ->where('auto_generated', true)
            ->delete();
    }

    protected function notifyParentOnSession(RecitationSession $session): void
    {
        $student = $session->student;
        $parent = $student->parents()->first();
        if (! $parent || ! $parent->user) {
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
            \Log::error("Failed to notify parent about session {$session->id}: ".$e->getMessage());
        }
    }

    protected function notifyStudentOnSession(RecitationSession $session): void
    {
        $student = $session->student;
        if (! $student->user) {
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
                'تم تسجيل تسميع جديد',
                "تم تسجيل تلاوة بتاريخ {$session->session_date} من نوع {$typeLabel} بنتيجة {$statusText}{$scoreText}",
                route('student.sessions.show', $session->id)
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify student about session {$session->id}: ".$e->getMessage());
        }
    }

    /**
     * Return tajweed rules grouped by category for use in scoring forms.
     */
    public function tajweedRules(Request $request)
    {
        $rules = TajweedRuleDefinition::orderBy('category')->orderBy('order')
            ->get(['id', 'name', 'category', 'description']);

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    /**
     * Persist attached tajweed errors for a single recitation page.
     */
    private function saveTajweedErrorsForPage(RecitationPage $page, array $tajweedErrors): void
    {
        foreach ($tajweedErrors as $err) {
            if (empty($err['tajweed_rule_id']) && empty($err['category']) && empty($err['note'])) {
                continue;
            }

            RecitationTajweedError::create([
                'recitation_page_id' => $page->id,
                'tajweed_rule_id' => $err['tajweed_rule_id'] ?? null,
                'category' => $err['category'] ?? null,
                'note' => $err['note'] ?? null,
                'severity' => isset($err['severity']) ? (int) $err['severity'] : 1,
                'occurrences' => isset($err['occurrences']) ? (int) $err['occurrences'] : 1,
                'source' => 'supervisor',
            ]);
        }
    }
}
