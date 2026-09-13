<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\{Student, User, Level, Attendance, RecitationSession};
use App\Services\AnalyticsService;
use App\Services\ReportExporter;
use App\Services\ReportService;
use App\Traits\AvatarUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller {
    use AvatarUpload;
    public function index(Request $request) {
        $supervisorId = Auth::id();

        $allStudents = Student::where('supervisor_id', $supervisorId)
            ->with('user', 'level')
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '')
            ->values();

        $reports = app(ReportService::class);

        $request->merge(['period' => $request->period ?? 'year']);
        [$fromDate, $toDate, $period] = $reports->resolvePeriod($request);

        $periodLabels = [
            'today'   => 'اليوم',
            'week'    => 'هذا الأسبوع',
            'month'   => 'هذا الشهر',
            'quarter' => 'هذا الربع',
            'half'    => 'هذا النصف',
            'year'    => 'هذه السنة',
            'all'     => 'كل الفترات',
            'custom'  => 'فترة مخصصة',
        ];
        $periodLabel = $periodLabels[$period] ?? 'هذه السنة';

        $selectedStudent = $request->student_id
            ? $allStudents->firstWhere('id', (int) $request->student_id)
            : null;

        $breakdown = [];
        $recStats  = null;
        $attStats  = null;

        if ($selectedStudent) {
            $sessionsQuery = RecitationSession::where('student_id', $selectedStudent->id)
                ->with(['student.user', 'student.supervisor', 'supervisor', 'part', 'pages']);
            $attendanceQuery = Attendance::where('student_id', $selectedStudent->id);

            if ($fromDate && $toDate) {
                $sessionsQuery->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()]);
                $attendanceQuery->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()]);
            }

            $sessions    = $sessionsQuery->get();
            $attendances = $attendanceQuery->get();

            $breakdown = $reports->studentBreakdown($sessions, $attendances);
            $recStats  = $reports->recitationStats($sessions);
            $attStats  = $reports->attendanceStats($attendances);
        }

        if ($request->export === 'pdf') {
            if ($selectedStudent) {
                return app(ReportExporter::class)->exportWrittenReportPdf(
                    $selectedStudent, $breakdown, $periodLabel, $fromDate, $toDate
                );
            }

            return redirect()->route('supervisor.students.index');
        }

        return view('supervisor.students.index', compact(
            'allStudents', 'selectedStudent', 'breakdown', 'periodLabel', 'period', 'recStats', 'attStats', 'fromDate', 'toDate'
        ));
    }

    public function create() {
        $levels = Level::all();
        return view('supervisor.students.create', compact('levels'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'level_id' => 'required|exists:levels,id',
            'national_id' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $uploadError = null;
        $avatarPath = null;

        if ($request->hasFile('image')) {
            try {
                $avatarPath = $this->uploadAvatar($request);
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage() . ' | File: ' . $request->file('image')->getClientOriginalName());
                $uploadError = 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.';
            }
        }

        if ($uploadError) {
            return back()->withInput()->with('error', $uploadError);
        }

        DB::transaction(function() use ($request, $avatarPath) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'avatar' => $avatarPath,
            ]);

            $currentYear = date('Y');
            $lastStudentThisYear = Student::where('student_number', 'like', "STU-{$currentYear}-%")
                ->orderBy('student_number', 'desc')
                ->first();
            $nextNumber = $lastStudentThisYear ? (int)substr($lastStudentThisYear->student_number, -4) + 1 : 1;
            $studentNumber = 'STU-' . $currentYear . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            Student::create([
                'user_id' => $user->id,
                'supervisor_id' => Auth::id(),
                'level_id' => $request->level_id,
                'student_number' => $studentNumber,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'enrollment_date' => today(),
                'notes' => $request->notes,
            ]);
        });

        return redirect()->route('supervisor.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function edit(Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
        $student->load('user');
        $levels = Level::all();
        return view('supervisor.students.edit', compact('student', 'levels'));
    }

    public function update(Request $request, Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $student->user_id,
            'phone' => 'nullable|string|unique:users,phone,' . $student->user_id,
            'email' => 'nullable|email|unique:users,email,' . $student->user_id,
            'level_id' => 'required|exists:levels,id',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            if ($student->user->avatar) {
                $this->deleteAvatar($student->user->avatar);
            }
            try {
                $newAvatarPath = $this->uploadAvatar($request);
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        DB::transaction(function() use ($request, $student, $newAvatarPath) {
            $userData = [
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
            ];

            if ($newAvatarPath) {
                $userData['avatar'] = $newAvatarPath;
            }

            $student->user->update($userData);

            if ($request->password) {
                $student->user->update(['password' => Hash::make($request->password)]);
            }

            $student->update([
                'level_id' => $request->level_id,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'enrollment_date' => $request->enrollment_date,
                'notes' => $request->notes,
            ]);
        });

        return redirect()->route('supervisor.students.show', $student)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function show(Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
        $student->load(['user','level','attendances'=>fn($q)=>$q->latest()->take(30),'recitationSessions'=>fn($q)=>$q->with('part')->latest()->take(20),'warnings'=>fn($q)=>$q->latest()]);

        $analyticsService = app(AnalyticsService::class);
        $progressData = $analyticsService->getStudentProgressData($student->id);

        return view('supervisor.students.show', array_merge(
            compact('student'),
            [
                'velocityData' => $progressData->velocityWeeks,
                'heatmapData' => $progressData->pageDifficulty,
                'recentSessions' => $progressData->recentSessions,
                'completionPercent' => $progressData->completionPercent,
                'levelPagesTotal' => $progressData->currentLevelPagesTotal,
                'levelPagesDone' => $progressData->currentLevelPagesDone,
            ]
        ));
    }

    public function destroy(Student $student)
    {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function() use ($student) {
            $student->user->delete();
            $student->delete();
        });

        return redirect()->route('supervisor.students.index')
            ->with('success', 'تم حذف الطالب بنجاح');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $students = Student::with(['user', 'level', 'supervisor.user'])
            ->where('supervisor_id', Auth::id())
            ->whereHas('user', function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('student_number', 'like', '%' . $query . '%');
            })
            ->limit(20)
            ->get()
            ->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'student_number' => $s->student_number,
                    'level_name' => $s->level->name ?? '-',
                    'supervisor_name' => $s->supervisor->user->name ?? '-',
                ];
            });

        return response()->json($students);
    }

    public function apiShow(Student $student)
    {
        if ($student->supervisor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student->load('user', 'level', 'supervisor');

        return response()->json([
            'id' => $student->id,
            'name' => $student->user->name,
            'student_number' => $student->student_number,
            'level_name' => $student->level->name ?? '-',
            'supervisor_name' => $student->supervisor->name ?? '-',
        ]);
    }
}
