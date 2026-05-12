<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingTask::with(['meetingMinute', 'assignee', 'assigner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        $supervisors = \App\Models\User::whereIn('role', ['admin', 'supervisor'])->get();

        return view('admin.tasks.index', compact('tasks', 'supervisors'));
    }

    public function updateStatus(Request $request, MeetingTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة التكليف بنجاح');
    }

    public function updateTitle(Request $request, MeetingTask $task)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->errors()['title'][0] ?? 'Validation failed'], 422);
            }
            throw $e;
        }

        $task->update(['title' => $validated['title']]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث عنوان التكليف بنجاح']);
        }

        return redirect()->back()->with('success', 'تم تحديث عنوان التكليف بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'meeting_minute_id' => 'nullable|exists:meeting_minutes,id',
        ]);

        // Get the latest meeting minute if not specified
        $meetingMinuteId = $validated['meeting_minute_id'] ?? \App\Models\MeetingMinute::latest('id')->value('id');

        $taskData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'assigned_by' => auth()->id(),
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'pending',
        ];

        // Only add meeting_minute_id if it exists
        if ($meetingMinuteId) {
            $taskData['meeting_minute_id'] = $meetingMinuteId;
        }

        $task = MeetingTask::create($taskData);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم إضافة التكليف بنجاح', 'task' => $task]);
        }

        return redirect()->back()->with('success', 'تم إضافة التكليف بنجاح');
    }
}
