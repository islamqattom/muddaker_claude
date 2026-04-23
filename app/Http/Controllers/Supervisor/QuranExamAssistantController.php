<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Surah;
use App\Models\Ayah;
use Illuminate\Http\Request;

class QuranExamAssistantController extends Controller
{
    public function index()
    {
        $surahs = Surah::orderBy('surah_number')->get();
        $students = \App\Models\Student::where('supervisor_id', auth()->id())
            ->with(['user', 'level'])
            ->get()
            ->sortBy(fn($s) => $s->user?->name ?? '');

        return view('supervisor.exam-assistant.index', compact('surahs', 'students'));
    }

    public function generateQuestion(Request $request)
    {
        $validated = $request->validate([
            'juz_list' => 'required|array|min:1',
            'juz_list.*' => 'integer|between:1,30',
        ]);

        $ayah = Ayah::with('surah')
            ->whereIn('juz_number', $validated['juz_list'])
            ->inRandomOrder()
            ->first();

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

}
