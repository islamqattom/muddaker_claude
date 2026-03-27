<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentBookmark;
use App\Models\BookmarkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $categoryId = $request->input('category');

        $bookmarksQuery = StudentBookmark::where('student_id', $student->id)
            ->with('category')
            ->orderBy('created_at', 'desc');

        if ($categoryId) {
            $bookmarksQuery->where('category_id', $categoryId);
        }

        $bookmarks = $bookmarksQuery->get();
        $categories = BookmarkCategory::where('student_id', $student->id)
            ->withCount('bookmarks')
            ->orderBy('sort_order')
            ->get();

        return view('student.bookmarks.index', compact(
            'student', 'bookmarks', 'categories', 'categoryId'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $validated = $request->validate([
            'page_number' => 'required|integer|min:1|max:604',
            'verse_number' => 'nullable|integer|min:1',
            'surah_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:bookmark_categories,id',
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $validated['student_id'] = $student->id;

        // Ensure category belongs to this student
        if (!empty($validated['category_id'])) {
            $category = BookmarkCategory::where('id', $validated['category_id'])
                ->where('student_id', $student->id)
                ->first();
            if (!$category) {
                $validated['category_id'] = null;
            }
        }

        $bookmark = StudentBookmark::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'bookmark' => $bookmark,
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة العلامة بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $bookmark = StudentBookmark::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $validated = $request->validate([
            'category_id' => 'nullable|exists:bookmark_categories,id',
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['category_id'])) {
            $category = BookmarkCategory::where('id', $validated['category_id'])
                ->where('student_id', $student->id)
                ->first();
            if (!$category) {
                $validated['category_id'] = null;
            }
        }

        $bookmark->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'bookmark' => $bookmark->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث العلامة بنجاح');
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $bookmark = StudentBookmark::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $bookmark->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $bookmarks = StudentBookmark::where('student_id', $student->id)
            ->with('category')
            ->orderBy('page_number')
            ->get();

        $format = $request->input('format', 'json');

        if ($format === 'csv') {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['Page', 'Verse', 'Surah', 'Title', 'Note', 'Category', 'Created At']);

            foreach ($bookmarks as $b) {
                fputcsv($handle, [
                    $b->page_number,
                    $b->verse_number ?? '',
                    $b->surah_name ?? '',
                    $b->title ?? '',
                    $b->note ?? '',
                    $b->category?->name ?? '',
                    $b->created_at?->toDateTimeString() ?? '',
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="bookmarks.csv"',
            ]);
        }

        return response()->json([
            'success' => true,
            'bookmarks' => $bookmarks,
        ]);
    }

    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $bookmarks = StudentBookmark::where('student_id', $student->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bookmarks' => $bookmarks,
        ]);
    }
}
