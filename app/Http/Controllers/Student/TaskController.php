<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MeetingTask;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function calendar()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $tasks = MeetingTask::where(function ($q) use ($student) {
            $q->where(function ($sub) use ($student) {
                $sub->where('assignee_type', 'student')
                    ->where('assignee_id', $student->id);
            })->orWhere(function ($sub) use ($student) {
                $sub->where('assignee_type', 'user')
                    ->where('assignee_id', $student->user_id);
            });
        })
            ->with(['assigner', 'meetingMinute'])
            ->orderBy('created_at', 'desc')
            ->get();

        $tasksJson = $tasks->map(function ($task) {
            return $this->formatTaskForCalendar($task);
        });

        return view('student.tasks.calendar', [
            'tasksJson' => $tasksJson,
            'student' => $student,
        ]);
    }

    public function store(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:due_time',
            'repeat_enabled' => 'nullable|boolean',
            'repeat_start_date' => 'nullable|required_with:repeat_enabled|date',
            'repeat_end_date' => 'nullable|required_with:repeat_enabled|date|after_or_equal:repeat_start_date',
        ]);

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
        $recurrenceGroupId = count($dates) > 1 ? (string) Str::uuid() : null;
        foreach ($dates as $date) {
            $task = MeetingTask::create([
                'assignee_id' => $student->id,
                'assignee_type' => 'student',
                'assigned_by' => auth()->id(),
                'recurrence_group_id' => $recurrenceGroupId,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
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
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $isOwner = ($task->assignee_type === 'student' && $task->assignee_id === $student->id)
            || ($task->assignee_type === 'user' && $task->assignee_id === $student->user_id);

        if (!$isOwner) {
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
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة المهمة بنجاح',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة المهمة بنجاح');
    }

    public function destroy(Request $request, MeetingTask $task)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $isOwner = ($task->assignee_type === 'student' && $task->assignee_id === $student->id)
            || ($task->assignee_type === 'user' && $task->assignee_id === $student->user_id);

        if (!$isOwner) {
            abort(403);
        }

        $scope = $request->input('scope', 'single');
        $deletedCount = 1;

        if ($scope === 'series' && $task->recurrence_group_id) {
            $deletedCount = MeetingTask::where('recurrence_group_id', $task->recurrence_group_id)
                ->delete();
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
            default => 'تم حذف المهمة',
        };

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkDelete(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $ids = $request->input('ids',');
        if (!is_array($ids) || empty($ids)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'لم يتم تحديد أي مهمة'], 422);
            }
            return redirect()->back()->with('error', 'لم يتم تحديد أي مهمة');
        }

        $ids = array_map('intval', $ids);

        $deletedCount = MeetingTask::whereIn('id', $ids)
            ->where(function ($q) use ($student) {
                $q->where(function ($sub) use ($student) {
                    $sub->where('assignee_type', 'student')->where('assignee_id', $student->id);
                })->orWhere(function ($sub) use ($student) {
                    $sub->where('assignee_type', 'user')->where('assignee_id', $student->user_id);
                });
            })
            ->delete();

        $message = "تم حذف {$deletedCount} مهمة";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, MeetingTask $task)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $isOwner = ($task->assignee_type === 'student' && $task->assignee_id === $student->id)
            || ($task->assignee_type === 'user' && $task->assignee_id === $student->user_id);

        if (!$isOwner) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:due_time',
        ]);

        $endTime = null;
        if (!empty($validated['due_time']) && !empty($validated['end_time'])) {
            $endTime = $validated['end_time'];
        }

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
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

        $student = Student::where('user_id', auth()->id())->first();

        $assignerIsMe = $task->assigned_by === auth()->id();

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
                'assigneeName' => $student?->user?->name ?? '-',
                'assignerName' => $task->assigner ? $task->assigner->name : '-',
                'isSelfAssigned' => $assignerIsMe,
                'createdDate' => $task->created_at->format('Y/m/d'),
                'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'dueTime' => $hasTime ? $task->due_time->format('H:i') : null,
                'endTime' => $hasEndTime ? $task->end_time->format('H:i') : null,
                'isOverdue' => $task->is_overdue,
                'isOwnTask' => true,
                'assigneeType' => $task->assignee_type,
                'color' => $task->effective_color,
                'recurrenceGroupId' => $task->recurrence_group_id,
            ]
        ];
    }
}
