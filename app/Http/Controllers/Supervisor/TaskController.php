<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\MeetingTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = MeetingTask::where('assignee_id', auth()->id())
            ->with(['meetingMinute', 'assigner'])
            ->when(request('status'), function($query) {
                $query->where('status', request('status'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('supervisor.tasks.index', compact('tasks'));
    }

    public function updateStatus(Request $request, MeetingTask $task)
    {
        if ($task->assignee_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة التكليف بنجاح');
    }
}