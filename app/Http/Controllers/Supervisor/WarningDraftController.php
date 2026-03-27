<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Warning;
use App\Models\WarningDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarningDraftController extends Controller
{
    public function index()
    {
        $drafts = WarningDraft::where('supervisor_id', Auth::id())
            ->where('status', 'pending')
            ->with(['student', 'warningRule'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supervisor.warning-drafts.index', compact('drafts'));
    }

    public function show(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->with(['student', 'warningRule'])
            ->firstOrFail();

        return view('supervisor.warning-drafts.show', compact('draft'));
    }

    public function approve(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $type = $draft->warningRule->type ?? 'general';

        $attendanceRecords = $draft->attendance_data ?? [];
        $recitationRecords = $draft->recitation_data ?? [];

        $absentDays = collect($attendanceRecords)->where('status', 'absent')->count();
        $lateDays = collect($attendanceRecords)->where('status', 'late')->count();
        $totalAttendance = $absentDays + $lateDays + collect($attendanceRecords)->whereIn('status', ['present', 'excused'])->count();

        $failedSessions = collect($recitationRecords)->whereIn('status', ['failed', 'not_recitate'])->count();
        $totalSessions = count($recitationRecords);

        $warning = \Illuminate\Support\Facades\DB::transaction(function () use ($draft, $type, $absentDays, $lateDays, $totalAttendance, $failedSessions, $totalSessions, $attendanceRecords, $recitationRecords) {
            $prefix = 'WRN-' . date('Y') . '-';
            $lastWarning = Warning::where('warning_number', 'LIKE', $prefix)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();
            $lastNum = $lastWarning ? (int) substr($lastWarning->warning_number, -4) : 0;
            $warningNumber = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

            $warning = Warning::create([
                'student_id' => $draft->student_id,
                'issued_by' => Auth::id(),
                'warning_number' => $warningNumber,
                'type' => $type,
                'title' => $draft->title,
                'content' => $draft->content,
                'issue_date' => $draft->issue_date,
                'issue_day' => $draft->issue_date ? \App\Models\Student::getArabicDay($draft->issue_date) : null,
                'from_date' => $draft->from_date,
                'from_day' => $draft->from_date ? \App\Models\Student::getArabicDay($draft->from_date) : null,
                'to_date' => $draft->to_date,
                'to_day' => $draft->to_date ? \App\Models\Student::getArabicDay($draft->to_date) : null,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'total_days' => $totalAttendance ?: null,
                'failed_sessions' => $failedSessions,
                'total_sessions' => $totalSessions ?: null,
                'attendance_data' => $attendanceRecords,
                'recitation_data' => $recitationRecords,
                'test_data' => $draft->test_data ?? [],
            ]);

            $this->notifyParent($draft->student, $warning);

            $draft->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return $warning;
        });

        return redirect()
            ->route('supervisor.warnings.show', $warning->id)
            ->with('success', 'تم إصدار الإنذار بنجاح');
    }

    public function dismiss(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $draft->update([
            'status' => 'dismissed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('supervisor.warning-drafts.index')
            ->with('success', 'تم تجاهل المسودة');
    }

    protected function notifyParent(Student $student, Warning $warning): void
    {
        $parent = $student->parent;
        if (!$parent || !$parent->user) {
            return;
        }

        try {
            if (class_exists(\App\Notifications\WarningIssued::class)) {
                $parent->user->notify(new \App\Notifications\WarningIssued($warning));
            }
        } catch (\Exception $e) {
            \Log::error("Failed to notify parent: " . $e->getMessage());
        }
    }
}
