<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Surah;
use App\Models\Ayah;
use Illuminate\Http\Request;

class QuranExamAssistantController extends Controller
{
    private const SCOPE_JUZ = 'juz';
    private const SCOPE_SURAH = 'surah';
    private const SCOPE_PAGE = 'page';

    public function index()
    {
        $surahs = Surah::orderBy('surah_number')->get();
        $students = \App\Models\Student::where('supervisor_id', auth()->id())
            ->with(['user', 'level'])
            ->get()
            ->sortBy(fn($s) => $s->user->name ?? '');

        return view('supervisor.exam-assistant.index', compact('surahs', 'students'));
    }

    public function generateQuestion(Request $request)
    {
        $validated = $request->validate([
            'scope_type' => 'required|in:juz,surah,page',
            'start_juz' => 'required_if:scope_type,juz|integer|between:1,30',
            'end_juz' => 'required_if:scope_type,juz|integer|between:1,30|gte:start_juz',
            'surah_id' => 'required_if:scope_type,surah|exists:surahs,id',
            'start_page' => 'required_if:scope_type,page|integer|between:1,604',
            'end_page' => 'required_if:scope_type,page|integer|between:1,604|gte:start_page',
        ]);

        $ayah = $this->getRandomAyah($validated);

        if (!$ayah) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على آيات في النطاق المحدد',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'surah_id' => $ayah->surah_id,
            'ayah_id' => $ayah->id,
            'ayah' => [
                'id' => $ayah->id,
                'text' => $ayah->text,
                'surah_name' => $ayah->surah->name_ar,
                'ayah_number' => $ayah->ayah_number,
                'surah_number' => $ayah->surah->surah_number,
                'page_number' => $ayah->page_number,
                'juz_number' => $ayah->juz_number,
            ],
            'reference' => "سورة {$ayah->surah->name_ar} - آية {$ayah->ayah_number} - صفحة {$ayah->page_number}",
            'page_html' => $ayah->page_html,
        ]);
    }

    private function getRandomAyah(array $filters)
    {
        $query = Ayah::with('surah');

        match ($filters['scope_type']) {
            self::SCOPE_JUZ => $query->whereBetween('juz_number', [$filters['start_juz'], $filters['end_juz']]),
            self::SCOPE_SURAH => $query->where('surah_id', $filters['surah_id']),
            self::SCOPE_PAGE => $query->whereBetween('page_number', [$filters['start_page'], $filters['end_page']]),
        };

        return $query->inRandomOrder()->first();
    }
}
