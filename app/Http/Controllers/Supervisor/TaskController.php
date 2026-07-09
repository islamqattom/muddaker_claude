<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\MeetingTask;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $supervisorId = auth()->id();
        $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();

        $tasks = MeetingTask::whereNotNull('meeting_minute_id')
            ->where(function ($q) use ($supervisorId, $supervisedStudentIds) {
                $q->where(function ($sub) use ($supervisorId) {
                    $sub->where('assignee_type', 'user')->where('assignee_id', $supervisorId);
                });
                if (!empty($supervisedStudentIds)) {
                    $q->orWhere(function ($sub) use ($supervisedStudentIds) {
                        $sub->where('assignee_type', 'student')
                            ->whereIn('assignee_id', $supervisedStudentIds);
                    });
                }
            })
            ->with(['meetingMinute', 'assigner'])
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('supervisor.tasks.index', compact('tasks'));
    }

    public function calendar()
    {
        $supervisorId = auth()->id();
        $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();

        $query = MeetingTask::where(function ($q) use ($supervisorId, $supervisedStudentIds) {
            $q->where(function ($sub) use ($supervisorId) {
                $sub->where('assignee_type', 'user')->where('assignee_id', $supervisorId);
            });
            if (!empty($supervisedStudentIds)) {
                $q->orWhere(function ($sub) use ($supervisedStudentIds) {
                    $sub->where('assignee_type', 'student')
                        ->whereIn('assignee_id', $supervisedStudentIds);
                });
            }
        })
            ->with(['meetingMinute', 'assigner']);

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $tasksJson = $tasks->map(function ($task) {
            return $this->formatTaskForCalendar($task);
        });

        $students = Student::with('user')
            ->where('supervisor_id', $supervisorId)
            ->get();

        $supervisors = \App\Models\User::whereIn('role', ['admin', 'supervisor'])
            ->where('id', '!=', $supervisorId)
            ->get();

        return view('admin.tasks.calendar', compact('tasksJson', 'students', 'supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
            'assignee_type' => 'nullable|in:user,student',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:due_time',
            'repeat_enabled' => 'nullable|boolean',
            'repeat_start_date' => 'nullable|required_with:repeat_enabled|date',
            'repeat_end_date' => 'nullable|required_with:repeat_enabled|date|after_or_equal:repeat_start_date',
        ]);

        $supervisorId = auth()->id();
        $assigneeType = $validated['assignee_type'] ?? 'user';
        $assigneeId = $validated['assignee_id'] ?? $supervisorId;

        if ($assigneeType === 'student' && $assigneeId) {
            $student = Student::find($assigneeId);
            if (!$student || $student->supervisor_id !== $supervisorId) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'لا تملك صلاحية إضافة مهام لهذا الطالب'], 403);
                }
                abort(403, 'لا تملك صلاحية إضافة مهام لهذا الطالب');
            }
        } elseif ($assigneeType === 'user' && $assigneeId && $assigneeId != $supervisorId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'لا يمكن إضافة مهام لمستخدمين آخرين'], 403);
            }
            abort(403, 'لا يمكن إضافة مهام لمستخدمين آخرين');
        } else {
            $assigneeId = $supervisorId;
            $assigneeType = 'user';
        }

        $endTime = null;
        if (!empty($validated['due_time']) && !empty($validated['end_time'])) {
            $endTime = $validated['end_time'];
        }

        $dates = $this->resolveTaskDates($validated);

        if (count($dates) > 60) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'الحد الأقصى للتكرار 60 يوماً'], 422);
            }
            return redirect()->back()->with('error', 'الحد الأقصى للتكرار 60 يوماً');
        }

        $createdTasks = [];
        $sharedColor = MeetingTask::generateUniqueColor();
        foreach ($dates as $date) {
            $task = MeetingTask::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assignee_id' => $assigneeId,
                'assignee_type' => $assigneeType,
                'assigned_by' => $supervisorId,
                'due_date' => $date,
                'due_time' => $validated['due_time'] ?? null,
                'end_time' => $endTime,
                'status' => 'pending',
                'color' => $sharedColor,
            ]);
            $createdTasks[] = $task;
        }

        $count = count($createdTasks);
        $message = $count > 1
            ? "تم إضافة {$count} مهمة بنجاح"
            : 'تم إضافة المهمة بنجاح';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count,
                'task' => $this->formatTaskForCalendar($createdTasks[0]->fresh()),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function resolveTaskDates(array $validated): array
    {
        $baseDate = $validated['due_date'];

        if (empty($validated['repeat_enabled'])) {
            return [$baseDate];
        }

        $startDate = $validated['repeat_start_date'] ?? $baseDate;
        $endDate = $validated['repeat_end_date'] ?? $baseDate;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $dates = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates ?: [$baseDate];
    }

    public function updateStatus(Request $request, MeetingTask $task)
    {
        $supervisorId = auth()->id();
        $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();

        $isAuthorized = false;
        if ($task->assignee_type === 'user' && $task->assignee_id === $supervisorId) {
            $isAuthorized = true;
        } elseif ($task->assignee_type === 'student' && in_array($task->assignee_id, $supervisedStudentIds, true)) {
            $isAuthorized = true;
        } elseif ($task->assigned_by === $supervisorId) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,partially_done,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $statusColor = MeetingTask::colorForStatus($newStatus);
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'pending') {
            $updateData['color'] = MeetingTask::generateUniqueColor();
        } elseif ($statusColor !== null) {
            $updateData['color'] = $statusColor;
        }

        $task->update($updateData);

        if ($newStatus === 'completed') {
            $task->update(['completed_at' => now()]);
        } else {
            $task->update(['completed_at' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث حالة التكليف بنجاح']);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة التكليف بنجاح');
    }

    public function update(Request $request, MeetingTask $task)
    {
        $supervisorId = auth()->id();
        $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();

        $isAuthorized = false;
        if ($task->assignee_type === 'user' && $task->assignee_id === $supervisorId) {
            $isAuthorized = true;
        } elseif ($task->assignee_type === 'student' && in_array($task->assignee_id, $supervisedStudentIds, true)) {
            $isAuthorized = true;
        } elseif ($task->assigned_by === $supervisorId) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
            'assignee_type' => 'nullable|in:user,student',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:due_time',
        ]);

        $assigneeType = $validated['assignee_type'] ?? $task->assignee_type;
        $assigneeId = $validated['assignee_id'] ?? $task->assignee_id;

        if ($assigneeType === 'student' && $assigneeId) {
            $student = Student::find($assigneeId);
            if (!$student || $student->supervisor_id !== $supervisorId) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'لا تملك صلاحية تعديل مهام لهذا الطالب'], 403);
                }
                abort(403, 'لا تملك صلاحية تعديل مهام لهذا الطالب');
            }
        } elseif ($assigneeType === 'user' && $assigneeId && $assigneeId != $supervisorId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'لا يمكن تعديل مهام لمستخدمين آخرين'], 403);
            }
            abort(403, 'لا يمكن تعديل مهام لمستخدمين آخرين');
        } else {
            $assigneeId = $supervisorId;
            $assigneeType = 'user';
        }

        $endTime = null;
        if (!empty($validated['due_time']) && !empty($validated['end_time'])) {
            $endTime = $validated['end_time'];
        }

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assignee_id' => $assigneeId,
            'assignee_type' => $assigneeType,
            'due_date' => $validated['due_date'],
            'due_time' => $validated['due_time'] ?? null,
            'end_time' => $endTime,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المهمة بنجاح',
                'task' => $this->formatTaskForCalendar($task->fresh()),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث المهمة بنجاح');
    }

    private function formatTaskForCalendar(MeetingTask $task): array
    {
        $statusClass = $task->status;
        if ($task->is_overdue && !in_array($task->status, ['completed', 'cancelled'], true)) {
            $statusClass = 'overdue';
        }

        $hasTime = $task->due_time !== null;
        $hasEndTime = $task->end_time !== null;
        $dateStr = $task->due_date ? $task->due_date->format('Y-m-d') : $task->created_at->format('Y-m-d');

        if ($hasTime) {
            $start = $dateStr . 'T' . $task->due_time->format('H:i:s');
            $end = $hasEndTime ? $dateStr . 'T' . $task->end_time->format('H:i:s') : null;
        } else {
            $start = $dateStr;
            $end = null;
        }

        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $start,
            'end' => $end,
            'allDay' => !$hasTime,
            'color' => $task->effective_color,
            'extendedProps' => [
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'statusLabel' => $task->status_label,
                'statusClass' => $statusClass,
                'assigneeId' => $task->assignee_id,
                'assigneeName' => $task->assignee_name,
                'assignerName' => $task->assigner?->name ?? '-',
                'createdDate' => $task->created_at->format('Y/m/d'),
                'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'dueTime' => $hasTime ? $task->due_time->format('H:i') : null,
                'endTime' => $hasEndTime ? $task->end_time->format('H:i') : null,
                'isOverdue' => $task->is_overdue,
                'assigneeType' => $task->assignee_type,
                'color' => $task->effective_color,
            ]
        ];
    }
}
