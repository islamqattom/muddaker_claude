<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\MafsilPosition;
use App\Models\QuranPart;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\RecitationTajweedError;
use App\Models\Student;
use App\Models\TajweedRuleDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MafsilController extends Controller
{
    private const SESSION_VALIDATION_RULES = [
        'student_name' => 'required|string|max:255',
        'mahd_name' => 'nullable|string|max:255',
        'student_id' => 'nullable|exists:students,id',
        'type' => 'required|in:mafsil',
        'mafsil_type' => 'required|integer|min:1|max:30',
        'error_score' => 'nullable|numeric|min:0|max:100',
        'hesitation_score' => 'nullable|numeric|min:0|max:100',
        'fail_score' => 'nullable|numeric|min:0|max:100',
        'session_date' => 'required|date',
        'pages' => 'required|array',
        'pages.*.page_number' => 'required|integer',
        'pages.*.errors' => 'required|integer|min:0',
        'pages.*.hesitations' => 'required|integer|min:0',
        'pages.*.mafsil_position_id' => 'nullable|integer',
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

    private function calculateMafsilFinalScore(array $pages, float $maxScore = 100, float $errorScore = 3.0, float $hesitationScore = 0.33): float
    {
        $totalDeduction = 0;
        foreach ($pages as $page) {
            $errorDeduction = $page['errors'] * $errorScore;
            $hesitationDeduction = floor($page['hesitations'] / 3) + (($page['hesitations'] % 3) * $hesitationScore);
            $totalDeduction += $errorDeduction + $hesitationDeduction;
        }

        return round(max(0, $maxScore - $totalDeduction), 2);
    }

    public function index(Request $request)
    {
        if ($request->isMethod('post') && ! $request->has('selected_parts')) {
            $request->validate([
                'student_name' => 'required|string|max:255',
                'mahd_name' => 'nullable|string|max:255',
                'mafsil_type' => 'required|integer|min:1|max:30',
                'positions_per_part' => 'required|integer|min:1|max:10',
                'error_score' => 'nullable|numeric|min:0|max:100',
                'hesitation_score' => 'nullable|numeric|min:0|max:100',
                'fail_score' => 'nullable|numeric|min:0|max:100',
                'session_date' => 'nullable|date',
            ]);

            return redirect()->route('public.recitation.mafsil', [
                'student_name' => $request->student_name,
                'mahd_name' => $request->mahd_name,
                'mafsil_type' => $request->mafsil_type,
                'positions_per_part' => $request->positions_per_part,
                'error_score' => $request->error_score ?? 3,
                'hesitation_score' => $request->hesitation_score ?? 0.33,
                'fail_score' => $request->fail_score ?? 60,
                'session_date' => $request->session_date ?: date('Y-m-d'),
            ]);
        }

        $studentName = trim((string) $request->query('student_name', $request->input('student_name', '')));
        $mahdName = trim((string) $request->query('mahd_name', $request->input('mahd_name', '')));
        $mafsilType = (int) $request->query('mafsil_type', $request->input('mafsil_type', 0));
        $positionsPerPart = (int) $request->query('positions_per_part', $request->input('positions_per_part', 3));
        $positionsPerPart = max(1, min(10, $positionsPerPart));
        $sessionDate = $request->query('session_date', $request->input('session_date', date('Y-m-d')));
        $errorScore = (float) $request->query('error_score', $request->input('error_score', 3));
        $hesitationScore = (float) $request->query('hesitation_score', $request->input('hesitation_score', 0.33));
        $failScore = (float) $request->query('fail_score', $request->input('fail_score', 60));

        if ($studentName === '' || $mafsilType < 1 || $mafsilType > 30) {
            return view('guest.mafsil-form', [
                'today' => date('Y-m-d'),
                'positionsPerPart' => old('positions_per_part', 3),
            ]);
        }

        $student = (object) [
            'id' => null,
            'user' => (object) ['name' => $studentName],
            'level' => null,
        ];

        $maxScore = self::MAFSIL_MAX_SCORE;
        $passingPercentage = $failScore > 0 ? $failScore : RecitationSession::getMafsilPassingPercentage($mafsilType);
        $partsCount = $mafsilType;

        $allParts = QuranPart::orderBy('part_number')->get();

        if ($request->isMethod('post') || $request->has('selected_parts')) {
            $request->validate([
                'student_name' => 'required|string|max:255',
                'mahd_name' => 'nullable|string|max:255',
                'mafsil_type' => 'required|integer|min:1|max:30',
                'positions_per_part' => 'required|integer|min:1|max:10',
                'error_score' => 'nullable|numeric|min:0|max:100',
                'hesitation_score' => 'nullable|numeric|min:0|max:100',
                'fail_score' => 'nullable|numeric|min:0|max:100',
                'session_date' => 'nullable|date',
                'selected_parts' => 'required|array',
                'selected_parts.*' => 'integer|min:1|max:30',
            ]);

            $selectedParts = $request->input('selected_parts', []);

            if (count($selectedParts) != $partsCount) {
                return back()->withErrors(['selected_parts' => 'يجب اختيار '.$partsCount.' أجزاء'])->withInput();
            }

            $selectedParts = array_map('intval', $selectedParts);

            $parts = QuranPart::whereIn('part_number', $selectedParts)
                ->get()
                ->sortBy(function ($part) use ($selectedParts) {
                    return array_search($part->part_number, $selectedParts);
                })
                ->values();

            if ($parts->count() != $partsCount) {
                return back()->withErrors(['selected_parts' => 'الأجزاء المختارة غير صالحة'])->withInput();
            }

            $difficultyDistribution = $request->input('difficulty', []);
            $validatedDistribution = $this->validateDifficultyDistribution($selectedParts, $difficultyDistribution, $positionsPerPart);

            if ($validatedDistribution === null) {
                return back()->withErrors(['difficulty' => 'توزيع المواضيع غير صالح. يجب أن يكون المجموع '.$positionsPerPart.' لكل جزء.'])->withInput();
            }

            $mafsilPositions = $this->selectRandomMafsilPositions($selectedParts, $validatedDistribution, $positionsPerPart);
            $tajweedRules = TajweedRuleDefinition::orderBy('category')->orderBy('order')
                ->get(['id', 'name', 'category']);

            $selectionMode = false;

            return view('guest.mafsil', compact(
                'student', 'studentName', 'mahdName', 'mafsilType', 'maxScore', 'parts', 'partsCount',
                'passingPercentage', 'allParts', 'selectionMode', 'mafsilPositions',
                'tajweedRules', 'sessionDate', 'errorScore', 'hesitationScore', 'failScore', 'positionsPerPart'
            ));
        }

        $parts = collect();
        $mafsilPositions = collect();
        $tajweedRules = collect();
        $selectionMode = true;

        return view('guest.mafsil', compact(
            'student', 'studentName', 'mahdName', 'mafsilType', 'maxScore', 'parts', 'partsCount',
            'passingPercentage', 'allParts', 'selectionMode', 'mafsilPositions',
            'tajweedRules', 'sessionDate', 'errorScore', 'hesitationScore', 'failScore', 'positionsPerPart'
        ));
    }

    private function validateDifficultyDistribution(array $selectedParts, array $difficultyDistribution, int $positionsPerPart = 3): ?array
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

            if (($easy + $medium + $hard) !== $positionsPerPart) {
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

    private function selectRandomMafsilPositions(array $selectedParts, array $validatedDistribution, int $positionsPerPart = 3): \Illuminate\Support\Collection
    {
        $allPositions = MafsilPosition::with(['startAyah.surah', 'endAyah.surah'])
            ->whereIn('juz_number', $selectedParts)
            ->get()
            ->groupBy(['juz_number', 'difficulty']);

        $selectedPositions = collect();

        foreach ($selectedParts as $partNumber) {
            $distribution = $validatedDistribution[$partNumber];
            $partSelected = collect();

            foreach ([1, 2, 3] as $difficulty) {
                $needed = $distribution[$difficulty];
                if ($needed === 0) {
                    continue;
                }

                $available = $allPositions->get($partNumber, collect())->get($difficulty, collect());

                if ($available->count() < $needed) {
                    $partSelected = $partSelected->merge(
                        $this->buildPlaceholderPositions($partNumber, $needed, $difficulty, count($partSelected))
                    );
                    continue;
                }

                $shuffled = $available->shuffle();
                $partSelected = $partSelected->merge($shuffled->take($needed));
            }

            if ($partSelected->isEmpty()) {
                $partSelected = $this->buildPlaceholderPositions($partNumber, $positionsPerPart, 0, 0);
            }

            $selectedPositions = $selectedPositions->merge(
                $partSelected->shuffle()->sortBy('list_order')->values()
            );
        }

        return $selectedPositions->groupBy('juz_number');
    }

    private function buildPlaceholderPositions(int $partNumber, int $count, int $difficulty, int $startOrder): \Illuminate\Support\Collection
    {
        $placeholders = collect();
        for ($i = 0; $i < $count; $i++) {
            $position = new MafsilPosition;
            $position->id = -1 * (($partNumber * 100) + $startOrder + $i + 1);
            $position->juz_number = $partNumber;
            $position->position_number = $startOrder + $i + 1;
            $position->list_order = $startOrder + $i + 1;
            $position->start_ayah_id = null;
            $position->end_ayah_id = null;
            $position->difficulty = $difficulty > 0 ? $difficulty : 2;
            $position->notes = null;
            $position->exists = false;
            $placeholders->push($position);
        }

        return $placeholders;
    }

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

    public function tajweedRules(Request $request)
    {
        $rules = TajweedRuleDefinition::orderBy('category')->orderBy('order')
            ->get(['id', 'name', 'category', 'description']);

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    private function arabicNumeral(int $num): string
    {
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return implode('', array_map(fn ($d) => $arabic[(int) $d], str_split($num)));
    }

    public function saveSession(Request $request)
    {
        try {
            $request->validate(self::SESSION_VALIDATION_RULES);

            $student = null;
            if ($request->student_id) {
                $student = Student::find($request->student_id);
            }

            $errorScore = (float) ($request->error_score ?? 3);
            $hesitationScore = (float) ($request->hesitation_score ?? 0.33);
            $failScore = (float) ($request->fail_score ?? 60);

            $session = DB::transaction(function () use ($request, $student, $errorScore, $hesitationScore, $failScore) {
                $pages = $request->input('pages');
                $maxScore = 100;
                $finalScore = $this->calculateMafsilFinalScore($pages, $maxScore, $errorScore, $hesitationScore);
                $passingPercentage = $failScore > 0 ? $failScore : RecitationSession::getMafsilPassingPercentage($request->mafsil_type);
                $sessionStatus = $finalScore < $passingPercentage
                    ? RecitationSession::STATUS_FAILED
                    : RecitationSession::STATUS_PASSED;

                if ($request->failed) {
                    $sessionStatus = RecitationSession::STATUS_FAILED;
                }

                $session = RecitationSession::create([
                    'student_id' => $student?->id,
                    'student_name' => $request->student_name,
                    'mahd_name' => $request->mahd_name,
                    'supervisor_id' => null,
                    'type' => RecitationSession::TYPE_MAFSIL,
                    'part_id' => $request->part_id,
                    'start_page' => $pages[0]['page_number'] ?? null,
                    'end_page' => end($pages)['page_number'] ?? null,
                    'total_pages' => count(array_unique(array_column($pages, 'page_number'))),
                    'total_errors' => array_sum(array_column($pages, 'errors')),
                    'total_hesitations' => array_sum(array_column($pages, 'hesitations')),
                    'status' => $sessionStatus,
                    'mafsil_type' => $request->mafsil_type,
                    'max_score' => $maxScore,
                    'final_score' => $finalScore,
                    'error_score' => $errorScore,
                    'hesitation_score' => $hesitationScore,
                    'fail_score' => $passingPercentage,
                    'session_date' => $request->session_date,
                    'notes' => $request->notes,
                ]);

                foreach ($pages as $pageData) {
                    $colorResult = RecitationSession::calculatePageColor(
                        $pageData['errors'],
                        $pageData['hesitations'],
                        RecitationSession::TYPE_MAFSIL
                    );
                    $pageScore = RecitationSession::calculateMafsilScore(
                        $maxScore,
                        $pageData['errors'],
                        $pageData['hesitations']
                    );

                    $page = RecitationPage::create([
                        'session_id' => $session->id,
                        'page_number' => $pageData['page_number'],
                        'mafsil_position_id' => ! empty($pageData['mafsil_position_id']) ? $pageData['mafsil_position_id'] : null,
                        'position' => $pageData['position'] ?? null,
                        'position_range' => $pageData['position_range'] ?? null,
                        'page_number_in_mushaf' => $pageData['page_number_in_mushaf'] ?? null,
                        'errors' => $pageData['errors'],
                        'hesitations' => $pageData['hesitations'],
                        'color_status' => $colorResult['color'],
                        'is_passed' => $colorResult['passed'],
                        'page_score' => $pageScore,
                    ]);

                    $this->saveTajweedErrorsForPage($page, $pageData['tajweed_errors'] ?? []);
                }

                return $session;
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حفظ جلسة المفصل بنجاح',
                    'session_id' => $session->id,
                    'final_score' => $session->final_score,
                    'status' => $session->status,
                ]);
            }

            return redirect()->route('public.recitation.mafsil.result', ['sessionId' => $session->id])
                ->with('success', 'تم حفظ جلسة المفصل بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'بيانات غير صالحة', 'errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Public mafsil save failed: '.$e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ'], 500);
            }

            return back()->with('error', 'حدث خطأ أثناء الحفظ');
        }
    }

    public function result(int $sessionId)
    {
        $session = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->with(['student.user', 'pages'])
            ->findOrFail($sessionId);

        return view('guest.mafsil-result', [
            'session' => $session,
        ]);
    }

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
                'source' => 'public',
            ]);
        }
    }
}
