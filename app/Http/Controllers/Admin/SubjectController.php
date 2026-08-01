<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $role = Auth::user()->role;

        $query = Subject::with('creator')
            ->withCount(['lessons', 'lessons as completed_lessons_count' => function ($q) {
                $q->where('status', 'completed');
            }, 'lessons as pending_lessons_count' => function ($q) {
                $q->where('status', 'pending');
            }]);

        if ($role !== 'admin') {
            $query->where('created_by', $userId);
        } elseif ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('is_active') && $request->is_active !== 'all') {
            $query->where('is_active', $request->is_active === '1');
        }

        $subjects = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => (clone $query)->get()->count() > 0
                ? (clone $query)->count()
                : Subject::when($role !== 'admin', fn($q) => $q->where('created_by', $userId))->count(),
            'active' => Subject::when($role !== 'admin', fn($q) => $q->where('created_by', $userId))
                ->where('is_active', true)->count(),
            'total_lessons' => \App\Models\Lesson::when($role !== 'admin', fn($q) => $q->where('created_by', $userId))->count(),
            'completed_lessons' => \App\Models\Lesson::when($role !== 'admin', fn($q) => $q->where('created_by', $userId))
                ->where('status', 'completed')->count(),
        ];

        $creators = [];
        if ($role === 'admin') {
            $creators = \App\Models\User::whereIn('id', Subject::distinct()->pluck('created_by'))
                ->orderBy('name')->get();
        }

        return view('admin.subjects.index', compact('subjects', 'stats', 'creators'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['color'] = $validated['color'] ?? Subject::PALETTE[array_rand(Subject::PALETTE)];

        $subject = Subject::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المادة بنجاح',
                'subject' => $subject,
            ]);
        }

        return redirect()->route('admin.subjects.show', $subject->id)
            ->with('success', 'تم إنشاء المادة بنجاح');
    }

    public function show(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $subject->load('creator');
        $subject->loadCount([
            'lessons',
            'lessons as completed_lessons_count' => fn($q) => $q->where('status', 'completed'),
            'lessons as pending_lessons_count' => fn($q) => $q->where('status', 'pending'),
            'lessons as overdue_lessons_count' => function ($q) {
                $q->where('status', 'pending')->where('lesson_date', '<', now()->toDateString());
            },
        ]);

        $query = $subject->lessons()->orderBy('lesson_date')->orderBy('start_time');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('lesson_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('lesson_date', '<=', $request->to_date);
        }

        $lessons = $query->paginate(15)->withQueryString();

        return view('admin.subjects.show', compact('subject', 'lessons'));
    }

    public function edit(Subject $subject)
    {
        $this->authorizeAccess($subject);
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $subject->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المادة بنجاح',
                'subject' => $subject->fresh(),
            ]);
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', 'تم تحديث المادة بنجاح');
    }

    public function destroy(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $subject->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حذف المادة بنجاح']);
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', 'تم حذف المادة بنجاح');
    }

    public function calendar(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $lessons = $subject->lessons()->orderBy('lesson_date')->get();
        $events = $lessons->map(fn($l) => $l->toCalendarEvent($subject));

        return view('admin.subjects.calendar', [
            'subject' => $subject,
            'events' => $events,
        ]);
    }

    public function feed(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $lessons = $subject->lessons()->orderBy('lesson_date')->get();
        return response()->json($lessons->map(fn($l) => $l->toCalendarEvent($subject)));
    }

    public function authorizeAccess(Subject $subject): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $subject->created_by !== $user->id) {
            abort(403, 'لا تملك صلاحية الوصول لهذه المادة');
        }
    }

    public function authorizeAccessPublic(Subject $subject): void
    {
        $this->authorizeAccess($subject);
    }
}
