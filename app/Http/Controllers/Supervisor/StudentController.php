<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\{Student, User, Level};
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller {
    public function index() {
        $query = Student::where('supervisor_id', Auth::id())->with('user', 'level');

        if (request('search')) {
            $search = request('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('national_id', 'like', "%{$search}%")
              ->orWhere('student_number', 'like', "%{$search}%");
        }

        if (request('level_id')) {
            $query->where('level_id', request('level_id'));
        }

        $students = $query->paginate(20);
        return view('supervisor.students.index', compact('students'));
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
            $file = $request->file('image');
            try {
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $avatarPath = 'avatars/' . $filename;
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage() . ' | File: ' . $file->getClientOriginalName());
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
            // Delete old avatar if exists
            if ($student->user->avatar) {
                try {
                    $oldPath = storage_path('app/public/' . $student->user->avatar);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete old avatar: ' . $e->getMessage());
                }
            }
            try {
                $file = $request->file('image');
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $newAvatarPath = 'avatars/' . $filename;
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
        $students = Student::with('user')
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
}
