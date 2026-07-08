<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\MeetingMinute;
use App\Models\MeetingTask;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Services\AiParsingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MeetingMinuteController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingMinute::query()->with('creator');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('meeting_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('meeting_date', '<=', $request->to_date);
        }

        $meetingMinutes = $query->orderBy('meeting_date', 'desc')->paginate(15);

        return view('admin.meeting-minutes.index', compact('meetingMinutes'));
    }

    public function create()
    {
        $supervisors = User::whereIn('role', ['admin', 'supervisor'])->get();

        $tasksFromAllMeetings = MeetingTask::whereIn('status', ['pending', 'in_progress'])
            ->whereHas('meetingMinute', function($query) {
                $query->where('meeting_date', '<', now()->toDateString());
            })
            ->with(['assignee', 'meetingMinute'])
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $recentAttendance = Attendance::where('date', '>=', now()->subDays(3))
            ->with('student.user')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('student_id');

        $supervisorsWithStudents = User::where('role', 'supervisor')
            ->with(['supervisor', 'supervisedStudents.user', 'supervisedStudents.level'])
            ->get();

        $studentIds = $supervisorsWithStudents->pluck('supervisedStudents')->flatten()->pluck('id')->toArray();

        $allAttendances = empty($studentIds) ? collect() : Attendance::whereIn('student_id', $studentIds)
            ->where('date', '>=', now()->subDays(3))
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('student_id');

        $allRecitations = empty($studentIds) ? collect() : RecitationSession::whereIn('student_id', $studentIds)
            ->orderBy('session_date', 'desc')
            ->get()
            ->groupBy('student_id');

        $supervisorStudents = $supervisorsWithStudents->map(function ($supervisor) use ($allAttendances, $allRecitations) {
            $supervisor->students_with_data = $supervisor->supervisedStudents->map(function ($student) use ($allAttendances, $allRecitations) {
                $student->last_three_attendances = $allAttendances->get($student->id, collect())->take(3);
                $student->last_two_recitations = $allRecitations->get($student->id, collect())->take(2);
                return $student;
            });
            return $supervisor;
        });

        return view('admin.meeting-minutes.create', compact('supervisors', 'recentAttendance', 'tasksFromAllMeetings', 'supervisorStudents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|array',
            'manual_attendees' => 'nullable|array',
            'agenda' => 'nullable|array',
            'agenda_notes' => 'nullable|array',
            'program_axes' => 'nullable|array',
            'program_axes_names' => 'nullable|array',
            'decisions' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'assignments' => 'nullable|array',
            'task_status' => 'nullable|array',
            'next_meeting_date' => 'nullable|date',
            'excluded_supervisors' => 'nullable|array',
        ]);

        $meetingMinute = MeetingMinute::create([
            'meeting_date' => $validated['meeting_date'],
            'meeting_time' => $validated['meeting_time'] ?? now()->format('H:i:s'),
            'location' => $validated['location'] ?? '',
            'attendees' => $validated['attendees'] ?? [],
            'excluded_supervisors' => $validated['excluded_supervisors'] ?? [],
            'absent' => [],
            'agenda' => $validated['agenda'] ?? [],
            'decisions' => $validated['decisions'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'next_meeting_date' => $validated['next_meeting_date'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        if (!empty($validated['agenda_notes'])) {
            $meetingMinute->agenda_notes = $validated['agenda_notes'];
            $meetingMinute->save();
        }

        if (!empty($validated['program_axes'])) {
            $meetingMinute->program_axes = $validated['program_axes'];
            $meetingMinute->program_axes_names = $validated['program_axes_names'] ?? [];
            $meetingMinute->save();
        }

        if (!empty($validated['manual_attendees'])) {
            $meetingMinute->manual_attendees = array_filter($validated['manual_attendees'], fn($a) => !empty($a['name']));
            $meetingMinute->save();
        }

        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignment) {
                if (!empty($assignment['task']) && !empty($assignment['supervisor_id'])) {
                    $task = MeetingTask::create([
                        'meeting_minute_id' => $meetingMinute->id,
                        'assignee_id' => $assignment['supervisor_id'],
                        'assigned_by' => auth()->id(),
                        'title' => $assignment['task'],
                        'status' => 'pending',
                        'color' => MeetingTask::generateUniqueColor(),
                    ]);

                    $assignee = User::find($assignment['supervisor_id']);
                    if ($assignee) {
                        $link = $assignee->isSupervisor()
                            ? route('supervisor.tasks.index')
                            : route('admin.tasks.index');
                        app(NotificationService::class)->sendToUser(
                            $assignee,
                            'meeting_task',
                            'تكليف جديد',
                            'تم إسناد تكليف جديد: ' . $assignment['task'],
                            $link
                        );
                    }
                }
            }
        }

        $this->processAiExtractedTasks($meetingMinute, $validated);

        if (!empty($validated['task_status'])) {
            foreach ($validated['task_status'] as $taskId => $status) {
                $updateData = ['status' => $status];
                if ($status === 'completed') {
                    $updateData['completed_at'] = now();
                }
                MeetingTask::where('id', $taskId)->update($updateData);
            }
        }

        return redirect()->route('admin.meeting-minutes.show', $meetingMinute->id)
            ->with('success', 'تم إنشاء المحضر بنجاح');
    }

    public function show(MeetingMinute $meetingMinute)
    {
        $supervisors = User::whereIn('role', ['admin', 'supervisor'])->get();

        $meetingMinute->load('tasks.assignee');

        return view('admin.meeting-minutes.show', compact('meetingMinute', 'supervisors'));
    }

    public function edit(MeetingMinute $meetingMinute)
    {
        $supervisors = User::whereIn('role', ['admin', 'supervisor'])->get();

        $lastMeeting = MeetingMinute::where('id', '!=', $meetingMinute->id)
            ->orderBy('meeting_date', 'desc')
            ->first();
        $tasksFromAllMeetings = MeetingTask::whereIn('status', ['pending', 'in_progress'])
            ->whereHas('meetingMinute', function($query) use ($meetingMinute) {
                $query->where('id', '!=', $meetingMinute->id);
            })
            ->with(['assignee', 'meetingMinute'])
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $changedTasks = MeetingTask::where('meeting_minute_id', '!=', $meetingMinute->id)
            ->whereHas('meetingMinute', function($query) use ($meetingMinute) {
                $query->where('id', '!=', $meetingMinute->id);
            })
            ->whereIn('status', ['completed', 'cancelled'])
            ->where('updated_at', '>=', $meetingMinute->created_at)
            ->with(['assignee', 'meetingMinute'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        $recentAttendance = Attendance::where('date', '>=', now()->subDays(3))
            ->with('student.user')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('student_id');

        $supervisorsWithStudents = User::where('role', 'supervisor')
            ->with(['supervisor', 'supervisedStudents.user', 'supervisedStudents.level'])
            ->get();

        $studentIds = $supervisorsWithStudents->pluck('supervisedStudents')->flatten()->pluck('id')->toArray();

        $allAttendances = empty($studentIds) ? collect() : Attendance::whereIn('student_id', $studentIds)
            ->where('date', '>=', now()->subDays(3))
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('student_id');

        $allRecitations = empty($studentIds) ? collect() : RecitationSession::whereIn('student_id', $studentIds)
            ->orderBy('session_date', 'desc')
            ->get()
            ->groupBy('student_id');

        $supervisorStudents = $supervisorsWithStudents->map(function ($supervisor) use ($allAttendances, $allRecitations) {
            $supervisor->students_with_data = $supervisor->supervisedStudents->map(function ($student) use ($allAttendances, $allRecitations) {
                $student->last_three_attendances = $allAttendances->get($student->id, collect())->take(3);
                $student->last_two_recitations = $allRecitations->get($student->id, collect())->take(2);
                return $student;
            });
            return $supervisor;
        });

        return view('admin.meeting-minutes.edit', compact('meetingMinute', 'supervisors', 'recentAttendance', 'tasksFromAllMeetings', 'lastMeeting', 'changedTasks', 'supervisorStudents'));
    }

    public function update(Request $request, MeetingMinute $meetingMinute)
    {
        $validated = $request->validate([
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'attendees' => 'nullable|array',
            'manual_attendees' => 'nullable|array',
            'agenda' => 'nullable|array',
            'agenda_notes' => 'nullable|array',
            'program_axes' => 'nullable|array',
            'program_axes_names' => 'nullable|array',
            'decisions' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'assignments' => 'nullable|array',
            'task_status' => 'nullable|array',
            'task_titles' => 'nullable|array',
            'next_meeting_date' => 'nullable|date',
            'excluded_supervisors' => 'nullable|array',
        ]);

        $meetingMinute->update([
            'meeting_date' => $validated['meeting_date'],
            'meeting_time' => $validated['meeting_time'] ?? now()->format('H:i:s'),
            'location' => $validated['location'] ?? '',
            'attendees' => $validated['attendees'] ?? [],
            'excluded_supervisors' => $validated['excluded_supervisors'] ?? [],
            'agenda' => $validated['agenda'] ?? [],
            'decisions' => $validated['decisions'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
            'next_meeting_date' => $validated['next_meeting_date'] ?? null,
        ]);

        if (isset($validated['agenda_notes'])) {
            $meetingMinute->agenda_notes = $validated['agenda_notes'];
            $meetingMinute->save();
        }

        if (isset($validated['program_axes'])) {
            $meetingMinute->program_axes = $validated['program_axes'];
            $meetingMinute->program_axes_names = $validated['program_axes_names'] ?? [];
            $meetingMinute->save();
        }

        if (isset($validated['manual_attendees'])) {
            $meetingMinute->manual_attendees = array_filter($validated['manual_attendees'], fn($a) => !empty($a['name']));
            $meetingMinute->save();
        }

        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignment) {
                if (!empty($assignment['task']) && !empty($assignment['supervisor_id'])) {
                    $task = MeetingTask::create([
                        'meeting_minute_id' => $meetingMinute->id,
                        'assignee_id' => $assignment['supervisor_id'],
                        'assigned_by' => auth()->id(),
                        'title' => $assignment['task'],
                        'status' => 'pending',
                        'color' => MeetingTask::generateUniqueColor(),
                    ]);

                    $assignee = User::find($assignment['supervisor_id']);
                    if ($assignee) {
                        $link = $assignee->isSupervisor()
                            ? route('supervisor.tasks.index')
                            : route('admin.tasks.index');
                        app(NotificationService::class)->sendToUser(
                            $assignee,
                            'meeting_task',
                            'تكليف جديد',
                            'تم إسناد تكليف جديد: ' . $assignment['task'],
                            $link
                        );
                    }
                }
            }
        }

        if (!empty($validated['task_status'])) {
            foreach ($validated['task_status'] as $taskId => $status) {
                $task = MeetingTask::where('id', $taskId)
                    ->where('meeting_minute_id', $meetingMinute->id)
                    ->first();
                if ($task) {
                    $updateData = ['status' => $status];
                    if ($status === 'completed') {
                        $updateData['completed_at'] = now();
                    }
                    $task->update($updateData);
                }
            }
        }

        if (!empty($validated['task_titles'])) {
            foreach ($validated['task_titles'] as $taskId => $title) {
                MeetingTask::where('id', $taskId)
                    ->where('meeting_minute_id', $meetingMinute->id)
                    ->update(['title' => $title]);
            }
        }

        return redirect()->route('admin.meeting-minutes.show', $meetingMinute->id)
            ->with('success', 'تم تحديث المحضر بنجاح');
    }

    public function destroy(MeetingMinute $meetingMinute)
    {
        $meetingMinute->delete();

        return redirect()->route('admin.meeting-minutes.index')
            ->with('success', 'تم حذف المحضر بنجاح');
    }

    public function approve(MeetingMinute $meetingMinute)
    {
        $meetingMinute->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'تم اعتماد المحضر بنجاح');
    }

    public function archive(MeetingMinute $meetingMinute)
    {
        $meetingMinute->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'تم أرشفة المحضر بنجاح');
    }

    private function processAiExtractedTasks(MeetingMinute $meetingMinute, array $validated): void
    {
        $supervisors = User::whereIn('role', ['supervisor'])->get()->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
        ])->toArray();

        $meetingData = [
            'title' => $validated['meeting_date'] ?? '',
            'decisions' => $validated['decisions'] ?? '',
            'recommendations' => $validated['recommendations'] ?? '',
            'agenda' => $validated['agenda'] ?? [],
            'attendees' => $validated['attendees'] ?? [],
            'supervisors' => $supervisors,
        ];

        $aiService = app(AiParsingService::class);
        $result = $aiService->analyzeMeetingMinutes($meetingData);

        if (!$result['success'] || empty($result['tasks'])) {
            return;
        }

        foreach ($result['tasks'] as $aiTask) {
            if (empty($aiTask['title']) || empty($aiTask['assignee_id'])) {
                continue;
            }

            $assigneeId = (int) $aiTask['assignee_id'];
            $supervisorExists = collect($supervisors)->contains('id', $assigneeId);

            if (!$supervisorExists) {
                continue;
            }

            $task = MeetingTask::create([
                'meeting_minute_id' => $meetingMinute->id,
                'assignee_id' => $assigneeId,
                'assigned_by' => auth()->id(),
                'title' => $aiTask['title'],
                'description' => $aiTask['description'] ?? null,
                'status' => 'pending',
                'color' => MeetingTask::generateUniqueColor(),
            ]);

            $assignee = User::find($assigneeId);
            if ($assignee) {
                $link = $assignee->isSupervisor()
                    ? route('supervisor.tasks.index')
                    : route('admin.tasks.index');
                app(NotificationService::class)->sendToUser(
                    $assignee,
                    'meeting_task',
                    'تكليف جديد من الذكاء الاصطناعي',
                    'تم إسناد تكليف تلقائياً: ' . $aiTask['title'],
                    $link
                );
            }
        }
    }
}
