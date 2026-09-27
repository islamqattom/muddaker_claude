<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingTask;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingTask::with(['meetingMinute', 'assignee', 'assigner'])
            ->whereHas('assigner', function ($q) {
                $q->where('role', 'admin');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);

        $supervisors = \App\Models\User::whereIn('role', ['admin', 'supervisor'])->get();
        $students = Student::with('user')->orderedByName()->get();

        return view('admin.tasks.index', compact('tasks', 'supervisors', 'students'));
    }

    public function updateStatus(Request $request, MeetingTask $task)
    {
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

    public function destroy(Request $request, MeetingTask $task)
    {
        if (auth()->user()->role === 'supervisor') {
            $supervisorId = auth()->id();
            $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();
            $allowed = ($task->assignee_type === 'user' && $task->assignee_id === $supervisorId)
                || ($task->assignee_type === 'student' && in_array($task->assignee_id, $supervisedStudentIds, true))
                || $task->assigned_by === $supervisorId;
            if (! $allowed) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'لا تملك صلاحية حذف هذه المهمة'], 403);
                }
                abort(403, 'لا تملك صلاحية حذف هذه المهمة');
            }
        }

        $scope = $request->input('scope', 'single');
        $deletedCount = 1;

        if ($scope === 'series' && $task->recurrence_group_id) {
            $deletedCount = MeetingTask::where('recurrence_group_id', $task->recurrence_group_id)->delete();
        } elseif ($scope === 'future' && $task->recurrence_group_id) {
            $deletedCount = MeetingTask::where('recurrence_group_id', $task->recurrence_group_id)
                ->where('due_date', '>=', $task->due_date->format('Y-m-d'))
                ->delete();
        } elseif ($scope === 'past' && $task->recurrence_group_id) {
            $deletedCount = MeetingTask::where('recurrence_group_id', $task->recurrence_group_id)
                ->where('due_date', '<', $task->due_date->format('Y-m-d'))
                ->delete();
        } else {
            $task->delete();
        }

        $message = match (true) {
            $deletedCount > 1 && $scope === 'series' => "تم حذف {$deletedCount} مهمة (السلسلة الكاملة)",
            $deletedCount > 1 && $scope === 'future' => "تم حذف {$deletedCount} مهمة (هذه وجميع اللاحقة)",
            $deletedCount > 1 && $scope === 'past' => "تم حذف {$deletedCount} مهمة (السابقة في السلسلة)",
            default => 'تم حذف المهمة بنجاح',
        };

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, MeetingTask $task)
    {
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
            if (!$student) {
                return $this->errorResponse($request, 'الطالب المحدد غير موجود');
            }
            if (auth()->user()->role === 'supervisor' && $student->supervisor_id !== auth()->id()) {
                return $this->errorResponse($request, 'لا تملك صلاحية تعديل مهام لهذا الطالب');
            }
        } elseif ($assigneeType === 'user' && $assigneeId) {
            $user = \App\Models\User::find($assigneeId);
            if (!$user || !in_array($user->role, ['admin', 'supervisor'], true)) {
                return $this->errorResponse($request, 'المستخدم المحدد غير صالح');
            }
            if (auth()->user()->role === 'supervisor' && $user->id !== auth()->id()) {
                return $this->errorResponse($request, 'لا تملك صلاحية تعديل مهام لمستخدمين آخرين');
            }
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

    public function calendar(Request $request)
    {
        $query = MeetingTask::with(['meetingMinute', 'assigner']);

        if (auth()->user()->role === 'supervisor') {
            $supervisorId = auth()->id();
            $supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();
            $query->where(function ($q) use ($supervisorId, $supervisedStudentIds) {
                $q->where(function ($sub) use ($supervisorId) {
                    $sub->where('assignee_type', 'user')->where('assignee_id', $supervisorId);
                });
                if (!empty($supervisedStudentIds)) {
                    $q->orWhere(function ($sub) use ($supervisedStudentIds) {
                        $sub->where('assignee_type', 'student')
                            ->whereIn('assignee_id', $supervisedStudentIds);
                    });
                }
            });
        }

        $studentFilter = $request->get('student_id');
        $selectedStudent = null;
        if ($studentFilter) {
            $selectedStudent = Student::with('user')->find($studentFilter);
            if ($selectedStudent) {
                $query->where('assignee_type', 'student')
                    ->where('assignee_id', $selectedStudent->id);
            }
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        $tasksJson = $tasks->map(function ($task) {
            return $this->formatTaskForCalendar($task);
        });

        $supervisors = \App\Models\User::whereIn('role', ['admin', 'supervisor'])
            ->where('id', '!=', auth()->id())
            ->get();

        if (auth()->user()->role === 'supervisor') {
            $students = Student::with('user')
                ->where('supervisor_id', auth()->id())
                ->orderedByName()
                ->get();
        } else {
            $students = Student::with('user')->orderedByName()->get();
        }

        return view('admin.tasks.calendar', compact('tasksJson', 'supervisors', 'students', 'studentFilter', 'selectedStudent'));
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
            'meeting_minute_id' => 'nullable|exists:meeting_minutes,id',
            'repeat_enabled' => 'nullable|boolean',
            'repeat_start_date' => 'nullable|required_with:repeat_enabled|date',
            'repeat_end_date' => 'nullable|required_with:repeat_enabled|date|after_or_equal:repeat_start_date',
        ]);

        $assigneeType = $validated['assignee_type'] ?? 'user';
        $assigneeId = $validated['assignee_id'] ?? null;

        if ($assigneeType === 'student' && $assigneeId) {
            $student = Student::find($assigneeId);
            if (!$student) {
                return $this->errorResponse($request, 'الطالب المحدد غير موجود');
            }
            if (auth()->user()->role === 'supervisor' && $student->supervisor_id !== auth()->id()) {
                return $this->errorResponse($request, 'لا تملك صلاحية إضافة مهام لهذا الطالب');
            }
        } elseif ($assigneeType === 'user' && $assigneeId) {
            $user = \App\Models\User::find($assigneeId);
            if (!$user || !in_array($user->role, ['admin', 'supervisor'], true)) {
                return $this->errorResponse($request, 'المستخدم المحدد غير صالح');
            }
            if (auth()->user()->role === 'supervisor' && $user->id !== auth()->id()) {
                return $this->errorResponse($request, 'لا تملك صلاحية إضافة مهام لمستخدمين آخرين');
            }
        } else {
            $assigneeId = auth()->id();
            $assigneeType = 'user';
        }

        $endTime = null;
        if (!empty($validated['due_time']) && !empty($validated['end_time'])) {
            $endTime = $validated['end_time'];
        }

        $dates = $this->resolveTaskDates($validated);

        if (count($dates) > 60) {
            return $this->errorResponse($request, 'الحد الأقصى للتكرار 60 يوماً');
        }

        $meetingMinuteId = $validated['meeting_minute_id'] ?? null;

        $createdTasks = [];
        $notifiedAlready = false;
        $sharedColor = MeetingTask::generateUniqueColor();
        $recurrenceGroupId = count($dates) > 1 ? (string) Str::uuid() : null;

        foreach ($dates as $date) {
            $taskData = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assignee_id' => $assigneeId,
                'assignee_type' => $assigneeType,
                'assigned_by' => auth()->id(),
                'recurrence_group_id' => $recurrenceGroupId,
                'due_date' => $date,
                'due_time' => $validated['due_time'] ?? null,
                'end_time' => $endTime,
                'status' => 'pending',
                'color' => $sharedColor,
            ];

            if ($meetingMinuteId) {
                $taskData['meeting_minute_id'] = $meetingMinuteId;
            }

            $task = MeetingTask::create($taskData);
            $createdTasks[] = $task;

            if ($assigneeId && !$notifiedAlready && $meetingMinuteId) {
                $this->notifyAssignee($task, $assigneeType, $assigneeId, $validated['title']);
                $notifiedAlready = true;
            }
        }

        $count = count($createdTasks);
        $message = $count > 1
            ? "تم إضافة {$count} مهمة بنجاح"
            : 'تم إضافة التكليف بنجاح';

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

    private function notifyAssignee(MeetingTask $task, string $type, int $id, string $title): void
    {
        try {
            $user = null;
            if ($type === 'student') {
                $student = Student::find($id);
                $user = $student?->user;
            } else {
                $user = \App\Models\User::find($id);
            }

            if (!$user) {
                return;
            }

            $notificationService = app(\App\Services\NotificationService::class);
            $link = match (true) {
                $user->isAdmin() => route('admin.tasks.index'),
                $user->isSupervisor() => route('supervisor.tasks.index'),
                $user->isStudent() => route('student.tasks.calendar'),
                default => route('admin.tasks.index'),
            };

            $notificationService->sendToUser(
                $user,
                'meeting_task',
                'تكليف جديد',
                'تم إسناد تكليف جديد: ' . $title,
                $link
            );
        } catch (\Exception $e) {
            \Log::error("Failed to notify assignee {$id} about task {$task->id}: " . $e->getMessage());
        }
    }

    private function errorResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }
        return redirect()->back()->with('error', $message);
    }

    public function formatTaskForCalendar(MeetingTask $task): array
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
                'recurrenceGroupId' => $task->recurrence_group_id,
            ]
        ];
    }
}
