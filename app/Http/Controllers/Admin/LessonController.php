<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function create(Subject $subject)
    {
        $this->authorizeSubject($subject);
        return view('admin.lessons.create', compact('subject'));
    }

    public function store(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:5000',
            'lesson_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:pending,completed,cancelled',
            'repeat_enabled' => 'nullable|boolean',
            'repeat_weeks' => 'nullable|integer|min:1|max:52|required_with:repeat_enabled',
        ]);

        $status = $validated['status'] ?? 'pending';
        $dates = $this->resolveLessonDates($validated);

        if (count($dates) > 60) {
            return $this->errorResponse($request, 'الحد الأقصى للتكرار 60 أسبوعاً');
        }

        $created = [];
        foreach ($dates as $date) {
            $created[] = Lesson::create([
                'subject_id' => $subject->id,
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'content' => $validated['content'] ?? null,
                'lesson_date' => $date,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);
        }

        $count = count($created);
        $message = $count > 1
            ? "تم إضافة {$count} درس بنجاح"
            : 'تم إضافة الدرس بنجاح';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count,
                'lesson' => $created[0]->toCalendarEvent($subject),
            ]);
        }

        return redirect()->route('admin.subjects.show', $subject->id)
            ->with('success', $message);
    }

    public function show(Subject $subject, Lesson $lesson)
    {
        $this->authorizeSubject($subject);
        $this->authorizeLesson($subject, $lesson);
        $lesson->load('subject', 'creator');
        return view('admin.lessons.show', compact('subject', 'lesson'));
    }

    public function edit(Subject $subject, Lesson $lesson)
    {
        $this->authorizeSubject($subject);
        $this->authorizeLesson($subject, $lesson);
        return view('admin.lessons.edit', compact('subject', 'lesson'));
    }

    public function update(Request $request, Subject $subject, Lesson $lesson)
    {
        $this->authorizeSubject($subject);
        $this->authorizeLesson($subject, $lesson);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:5000',
            'lesson_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:pending,completed,cancelled',
        ]);

        $newStatus = $validated['status'] ?? $lesson->status;
        $data = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'lesson_date' => $validated['lesson_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $newStatus,
        ];

        if ($newStatus === 'completed' && $lesson->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif ($newStatus !== 'completed') {
            $data['completed_at'] = null;
        }

        $lesson->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الدرس بنجاح',
                'lesson' => $lesson->fresh()->toCalendarEvent($subject),
            ]);
        }

        return redirect()->route('admin.subjects.show', $subject->id)
            ->with('success', 'تم تحديث الدرس بنجاح');
    }

    public function updateStatus(Request $request, Subject $subject, Lesson $lesson)
    {
        $this->authorizeSubject($subject);
        $this->authorizeLesson($subject, $lesson);

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $data = ['status' => $newStatus];

        if ($newStatus === 'completed' && $lesson->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif ($newStatus !== 'completed') {
            $data['completed_at'] = null;
        }

        $lesson->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الدرس بنجاح',
                'status' => $newStatus,
                'statusLabel' => $lesson->fresh()->status_label,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الدرس بنجاح');
    }

    public function destroy(Request $request, Subject $subject, Lesson $lesson)
    {
        $this->authorizeSubject($subject);
        $this->authorizeLesson($subject, $lesson);

        $lesson->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حذف الدرس بنجاح']);
        }

        return redirect()->route('admin.subjects.show', $subject->id)
            ->with('success', 'تم حذف الدرس بنجاح');
    }

    private function resolveLessonDates(array $validated): array
    {
        $baseDate = !empty($validated['lesson_date'])
            ? Carbon::parse($validated['lesson_date'])->toDateString()
            : null;

        if (empty($validated['repeat_enabled']) || empty($validated['repeat_weeks'])) {
            return [$baseDate];
        }

        $weeks = (int) $validated['repeat_weeks'];
        $dates = [$baseDate];

        if ($baseDate) {
            for ($i = 1; $i <= $weeks; $i++) {
                $dates[] = Carbon::parse($baseDate)->addWeeks($i)->toDateString();
            }
        }

        return $dates;
    }

    public function authorizeSubject(Subject $subject): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $subject->created_by !== $user->id) {
            abort(403, 'لا تملك صلاحية الوصول لهذه المادة');
        }
    }

    public function authorizeSubjectPublic(Subject $subject): void
    {
        $this->authorizeSubject($subject);
    }

    private function authorizeLesson(Subject $subject, Lesson $lesson): void
    {
        if ($lesson->subject_id !== $subject->id) {
            abort(404);
        }
    }

    private function errorResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }
        return redirect()->back()->with('error', $message);
    }
}
