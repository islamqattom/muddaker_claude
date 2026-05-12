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
}
