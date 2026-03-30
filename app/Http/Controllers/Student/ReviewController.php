<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReviewItem;
use App\Models\RecitationReview;
use App\Services\SM2Service;
use App\Services\QuranApiService;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReviewController extends Controller
{
    public function __construct(
        private SM2Service $sm2Service,
        private QuranApiService $quranApiService
    ) {}

    public function index()
    {
        $student = $this->getStudent();

        // Get all review items grouped by urgency
        $reviewItems = $student->reviewItems()
            ->orderByRaw("COALESCE(next_review_date, '1970-01-01') ASC")
            ->get()
            ->groupBy(function ($item) {
                return $item->urgency;
            });

        $stats = [
            'total' => $student->reviewItems()->count(),
            'due' => $reviewItems->get('overdue', collect())->count() + $reviewItems->get('due_today', collect())->count(),
            'upcoming' => $reviewItems->get('upcoming', collect())->count(),
            'future' => $reviewItems->get('future', collect())->count(),
        ];

        return view('student.reviews.index', [
            'overdueItems' => $reviewItems->get('overdue', collect()),
            'todayItems' => $reviewItems->get('due_today', collect()),
            'upcomingItems' => $reviewItems->get('upcoming', collect()),
            'futureItems' => $reviewItems->get('future', collect()),
            'stats' => $stats,
        ]);
    }

    public function session()
    {
        $student = $this->getStudent();

        // Get next due review item
        $reviewItem = $student->reviewItems()
            ->where(function ($query) {
                $query->whereNull('next_review_date')
                    ->orWhere('next_review_date', '<=', Carbon::today());
            })
            ->orderBy('next_review_date')
            ->first();

        if (!$reviewItem) {
            return redirect()->route('student.reviews.index')
                ->with('info', 'لا توجد مراجعات مستحقة حالياً');
        }

        // Get verse text from Quran API
        try {
            if ($reviewItem->verse_start == $reviewItem->verse_end) {
                $verseData = $this->quranApiService->getVerse($reviewItem->surah, $reviewItem->verse_start);
            } else {
                $verses = $this->quranApiService->getVerses($reviewItem->surah, $reviewItem->verse_start, $reviewItem->verse_end);
                $verseData = [
                    'text' => implode(' ', array_column($verses, 'text')),
                    'surah_name' => $verses[0]['surah_name'] ?? '',
                    'number' => $verses[0]['number'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            return redirect()->route('student.reviews.index')
                ->with('error', 'فشل في جلب نص الآية. يرجى المحاولة لاحقاً.');
        }

        return view('student.reviews.session', [
            'reviewItem' => $reviewItem,
            'verseText' => $verseData['text'] ?? '',
            'verseMeta' => $verseData,
        ]);
    }

    public function submitReview(Request $request, ReviewItem $reviewItem)
    {
        $request->validate([
            'quality' => 'required|integer|min:1|max:5',
        ]);

        $student = $this->getStudent();

        // Verify ownership
        if ($reviewItem->student_id !== $student->id) {
            abort(403);
        }

        $quality = $this->sm2Service->mapRatingToQuality((int) $request->quality);

        // Calculate new SM-2 values
        $next = $this->sm2Service->calculateNext(
            $reviewItem->repetitions,
            $reviewItem->ease_factor,
            $reviewItem->interval,
            $quality
        );

        // Update review item
        $reviewItem->update([
            'repetitions' => $next['repetitions'],
            'ease_factor' => $next['ease_factor'],
            'interval' => $next['interval'],
            'next_review_date' => $this->sm2Service->calculateNextReviewDate($next['interval']),
            'last_reviewed_at' => Carbon::now(),
        ]);

        // Record the review
        RecitationReview::create([
            'review_item_id' => $reviewItem->id,
            'student_id' => $student->id,
            'quality' => $quality,
            'reviewed_at' => Carbon::now(),
        ]);

        // Check if student wants to continue or finish
        if ($request->has('continue') && $request->continue) {
            return redirect()->route('student.reviews.session');
        }

        return redirect()->route('student.reviews.index')
            ->with('success', 'تم تسجيل المراجعة بنجاح');
    }

    public function addToReviews(Request $request)
    {
        $request->validate([
            'surah' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'required|integer|min:1',
            'page_number' => 'nullable|integer|min:1|max:604',
        ]);

        $student = $this->getStudent();

        // Check if already exists
        $exists = ReviewItem::where('student_id', $student->id)
            ->where('surah', $request->surah)
            ->where('verse_start', $request->verse_start)
            ->where('verse_end', $request->verse_end)
            ->exists();

        if ($exists) {
            return back()->with('info', 'هذه الآيات موجودة بالفعل في قائمة المراجعة');
        }

        ReviewItem::create([
            'student_id' => $student->id,
            'surah' => $request->surah,
            'verse_start' => $request->verse_start,
            'verse_end' => $request->verse_end,
            'page_number' => $request->page_number,
            'ease_factor' => SM2Service::DEFAULT_EASE_FACTOR,
            'interval' => 1,
            'repetitions' => 0,
            'next_review_date' => Carbon::today(),
        ]);

        return back()->with('success', 'تمت إضافة الآيات إلى قائمة المراجعة');
    }

    public function destroy(ReviewItem $reviewItem)
    {
        $student = $this->getStudent();

        if ($reviewItem->student_id !== $student->id) {
            abort(403);
        }

        $reviewItem->delete();

        return redirect()->route('student.reviews.index')
            ->with('success', 'تم حذف العنصر من المراجعة');
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
