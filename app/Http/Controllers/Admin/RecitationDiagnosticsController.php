<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Services\MissedRecitationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RecitationDiagnosticsController extends Controller
{
    public function index(Request $request)
    {
        $lastScan = DB::table('settings')->where('key', 'recitation_last_scan_date')->value('value');

        $studentId = $request->student_id;
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $diagnosis = null;
        if ($studentId) {
            $student = Student::with('user')->find($studentId);
            if ($student) {
                $diagnosis = $this->diagnose($student, $date);
            }
        }

        $students = Student::with('user')->orderedByName()->get();

        return view('admin.recitation-diagnostics.index', [
            'lastScan' => $lastScan,
            'students' => $students,
            'studentId' => $studentId,
            'date' => $date,
            'diagnosis' => $diagnosis,
            'autoLookbackDays' => (int) config('recitation.auto_lookback_days', 7),
            'requiredDays' => (array) config('recitation.required_days', []),
            'pendingMigrations' => $this->pendingMigrations(),
        ]);
    }

    /**
     * Read-only explanation of why a student did or did not get an automatic
     * "لم يسمع" session for a given date.
     */
    private function diagnose(Student $student, Carbon $date): array
    {
        $service = app(MissedRecitationService::class);

        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', $date)
            ->first();

        $sessions = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('student_id', $student->id)
            ->whereDate('session_date', $date)
            ->get();

        $autoSession = $sessions->firstWhere('auto_generated', true);

        $willCreate = $attendance ? $service->shouldCreateForAttendance($attendance, now()) : false;

        return [
            'student' => $student,
            'attendance_status' => $attendance->status ?? null,
            'is_attended' => $attendance ? in_array($attendance->status, config('recitation.attendance_statuses', []), true) : false,
            'required_day' => $attendance ? $service->isRequiredAttendanceDay($attendance) : false,
            'day_ended' => $attendance ? $service->hasAttendanceDayEnded($attendance, now()) : false,
            'has_session' => $sessions->isNotEmpty(),
            'sessions' => $sessions,
            'auto_session' => $autoSession,
            'will_create' => $willCreate,
            'is_today' => $date->isToday(),
        ];
    }

    /**
     * Preview how many sessions an explicit range would create (no writes).
     */
    public function preview(Request $request, MissedRecitationService $service)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $details = $service->previewRangeDetails(
            now(),
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to'])
        );

        return response()->json([
            'success' => true,
            'count' => $details['total'],
            'days' => $details['days'],
        ]);
    }

    /**
     * Create the "لم يسمع" sessions for an explicit older range.
     */
    public function backfill(Request $request, MissedRecitationService $service)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $result = $service->runRange(now(), Carbon::parse($validated['from']), Carbon::parse($validated['to']));

        return back()->with('success', sprintf(
            'تم إنشاء %d جلسة "لم يسمع" (تم تخطي %d).',
            $result['created'],
            $result['skipped']
        ));
    }

    /**
     * Run pending migrations from the browser (for hosts without SSH).
     */
    public function runMigrations()
    {
        if (empty($this->pendingMigrations())) {
            return back()->with('error', 'لا توجد migrations معلّقة.');
        }

        Artisan::call('migrate', ['--force' => true]);

        return back()->with('success', 'تم تشغيل الـ migrations.'."\n".Artisan::output());
    }

    public function clearConfig()
    {
        Artisan::call('config:clear');

        return back()->with('success', 'تم مسح الـ config cache.');
    }

    /**
     * @return array<int, string>
     */
    private function pendingMigrations(): array
    {
        try {
            $migrator = app('migrator');
            if (! $migrator->repositoryExists()) {
                return [];
            }

            $ran = $migrator->getRepository()->getRan();

            return array_values(array_diff($migrator->getMigrationFiles($migrator->paths()), $ran));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
