<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\QuranApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuranReaderController extends Controller
{
    private QuranApiService $quranApi;

    public function __construct(QuranApiService $quranApi)
    {
        $this->quranApi = $quranApi;
    }

    public function page(int $page)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        if ($page < 1 || $page > 604) {
            $page = 1;
        }

        try {
            $pageData = $this->quranApi->getPage($page);
            $audioUrls = $this->quranApi->getAudioUrls($page);
            $juzOptions = $this->quranApi->getJuzOptions();
            $currentJuz = $this->quranApi->getJuzForPage($page);
            $reciters = $this->quranApi->getAvailableReciters();
        } catch (\Exception $e) {
            return back()->with('error', 'فشل في تحميل الصفحة: ' . $e->getMessage());
        }

        return view('student.quran.reader', [
            'student' => $student,
            'currentPage' => $page,
            'pageData' => $pageData,
            'audioUrls' => $audioUrls,
            'juzOptions' => $juzOptions,
            'currentJuz' => $currentJuz,
            'reciters' => $reciters,
        ]);
    }

    public function part(int $part)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        if ($part < 1 || $part > 30) {
            $part = 1;
        }

        $pages = $this->quranApi->getPartPages($part);
        $firstPage = $pages[0] ?? 1;

        return redirect()->route('student.quran.page', ['page' => $firstPage]);
    }

    public function apiGetPage(int $page)
    {
        try {
            $pageData = $this->quranApi->getPage($page);
            $audioUrls = $this->quranApi->getAudioUrls($page);

            return response()->json([
                'success' => true,
                'data' => $pageData,
                'audio_urls' => $audioUrls,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetAudio(int $page)
    {
        try {
            $audioUrls = $this->quranApi->getAudioUrls($page);

            return response()->json([
                'success' => true,
                'audio_urls' => $audioUrls,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiSearch(Request $request)
    {
        $query = $request->input('query', '');

        if (strlen($query) < 3) {
            return response()->json([
                'success' => false,
                'error' => 'البحث يتطلب 3 أحرف على الأقل',
            ], 400);
        }

        try {
            $results = $this->quranApi->searchVerse($query);

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetJuzOptions()
    {
        return response()->json([
            'success' => true,
            'juz' => $this->quranApi->getJuzOptions(),
        ]);
    }
}